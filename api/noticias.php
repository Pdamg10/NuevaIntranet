<?php
// Proxy de noticias: normaliza campos, cachea 120 s y sirve la copia previa si la API cae.
header('Content-Type: application/json; charset=utf-8');
const BASE = 'http://alcasa.com.ve:5001'; const TTL = 120;
$cache = sys_get_temp_dir() . '/alcasa_noticias.json';
if (is_file($cache) && time() - filemtime($cache) < TTL) { readfile($cache); exit; }
$ch = curl_init(BASE . '/api/noticias');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 8, CURLOPT_FOLLOWLOCATION => 1]);
$raw = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
$d = json_decode($raw, true);
if ($code !== 200 || !is_array($d)) {
  if (is_file($cache)) { readfile($cache); exit; }
  http_response_code(502); echo '[]'; exit;
}
$list = isset($d['noticias']) ? $d['noticias'] : $d;
$pick = function ($n, $ks) { foreach ($ks as $k) if (!empty($n[$k])) return $n[$k]; return ''; };
$abs = function ($u) { return ($u && $u[0] === '/') ? BASE . $u : $u; };
$out = [];
foreach ($list as $n) {
  $out[] = ['titulo' => $pick($n, ['titulo', 'title']),
    'resumen' => $pick($n, ['resumen', 'subtitulo', 'descripcion', 'extracto']),
    'imagen' => $abs($pick($n, ['imagen', 'portada', 'miniatura', 'imagen_url', 'thumbnail', 'foto'])),
    'fecha' => $pick($n, ['fecha', 'created_at', 'date']),
    'url' => $abs($pick($n, ['url', 'enlace', 'link']))];
}
$json = json_encode($out, JSON_UNESCAPED_UNICODE);
file_put_contents($cache, $json); echo $json;
