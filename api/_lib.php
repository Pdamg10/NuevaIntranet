<?php
// Utilidades compartidas del backend PHP (destino final: WordPress).
// PENDIENTE: definir los correos de destino en producción (vacío = no se envía correo).
const MAIL_CONTACTO = '';
const MAIL_RRHH = '';
const STORAGE = __DIR__ . '/../storage'; // En producción: mover FUERA de la raíz web.

function out($ok, $data = [], $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $ok] + $data, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail($msg, $code = 400) {
    out(false, ['error' => $msg], $code);
}

function must_post() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        out(false, ['error' => 'Método no permitido'], 405);
    }
}

function input_data() {
    if (!empty($_POST)) {
        return $_POST;
    }
    $raw = file_get_contents('php://input');
    if ($raw) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            return $json;
        }
    }
    return [];
}

function clean($s, $max) {
    $s = mb_substr(trim(preg_replace('/[\r\n"]+/', ' ', (string)$s)), 0, $max);
    return ($s !== '' && strpos('=+-@', $s[0]) !== false) ? "'" . $s : $s;
}

function valid_mail($m) {
    return (bool) filter_var($m, FILTER_VALIDATE_EMAIL);
}

function csv_append($file, $head, $row) {
    if (!is_dir(STORAGE)) {
        mkdir(STORAGE, 0750, true);
    }
    $f = STORAGE . '/' . $file;
    $new = !is_file($f);
    if ($new) {
        $seed = __DIR__ . '/../data/' . pathinfo($file, PATHINFO_FILENAME) . '-semilla.csv';
        if (is_file($seed)) {
            $rawSeed = (string)file_get_contents($seed);
            if ($rawSeed !== '' && substr($rawSeed, -1) !== "\n") {
                $rawSeed .= "\n";
            }
            @file_put_contents($f, $rawSeed, LOCK_EX);
            $new = !is_file($f) || filesize($f) === 0;
        }
    }
    $h = fopen($f, 'a');
    if ($h) {
        flock($h, LOCK_EX);
        if ($new) {
            fputcsv($h, $head);
        }
        fputcsv($h, $row);
        flock($h, LOCK_UN);
        fclose($h);
    }
}

function notify($to, $subject, $body) {
    if ($to !== '') {
        @mail($to, $subject, $body, 'Content-Type: text/plain; charset=utf-8');
    }
}
