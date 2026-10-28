<?php
require __DIR__ . '/_lib.php'; must_post();
$n = clean($_POST['nombre'] ?? '', 100); $m = clean($_POST['correo'] ?? '', 120);
if ($n === '' || !valid_mail($m)) out(false, ['error' => 'Nombre y correo válidos son obligatorios'], 400);
$r = [date('Y-m-d H:i:s'), $n, $m, clean($_POST['empresa'] ?? '', 100), clean($_POST['interes'] ?? '', 100), clean($_POST['comentario'] ?? '', 1000), $_SERVER['REMOTE_ADDR'] ?? ''];
csv_append('contactos.csv', ['Fecha', 'Nombre', 'Correo', 'Empresa', 'Interes', 'Comentario', 'IP'], $r);
notify(MAIL_CONTACTO, 'Nuevo contacto web', implode("\n", $r)); out(true);
