<?php
/**
 * proxy_noticias.php
 * 
 * Proxy para obtener noticias desde el periódico digital de ALCASA
 * Evita problemas de CORS al hacer la petición desde el servidor
 */

// Permitir acceso desde cualquier origen (para que tu sala de prensa pueda consumirlo)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// URL de la API de noticias del periódico digital
$url = 'http://alcasa.com.ve:5001/api/noticias';

// Inicializar cURL
$ch = curl_init();

// Configurar opciones de cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Devolver el resultado como string
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Seguir redirecciones
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignorar verificación SSL (si es necesario)
curl_setopt($ch, CURLOPT_TIMEOUT, 10);           // Timeout de 10 segundos

// Ejecutar la petición
$response = curl_exec($ch);

// Verificar si hubo error en cURL
if (curl_errno($ch)) {
    // Si hay error, devolver un JSON con el mensaje de error
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al conectar con el servidor de noticias: ' . curl_error($ch)
    ]);
} else {
    // Si todo está bien, devolver la respuesta tal cual
    echo $response;
}

// Cerrar la sesión cURL
curl_close($ch);
?>