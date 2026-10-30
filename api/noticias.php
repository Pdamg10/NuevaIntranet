<?php
// Proxy enriquecido de noticias de CVG Alcasa:
// Combina el endpoint JSON (/api/noticias) con el portal HTML (http://alcasa.com.ve:5001/)
// para obtener miniatura real (/uploads/... o copia optimizada en /assets/img/noticias/),
// categoría oficial, subtítulo, resumen/descripción, fecha y hora exacta.
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

const BASE = 'http://alcasa.com.ve:5001';
const TTL = 120;

$includeAll = !empty($_GET['include_all']);
$cache = sys_get_temp_dir() . '/cvg_alcasa_noticias_v4' . ($includeAll ? '_all' : '') . '.json';
$fallbackLocal = __DIR__ . '/../data/noticias.json';

if (is_file($cache) && time() - filemtime($cache) < TTL && !isset($_GET['nocache'])) {
    readfile($cache);
    exit;
}

function httpGet(string $url, int $timeout = 6): string {
    if (!function_exists('curl_init')) return '';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code === 200 && is_string($res)) ? $res : '';
}

$rawApi = httpGet(BASE . '/api/noticias', 7);
$d = $rawApi ? json_decode($rawApi, true) : null;

if (!is_array($d) || empty($d)) {
    if (is_file($cache)) {
        readfile($cache);
        exit;
    }
    if (is_file($fallbackLocal)) {
        readfile($fallbackLocal);
        exit;
    }
    http_response_code(502);
    echo '[]';
    exit;
}

