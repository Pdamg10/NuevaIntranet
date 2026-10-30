<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/_lib.php';

if (empty($_SESSION['alcasa_user'])) {
    header('Content-Type: application/json; charset=utf-8');
    fail('No autenticado. Inicia sesión primero.', 401);
}

$permisos = $_SESSION['alcasa_user']['permisos'] ?? [];
$esAdmin = (($_SESSION['alcasa_user']['rol'] ?? '') === 'admin');
$tipo = $_GET['tipo'] ?? 'resumen';

function leerCsvComoArray(string $path): array {
    if (!is_file($path)) return [];
    $fp = fopen($path, 'rb');
    if (!$fp) return [];
    $headers = [];
    $rows = [];
    $lineIdx = 0;
    while (($data = fgetcsv($fp)) !== false) {
        if ($lineIdx === 0) {
            foreach ($data as $h) {
                $hClean = preg_replace('/^\xEF\xBB\xBF/', '', trim((string)$h));
                $headers[] = strtolower($hClean);
            }
        } else {
            if (count($data) < 2) continue;
            $item = [];
            foreach ($headers as $i => $key) {
                $item[$key] = trim((string)($data[$i] ?? ''));
            }
            $rows[] = $item;
        }
        $lineIdx++;
    }
    fclose($fp);
    return array_reverse($rows);
}

if ($tipo === 'descargar_cv') {
    if (!$esAdmin && !in_array('cv', $permisos, true)) {
        header('Content-Type: application/json; charset=utf-8');
        fail('No tienes permisos para descargar currículums.', 403);
    }
    $archivo = basename((string)($_GET['archivo'] ?? ''));
    $fullPath = STORAGE . '/cv/' . $archivo;
    if ($archivo === '' || !is_file($fullPath)) {
        http_response_code(404);
        echo 'Archivo CV no encontrado.';
        exit;
    }
    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    $mime = $ext === 'pdf' ? 'application/pdf' : 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="' . $archivo . '"');
    header('Content-Length: ' . filesize($fullPath));
    readfile($fullPath);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$puedeContactos = $esAdmin || in_array('contactos', $permisos, true);
$puedeCV = $esAdmin || in_array('cv', $permisos, true);

$contactos = $puedeContactos ? leerCsvComoArray(STORAGE . '/contactos.csv') : [];
$postulaciones = $puedeCV ? leerCsvComoArray(STORAGE . '/postulaciones.csv') : [];

echo json_encode([
    'success'       => true,
    'user'          => $_SESSION['alcasa_user'],
    'contactos'     => $contactos,
    'postulaciones' => $postulaciones
], JSON_UNESCAPED_UNICODE);
