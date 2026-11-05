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
$reqId = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
$reqPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 0;
$qFilter = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$fechaFilter = isset($_GET['fecha']) ? trim((string)$_GET['fecha']) : '';
$mesFilter = isset($_GET['mes']) ? trim((string)$_GET['mes']) : '';
$anioFilter = isset($_GET['anio']) ? trim((string)$_GET['anio']) : '';
$hasFilters = ($qFilter !== '' || $fechaFilter !== '' || $mesFilter !== '' || $anioFilter !== '');
$filterHash = $hasFilters ? '_' . substr(md5($qFilter . '|' . $fechaFilter . '|' . $mesFilter . '|' . $anioFilter), 0, 8) : '';
$cache = sys_get_temp_dir() . '/cvg_alcasa_noticias_v5' . ($reqId !== '' ? '_id_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $reqId) : (($includeAll ? '_all' : '') . ($reqPage > 0 ? '_p' . $reqPage : '') . $filterHash)) . '.json';
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

$normalizarTextoCVG = function (string $txt): string {
    if ($txt === '') return '';
    $txt = preg_replace('/(?<!CVG\s)(?<!CVG-)Aluminios?\s+del\s+Caron[íi],?\s*S\.?A\.?/iu', 'CVG Aluminio del Caroní, S.A.', $txt);
    $txt = preg_replace('/(?<!CVG\s)(?<!CVG-)(?<![.@\/])\bAlcasa\b/u', 'CVG Alcasa', $txt);
    $txt = preg_replace('/\bCVG\s+CVG\s+/iu', 'CVG ', $txt);
    return $txt;
};

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

$fallbackPool = [
    '/assets/img/noticias/noticia-761.jpg',
    '/assets/img/noticias/noticia-760.jpg',
    '/assets/img/noticias/noticia-759.jpg',
    '/assets/img/noticias/noticia-758.jpg',
    '/assets/img/noticias/noticia-757.jpg',
    '/assets/img/noticias/noticia-753.jpg',
    '/assets/img/noticias/noticia-752.jpg',
    '/assets/img/noticias/noticia-751.jpg',
    '/assets/img/noticias/noticia-750.jpg',
    '/assets/img/noticias/noticia-749.jpg',
    '/assets/img/noticias/noticia-748.jpg',
    '/assets/img/noticias/noticia-739.jpg'
];

// Modo detalle individual (?id=N) para minimal.html
if ($reqId !== '') {
    if (in_array($reqId, $ocultasIds, true)) {
        http_response_code(404);
        echo json_encode(['error' => 'Noticia no disponible'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (isset($localCustomNew[$reqId])) {
        $ln = $localCustomNew[$reqId];
        $imgs = (!empty($ln['imagenes']) && is_array($ln['imagenes'])) ? $ln['imagenes'] : [['url' => $ln['imagen'] ?? '']];
        $imgUrl = (string)($imgs[0]['url'] ?? ($ln['imagen'] ?? '/assets/img/noticias/noticia-761.jpg'));
        $cuerpoTxt = (string)($ln['cuerpo'] ?? ($ln['resumen'] ?? ''));
        $cuerpoHtml = (strpos($cuerpoTxt, '<p') !== false || strpos($cuerpoTxt, '<div') !== false)
            ? $cuerpoTxt
            : ('<p>' . nl2br(htmlspecialchars($cuerpoTxt, ENT_QUOTES | ENT_HTML5, 'UTF-8')) . '</p>');
        $detJson = json_encode([
            'id'            => $reqId,
            'esVivo'        => false,
            'url'           => BASE . '/noticia/' . rawurlencode($reqId),
            'titulo'        => $normalizarTextoCVG((string)($ln['titulo'] ?? 'Comunicado')),
            'subtitulo'     => $normalizarTextoCVG((string)($ln['subtitulo'] ?? '')),
            'categoria'     => $normalizarTextoCVG((string)($ln['categoria'] ?? 'Institucional')),
            'autor'         => (string)($ln['autor'] ?? 'Comunicación Estratégica'),
            'fecha'         => trim(((string)($ln['fecha_solo'] ?? '')) . ' ' . ((string)($ln['hora_solo'] ?? ''))),
            'imagen'        => $imgUrl,
            'nombreArchivo' => basename($imgUrl),
            'cuerpo'        => $cuerpoHtml
        ], JSON_UNESCAPED_UNICODE);
        @file_put_contents($cache, $detJson);
        echo $detJson;
        exit;
    }

    $detHtml = httpGet(BASE . '/noticia/' . rawurlencode($reqId), 6);
    if ($detHtml !== '' && preg_match('/<article class="detalle-noticia">(.*?)<\/article>/s', $detHtml, $artM)) {
        $artHtml = $artM[1];
        $tit = '';
        if (preg_match('/<h1>(.*?)<\/h1>/s', $artHtml, $m)) {
            $tit = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $sub = '';
        if (preg_match('/<h2 class="subtitulo-detalle">(.*?)<\/h2>/s', $artHtml, $m)) {
            $sub = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $aut = 'Comunicación Estratégica';
        if (preg_match('/👤\s*([^<]+)/u', $artHtml, $m)) {
            $aut = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $fec = '';
        if (preg_match('/📅\s*([0-9\/]+\s*[0-9:]*)/u', $artHtml, $m)) {
            $fec = trim($m[1]);
        }
        $cat = 'Institucional';
        if (preg_match('/<span class="categoria">(.*?)<\/span>/s', $artHtml, $m)) {
            $cat = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $img = '';
        if (preg_match('/<div class="imagen-principal">.*?<img\s+src="([^"]+)"/s', $artHtml, $m)) {
            $img = trim($m[1]);
            if ($img !== '' && $img[0] === '/') {
                $img = BASE . $img;
            }
        }
        $nomArchivo = '';
        if (preg_match('/<div class="nombre-archivo"[^>]*>(.*?)<\/div>/s', $artHtml, $m)) {
            $nomArchivo = trim(str_replace('📎', '', html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        }
        $cuerpo = '';
        if (preg_match('/<div class="texto-principal">(.*?)<\/div>\s*<\/div>\s*<div class="acciones-detalle">/s', $artHtml, $m) ||
            preg_match('/<div class="texto-principal">(.*?)<\/div>/s', $artHtml, $m)) {
            $cuerpo = trim($m[1]);
            $cuerpo = preg_replace_callback('/\b(src|href)="(\/[^"]+)"/i', function ($mm) {
                return $mm[1] . '="' . BASE . $mm[2] . '"';
            }, $cuerpo);
        }

        $detJson = json_encode([
            'id'            => $reqId,
            'esVivo'        => true,
            'url'           => BASE . '/noticia/' . rawurlencode($reqId),
            'titulo'        => $tit ?: ('Comunicado #' . $reqId),
            'subtitulo'     => $sub,
            'categoria'     => $cat,
            'autor'         => $aut,
            'fecha'         => $fec,
            'imagen'        => $img,
            'nombreArchivo' => $nomArchivo,
            'cuerpo'        => $cuerpo
        ], JSON_UNESCAPED_UNICODE);
        @file_put_contents($cache, $detJson);
        echo $detJson;
        exit;
    }

    if (isset($localBackupMap[$reqId])) {
        $bk = $localBackupMap[$reqId];
        $imgs = (!empty($bk['imagenes']) && is_array($bk['imagenes'])) ? $bk['imagenes'] : [['url' => $bk['imagen'] ?? '']];
        $imgUrl = (string)($imgs[0]['url'] ?? ($bk['imagen'] ?? '/assets/img/noticias/noticia-761.jpg'));
        $cuerpoTxt = (string)($bk['cuerpo'] ?? ($bk['resumen'] ?? ''));
        $cuerpoHtml = (strpos($cuerpoTxt, '<p') !== false || strpos($cuerpoTxt, '<div') !== false)
            ? $cuerpoTxt
            : ('<p>' . nl2br(htmlspecialchars($cuerpoTxt, ENT_QUOTES | ENT_HTML5, 'UTF-8')) . '</p>');
        $detJson = json_encode([
            'id'            => $reqId,
            'esVivo'        => false,
            'url'           => BASE . '/noticia/' . rawurlencode($reqId),
            'titulo'        => $normalizarTextoCVG((string)($bk['titulo'] ?? 'Comunicado')),
            'subtitulo'     => $normalizarTextoCVG((string)($bk['subtitulo'] ?? '')),
            'categoria'     => $normalizarTextoCVG((string)($bk['categoria'] ?? 'Institucional')),
            'autor'         => (string)($bk['autor'] ?? 'Comunicación Estratégica'),
            'fecha'         => trim(((string)($bk['fecha_solo'] ?? ($bk['fecha'] ?? ''))) . ' ' . ((string)($bk['hora_solo'] ?? ''))),
            'imagen'        => $imgUrl,
            'nombreArchivo' => basename($imgUrl),
            'cuerpo'        => $cuerpoHtml
        ], JSON_UNESCAPED_UNICODE);
        echo $detJson;
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Noticia no encontrada'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Modo paginado o filtrado directo desde http://alcasa.com.ve:5001/?page=N (para Sala de Prensa prensa.html y minimal.html)
if ($reqPage > 0 || $hasFilters) {
    $effPage = $reqPage > 0 ? $reqPage : 1;
    $queryParams = ['page' => $effPage];
    if ($qFilter !== '') $queryParams['q'] = $qFilter;
    if ($fechaFilter !== '') $queryParams['fecha'] = $fechaFilter;
    if ($mesFilter !== '') $queryParams['mes'] = $mesFilter;
    if ($anioFilter !== '') $queryParams['anio'] = $anioFilter;

    $pageHtml = httpGet(BASE . '/?' . http_build_query($queryParams), 6);
    $totalPages = $hasFilters ? 1 : 72;
    $pageItems = [];

    if ($pageHtml !== '') {
        if (preg_match_all('/[?&]page=(\d+)/', $pageHtml, $pm) && !empty($pm[1])) {
            $maxP = max(array_map('intval', $pm[1]));
            if ($maxP > 0) $totalPages = max($effPage, $maxP);
        }

        if ($effPage === 1 && !$hasFilters && !empty($localCustomNew)) {
            foreach ($localCustomNew as $lid => $ln) {
                $estado = strtolower(trim((string)($ln['estado'] ?? 'publicada')));
                if ($estado !== 'publicada') continue;
                $pageItems[] = $ln;
            }
        }

        if (preg_match_all('/<article class="noticia-card">(.*?)<\/article>/s', $pageHtml, $matches)) {
            foreach ($matches[1] as $cardHtml) {
                if (!preg_match('/href="\/noticia\/(\d+)"/', $cardHtml, $idM)) continue;
                $nid = (string)$idM[1];
                if (in_array($nid, $ocultasIds, true)) continue;

                $backup = $localBackupMap[$nid] ?? [];
                $titOrig = '';
                if (preg_match('/<h2><a[^>]*>(.*?)<\/a><\/h2>/s', $cardHtml, $tm)) {
                    $titOrig = trim(html_entity_decode(strip_tags($tm[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }
                $subOrig = '';
                if (preg_match('/<p class="subtitulo">(.*?)<\/p>/s', $cardHtml, $sm)) {
                    $subOrig = trim(html_entity_decode(strip_tags($sm[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }
                $catOrig = '';
                if (preg_match('/<span class="categoria">(.*?)<\/span>/s', $cardHtml, $cm)) {
                    $catOrig = trim(html_entity_decode(strip_tags($cm[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }
                $fecOrig = '';
                if (preg_match('/📅\s*([0-9\/]+\s+[0-9:]+)/u', $cardHtml, $fm)) {
                    $fecOrig = trim($fm[1]);
                }
                $autOrig = 'Gerencia de Comunicación Estratégica';
                if (preg_match('/👤\s*([^<]+)/u', $cardHtml, $am)) {
                    $autOrig = trim(html_entity_decode($am[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }
                $resOrig = '';
                if (preg_match('/<p class="resumen">(.*?)<\/p>/s', $cardHtml, $rm)) {
                    $resOrig = trim(html_entity_decode(strip_tags($rm[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $resOrig = preg_replace('/\xC2\xA0|\s+/u', ' ', $resOrig);
                }
                $imgOrig = '';
                if (preg_match('/<img\s+src="([^"]+)"\s+class="noticia-imagen"/i', $cardHtml, $im)) {
                    $imgOrig = trim($im[1]);
                    if ($imgOrig !== '' && $imgOrig[0] === '/') {
                        $imgOrig = BASE . $imgOrig;
                    }
                }

                $localThumbRel = '/assets/img/noticias/noticia-' . $nid . '.jpg';
                $localThumbAbs = __DIR__ . '/..' . $localThumbRel;
                if (is_file($localThumbAbs)) {
                    $imagenFinal = $localThumbRel;
                } elseif ($imgOrig !== '') {
                    $imagenFinal = $imgOrig;
                } elseif (!empty($backup['imagen'])) {
                    $imagenFinal = (string)$backup['imagen'];
                } else {
                    $imagenFinal = $fallbackPool[abs((int)$nid) % count($fallbackPool)];
                }

                $tituloFinal = !empty($backup['titulo']) ? (string)$backup['titulo'] : ($titOrig ?: ('Comunicado Oficial #' . $nid));
                $resumenFinal = (!empty($backup['resumen']) && mb_strlen((string)$backup['resumen']) >= mb_strlen($resOrig))
                    ? (string)$backup['resumen']
                    : ($resOrig ?: ($subOrig ? $subOrig . '. Comunicado oficial de CVG Alcasa.' : $tituloFinal));

                $partesF = preg_split('/\s+/', $fecOrig, 2);
                $imgsArr = (!empty($backup['imagenes']) && is_array($backup['imagenes']))
                    ? $backup['imagenes']
                    : [['url' => $imagenFinal, 'pie' => $tituloFinal]];

                $pageItems[] = [
                    'id'         => $nid,
                    'estado'     => 'publicada',
                    'categoria'  => $normalizarTextoCVG($catOrig ?: ($backup['categoria'] ?? ($subOrig ?: 'Comunicado Oficial'))),
                    'subtitulo'  => $normalizarTextoCVG($subOrig),
                    'titulo'     => $normalizarTextoCVG($tituloFinal),
                    'resumen'    => $normalizarTextoCVG($resumenFinal),
                    'cuerpo'     => $normalizarTextoCVG((string)($backup['cuerpo'] ?? $resumenFinal)),
                    'autor'      => $autOrig,
                    'imagen'     => $imgsArr[0]['url'] ?? $imagenFinal,
                    'imagenes'   => $imgsArr,
                    'fecha'      => $fecOrig,
                    'fecha_solo' => $partesF[0] ?? $fecOrig,
                    'hora_solo'  => $partesF[1] ?? '',
                    'url'        => BASE . '/noticia/' . $nid
                ];
            }
        }
    }

    if ($pageHtml === '' && empty($pageItems) && !empty($localBackupMap)) {
        $allLocal = array_values($localBackupMap);
        if ($hasFilters) {
            $allLocal = array_values(array_filter($allLocal, function ($it) use ($qFilter, $fechaFilter, $mesFilter, $anioFilter) {
                if ($qFilter !== '') {
                    $hay = mb_strtolower(($it['titulo'] ?? '') . ' ' . ($it['resumen'] ?? '') . ' ' . ($it['categoria'] ?? '') . ' ' . ($it['autor'] ?? ''), 'UTF-8');
                    if (mb_strpos($hay, mb_strtolower($qFilter, 'UTF-8')) === false) return false;
                }
                $fSolo = (string)($it['fecha_solo'] ?? ($it['fecha'] ?? ''));
                if ($fechaFilter !== '') {
                    // Convertir YYYY-MM-DD a DD/MM/YYYY
                    $pF = explode('-', $fechaFilter);
                    $fTarget = count($pF) === 3 ? ($pF[2] . '/' . $pF[1] . '/' . $pF[0]) : $fechaFilter;
                    if (strpos($fSolo, $fTarget) === false) return false;
                }
                if ($mesFilter !== '' && !preg_match('/^\d{2}\/' . preg_quote($mesFilter, '/') . '\//', $fSolo)) {
                    return false;
                }
                if ($anioFilter !== '' && strpos($fSolo, $anioFilter) === false) {
                    return false;
                }
                return true;
            }));
        }
        $perPage = 10;
        $totalPages = max(1, (int)ceil(count($allLocal) / $perPage));
        $pageItems = array_slice($allLocal, ($effPage - 1) * $perPage, $perPage);
    }

    $respJson = json_encode([
        'page'        => $effPage,
        'total_pages' => $totalPages,
        'noticias'    => $pageItems
    ], JSON_UNESCAPED_UNICODE);
    @file_put_contents($cache, $respJson);
    echo $respJson;
    exit;
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

// Extraer miniaturas, categorías y resúmenes desde las páginas 1 a 5 del periódico digital
$htmlMeta = [];
foreach ([BASE . '/?page=1', BASE . '/?page=2', BASE . '/?page=3', BASE . '/?page=4', BASE . '/?page=5'] as $pageUrl) {
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
    $imgPortada = (string)($ln['imagen'] ?: '/assets/img/noticias/noticia-761.jpg');
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
    $fallbackPool = [
        '/assets/img/noticias/noticia-761.jpg',
        '/assets/img/noticias/noticia-760.jpg',
        '/assets/img/noticias/noticia-759.jpg',
        '/assets/img/noticias/noticia-758.jpg',
        '/assets/img/noticias/noticia-757.jpg',
        '/assets/img/noticias/noticia-753.jpg',
        '/assets/img/noticias/noticia-752.jpg',
        '/assets/img/noticias/noticia-751.jpg',
        '/assets/img/noticias/noticia-750.jpg',
        '/assets/img/noticias/noticia-749.jpg',
        '/assets/img/noticias/noticia-748.jpg',
        '/assets/img/noticias/noticia-739.jpg'
    ];
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
    if ($imagen === '') {
        $imagen = $fallbackPool[abs((int)$id) % count($fallbackPool)];
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