// Extraer miniaturas, categorías y resúmenes desde las páginas principales del periódico digital
$htmlMeta = [];
foreach ([BASE . '/', BASE . '/?page=2'] as $pageUrl) {
    $html = httpGet($pageUrl, 5);
    if (!$html) continue;
    if (preg_match_all('/<article class="noticia-card">(.*?)<\/article>/s', $html, $matches)) {
        foreach ($matches[1] as $cardHtml) {
            if (!preg_match('/href="\/noticia\/(\d+)"/', $cardHtml, $idM)) continue;
            $nid = (string)$idM[1];
            $img = '';
            if (preg_match('/<img\s+src="([^"]+)"\s+class="noticia-imagen"/i', $cardHtml, $imgM)) {
                $img = trim($imgM[1]);
            }
            $cat = '';
            if (preg_match('/<span class="categoria">(.*?)<\/span>/s', $cardHtml, $catM)) {
                $cat = trim(html_entity_decode(strip_tags($catM[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            $res = '';
            if (preg_match('/<p class="resumen">(.*?)<\/p>/s', $cardHtml, $resM)) {
                $res = trim(html_entity_decode(strip_tags($resM[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $res = preg_replace('/\xC2\xA0|\s+/u', ' ', $res);
            }
            $htmlMeta[$nid] = [
                'imagen'    => $img,
                'categoria' => $cat,
                'resumen'   => $res
            ];
        }
    }
}

// Cargar respaldo local y lista de noticias ocultas/eliminadas
$localBackupMap = [];
$localCustomNew = [];
$ocultasFile = __DIR__ . '/../storage/noticias_ocultas.json';
$ocultasIds = [];
if (is_file($ocultasFile)) {
    $ocArr = json_decode((string)file_get_contents($ocultasFile), true);
    if (is_array($ocArr)) {
        $ocultasIds = array_map('strval', $ocArr);
    }
}

if (is_file($fallbackLocal)) {
    $localArr = json_decode((string)file_get_contents($fallbackLocal), true);
    if (is_array($localArr)) {
        foreach ($localArr as $ln) {
            if (!empty($ln['id'])) {
                $lid = (string)$ln['id'];
                if (in_array($lid, $ocultasIds, true)) continue;
                $localBackupMap[$lid] = $ln;
                if (!empty($ln['local_edit'])) {
                    $localCustomNew[$lid] = $ln;
                }
            }
        }
    }
}

$list = isset($d['noticias']) && is_array($d['noticias']) ? $d['noticias'] : $d;
$pick = function ($n, $ks) {
    foreach ($ks as $k) {
        if (!empty($n[$k])) {
            return $n[$k];
        }
    }
    return '';
};
$abs = function ($u) {
    if (!$u) return '';
    if (strpos($u, 'http://') === 0 || strpos($u, 'https://') === 0 || strpos($u, '/assets/') === 0) {
        return $u;
    }
    return ($u[0] === '/') ? BASE . $u : $u;
};

$normalizarTextoCVG = function (string $txt): string {
    if ($txt === '') return '';
    // Asegurar que menciones a Alcasa o Aluminio(s) del Caroní lleven siempre CVG delante
    $txt = preg_replace('/(?<!CVG\s)(?<!CVG-)Aluminios?\s+del\s+Caron[íi],?\s*S\.?A\.?/iu', 'CVG Aluminio del Caroní, S.A.', $txt);
    $txt = preg_replace('/(?<!CVG\s)(?<!CVG-)(?<![.@\/])\bAlcasa\b/u', 'CVG Alcasa', $txt);
    $txt = preg_replace('/\bCVG\s+CVG\s+/iu', 'CVG ', $txt);
    return $txt;
};

$out = [];
$seenIds = [];

// 1. Incluir primero noticias gestionadas desde el panel administrativo local (Redacción)
foreach ($localCustomNew as $lid => $ln) {
    $seenIds[$lid] = true;
    $estado = strtolower(trim((string)($ln['estado'] ?? 'publicada')));
    if (!$includeAll && $estado !== 'publicada') {
        continue;
    }
    $imgPortada = (string)($ln['imagen'] ?? '/assets/img/noticias/noticia-773.jpg');
    $imgs = [];
    if (!empty($ln['imagenes']) && is_array($ln['imagenes'])) {
        foreach (array_slice($ln['imagenes'], 0, 20) as $im) {
            if (is_string($im) && trim($im) !== '') {
                $imgs[] = ['url' => trim($im), 'pie' => ''];
            } elseif (is_array($im) && !empty($im['url'])) {
                $imgs[] = ['url' => (string)$im['url'], 'pie' => (string)($im['pie'] ?? '')];
            }
        }
    }
    if (empty($imgs)) {
        $imgs[] = ['url' => $imgPortada, 'pie' => (string)($ln['pie_foto'] ?? '')];
    }
    $imgPortada = $imgs[0]['url'] ?? $imgPortada;

    $out[] = [
        'id'           => $lid,
        'estado'       => $estado ?: 'publicada',
        'categoria'    => $normalizarTextoCVG((string)($ln['categoria'] ?? 'Gestión Industrial')),
        'subtitulo'    => $normalizarTextoCVG((string)($ln['subtitulo'] ?? '')),
        'titulo'       => $normalizarTextoCVG((string)($ln['titulo'] ?? '')),
        'resumen'      => $normalizarTextoCVG((string)($ln['resumen'] ?? '')),
        'cuerpo'       => $normalizarTextoCVG((string)($ln['cuerpo'] ?? ($ln['resumen'] ?? ''))),
        'imagen'       => $imgPortada,
        'imagenes'     => $imgs,
        'pie_foto'     => (string)($ln['pie_foto'] ?? ($imgs[0]['pie'] ?? '')),
        'autor'        => (string)($ln['autor'] ?? 'Redacción CVG Alcasa'),
        'programacion' => (string)($ln['programacion'] ?? ''),
        'etiquetas'    => is_array($ln['etiquetas'] ?? null) ? $ln['etiquetas'] : [],
        'fecha'        => (string)($ln['fecha'] ?? ''),
        'fecha_solo'   => (string)($ln['fecha_solo'] ?? ''),
        'hora_solo'    => (string)($ln['hora_solo'] ?? ''),
        'url'          => (string)($ln['url'] ?? (BASE . '/minimal'))
    ];
}

// 2. Incorporar el feed de :5001 enriquecido
foreach ($list as $n) {
    if (!is_array($n)) continue;
    $id = (string)$pick($n, ['id', 'noticia_id']);
    if ($id !== '' && (isset($seenIds[$id]) || in_array($id, $ocultasIds, true))) {
        continue;
    }
    $meta = $htmlMeta[$id] ?? [];
    $backup = $localBackupMap[$id] ?? [];

    $tituloOrig = trim((string)$pick($n, ['titulo', 'title']));
    $subOrig = trim((string)$pick($n, ['subtitulo', 'categoria', 'category']));

    // Si existe título curado en el respaldo institucional o si el título es genérico, enriquecerlo
    $titulosGenericos = ['circular', 'información', 'informacion', 'invitación', 'invitacion', 'aviso', 'comunicado'];
    if (!empty($backup['titulo'])) {
        $tituloFinal = $backup['titulo'];
    } elseif (in_array(mb_strtolower($tituloOrig, 'UTF-8'), $titulosGenericos, true) && $subOrig !== '') {
        $tituloFinal = $tituloOrig . ': ' . $subOrig;
    } else {
        $tituloFinal = $tituloOrig;
    }

    $categoria = ($meta['categoria'] ?? '')
        ?: ($backup['categoria'] ?? '')
        ?: ($pick($n, ['categoria', 'category']) ?: ($subOrig && mb_strlen($subOrig) <= 42 ? $subOrig : 'Comunicado Oficial'));

    $metaResumen = $meta['resumen'] ?? '';
    $backupResumen = $backup['resumen'] ?? '';
    if ($backupResumen !== '' && ($metaResumen === '' || substr($metaResumen, -3) === '...' || mb_strlen($backupResumen) >= mb_strlen($metaResumen))) {
        $resumen = $backupResumen;
    } else {
        $resumen = $metaResumen ?: $pick($n, ['resumen', 'descripcion', 'extracto']);
    }

    if (!$resumen || stripos($resumen, 'se anexa información de interés colectivo') !== false) {
        if (!empty($backup['resumen']) && stripos($backup['resumen'], 'se anexa información') === false) {
            $resumen = $backup['resumen'];
        } elseif ($subOrig !== '') {
            $resumen = $subOrig . '. Comunicado oficial emitido por la Gerencia de Comunicación Estratégica de CVG Alcasa para conocimiento de toda la clase trabajadora.';
        }
    }

    // Priorizar miniatura local optimizada si existe en /assets/img/noticias/noticia-{id}.jpg
    $localThumbRel = '/assets/img/noticias/noticia-' . $id . '.jpg';
    $localThumbAbs = __DIR__ . '/..' . $localThumbRel;
    if ($id !== '' && is_file($localThumbAbs)) {
        $imagen = $localThumbRel;
    } elseif (!empty($meta['imagen'])) {
        $imagen = $abs($meta['imagen']);
    } elseif (!empty($backup['imagen'])) {
        $imagen = $backup['imagen'];
    } else {
        $imagen = $abs($pick($n, ['imagen', 'portada', 'miniatura', 'imagen_url', 'thumbnail', 'foto']));
    }

    $fechaRaw = trim((string)$pick($n, ['fecha', 'created_at', 'date']));
    $partesFecha = preg_split('/\s+/', $fechaRaw, 2);
    $fechaSolo = $partesFecha[0] ?? $fechaRaw;
    $horaSolo  = $partesFecha[1] ?? '';

    $imgsRem = [];
    if (!empty($backup['imagenes']) && is_array($backup['imagenes'])) {
        foreach (array_slice($backup['imagenes'], 0, 20) as $im) {
            if (is_string($im) && trim($im) !== '') {
                $imgsRem[] = ['url' => trim($im), 'pie' => ''];
            } elseif (is_array($im) && !empty($im['url'])) {
                $imgsRem[] = ['url' => (string)$im['url'], 'pie' => (string)($im['pie'] ?? '')];
            }
        }
    }
    if (empty($imgsRem)) {
        $imgsRem[] = ['url' => $imagen, 'pie' => (string)($backup['pie_foto'] ?? '')];
    }

    $estadoRem = strtolower(trim((string)($backup['estado'] ?? 'publicada')));
    if (!$includeAll && $estadoRem !== 'publicada') {
        continue;
    }

    $out[] = [
        'id'           => $id,
        'estado'       => $estadoRem ?: 'publicada',
        'categoria'    => $normalizarTextoCVG($categoria),
        'subtitulo'    => $normalizarTextoCVG($subOrig),
        'titulo'       => $normalizarTextoCVG($tituloFinal),
        'resumen'      => $normalizarTextoCVG($resumen),
        'cuerpo'       => $normalizarTextoCVG((string)($backup['cuerpo'] ?? $resumen)),
        'imagen'       => $imgsRem[0]['url'] ?? $imagen,
        'imagenes'     => $imgsRem,
        'pie_foto'     => (string)($backup['pie_foto'] ?? ($imgsRem[0]['pie'] ?? '')),
        'autor'        => (string)($backup['autor'] ?? 'Gerencia de Comunicación Estratégica'),
        'programacion' => (string)($backup['programacion'] ?? ''),
        'etiquetas'    => is_array($backup['etiquetas'] ?? null) ? $backup['etiquetas'] : ['CVG Alcasa', 'Prensa'],
        'fecha'        => $fechaRaw,
        'fecha_solo'   => $fechaSolo,
        'hora_solo'    => $horaSolo,
        'url'          => $abs($pick($n, ['url', 'enlace', 'link'])) ?: (BASE . '/minimal')
    ];
}

$json = json_encode($out, JSON_UNESCAPED_UNICODE);
@file_put_contents($cache, $json);
echo $json;
