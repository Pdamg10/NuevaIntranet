<?php
/**
 * guardar_contacto.php
 * Recibe los datos del formulario de contacto y los guarda en contactos.json
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Leer datos JSON del cuerpo
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Sanitizar y validar
$nombre     = trim(strip_tags($input['nombre'] ?? ''));
$correo     = trim($input['correo'] ?? '');
$empresa    = trim(strip_tags($input['empresa'] ?? ''));
$interes    = trim(strip_tags($input['interes'] ?? ''));
$comentario = trim(strip_tags($input['comentario'] ?? ''));

// Validaciones
if (empty($nombre) || empty($correo)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El nombre y el correo son obligatorios']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Correo electrónico inválido']);
    exit;
}

if (mb_strlen($nombre) > 120 || mb_strlen($empresa) > 120 || mb_strlen($comentario) > 2000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Alguno de los campos excede el tamaño permitido']);
    exit;
}

// Archivo JSON donde se guardarán los contactos
$archivo = __DIR__ . '/contactos.json';

// Leer contactos existentes
$contactos = [];
if (file_exists($archivo)) {
    $contenido = file_get_contents($archivo);
    if ($contenido) {
        $contactos = json_decode($contenido, true);
        if (!is_array($contactos)) {
            $contactos = [];
        }
    }
}

// Crear registro
$nuevo = [
    'fecha'       => date('Y-m-d H:i:s'),
    'nombre'      => $nombre,
    'correo'      => $correo,
    'empresa'     => $empresa,
    'interes'     => $interes,
    'comentario'  => $comentario,
    'ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? ''
];

// Agregar al listado
$contactos[] = $nuevo;

// Guardar con bloqueo para evitar corrupción si hay envíos simultáneos
$resultado = file_put_contents(
    $archivo,
    json_encode($contactos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

if ($resultado === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo guardar el contacto. Verifica los permisos de escritura en /sitio/']);
    exit;
}

echo json_encode([
    'success' => true,
    'mensaje' => '¡Gracias! Hemos recibido tu mensaje correctamente.'
]);