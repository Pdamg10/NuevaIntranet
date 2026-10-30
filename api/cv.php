<?php
require __DIR__ . '/_lib.php';
must_post();

$campos = [
    'nombre'   => 120,
    'cedula'   => 20,
    'correo'   => 120,
    'telefono' => 35,
    'nivel'    => 80,
    'area'     => 100,
    'resumen'  => 1200
];

$d = [];
foreach ($campos as $k => $x) {
    $d[$k] = clean($_POST[$k] ?? '', $x);
}

if (!$d['nombre'] || !$d['cedula'] || !$d['telefono'] || !$d['nivel'] || !$d['area'] || !valid_mail($d['correo'])) {
    out(false, ['error' => 'Por favor completa todos los campos obligatorios (*).'], 400);
}

$u = $_FILES['cv_archivo'] ?? null;
if (!$u || $u['error'] !== UPLOAD_ERR_OK) {
    out(false, ['error' => 'Por favor adjunta tu síntesis curricular (PDF, DOC o DOCX).'], 400);
}

if ($u['size'] > 10 * 1024 * 1024) {
    out(false, ['error' => 'El archivo supera el límite máximo de 10 MB.'], 400);
}

$ext = strtolower(pathinfo($u['name'], PATHINFO_EXTENSION));
$ok = [
    'pdf'  => ['application/pdf'],
    'doc'  => ['application/msword', 'application/octet-stream'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream']
];

$mime = class_exists('finfo') ? (new finfo(FILEINFO_MIME_TYPE))->file($u['tmp_name']) : ($u['type'] ?? '');
if (!isset($ok[$ext]) || ($mime && !in_array($mime, $ok[$ext], true))) {
    out(false, ['error' => 'Formato no permitido. Solo se admiten archivos PDF, DOC o DOCX.'], 400);
}

$dir = STORAGE . '/cv';
if (!is_dir($dir)) {
    mkdir($dir, 0750, true);
}

$name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext; // nombre aleatorio seguro
if (!move_uploaded_file($u['tmp_name'], "$dir/$name")) {
    out(false, ['error' => 'No se pudo guardar el archivo en el servidor.'], 500);
}

csv_append(
    'postulaciones.csv',
    ['Fecha', 'Nombre', 'Cedula', 'Correo', 'Telefono', 'Nivel', 'Area', 'Resumen', 'Archivo', 'IP'],
    [date('Y-m-d H:i:s'), $d['nombre'], $d['cedula'], $d['correo'], $d['telefono'], $d['nivel'], $d['area'], $d['resumen'], $name, $_SERVER['REMOTE_ADDR'] ?? '']
);

notify(MAIL_RRHH, 'Nueva postulación (CV) - CVG Alcasa', "{$d['nombre']} | {$d['area']} | archivo: $name");
out(true, [
    'mensaje' => "¡Postulación recibida con éxito! Tu Síntesis Curricular ha sido registrada para el área de {$d['area']}.",
    'archivo' => $name
]);
