<?php
require __DIR__ . '/_lib.php';
must_post();

$in = input_data();
$n = clean($in['nombre'] ?? '', 120);
$m = clean($in['correo'] ?? '', 120);

if ($n === '' || !valid_mail($m)) {
    out(false, ['error' => 'Nombre y correo electrónico válidos son obligatorios.'], 400);
}

$r = [
    date('Y-m-d H:i:s'),
    $n,
    $m,
    clean($in['empresa'] ?? '', 120),
    clean($in['interes'] ?? '', 120),
    clean($in['comentario'] ?? '', 2000),
    $_SERVER['REMOTE_ADDR'] ?? ''
];

csv_append('contactos.csv', ['Fecha', 'Nombre', 'Correo', 'Empresa', 'Interes', 'Comentario', 'IP'], $r);
notify(MAIL_CONTACTO, 'Nuevo contacto web - CVG Alcasa', implode("\n", $r));
out(true, ['mensaje' => '¡Gracias! Hemos recibido tu mensaje. Nos pondremos en contacto contigo a la brevedad.']);
