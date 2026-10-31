<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/_lib.php';

header('Content-Type: application/json; charset=utf-8');

const REMOTE_PRESS_BASE = 'http://alcasa.com.ve:5001';
const MAX_IMAGES_PER_NEWS = 20;

$storageDir = __DIR__ . '/../storage';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0750, true);
}

$usersFile = $storageDir . '/usuarios_config.json';
$noticiasFile = __DIR__ . '/../data/noticias.json';
$ocultasFile = $storageDir . '/noticias_ocultas.json';
$historialFile = $storageDir . '/noticias_historial.json';

function limpiarCachesNoticias(): void {
    foreach ([
        sys_get_temp_dir() . '/cvg_alcasa_noticias_v3.json',
        sys_get_temp_dir() . '/cvg_alcasa_noticias_v4.json',
        sys_get_temp_dir() . '/cvg_alcasa_noticias_v4_all.json'
    ] as $cf) {
        if (is_file($cf)) {
            @unlink($cf);
        }
    }
}

// Inicializar archivo de cuentas y niveles de acceso si no existe
if (!is_file($usersFile)) {
    $defaultUsers = [
        [
            'usuario'  => 'admin',
            'password' => 'D10998$a*',
            'nombre'   => 'Administrador General CVG Alcasa',
            'nivel'    => 'Nivel 1 — Super Administrador (Control Total: Prensa, Contactos y CV)',
            'rol'      => 'admin',
            'permisos' => ['prensa', 'prensa_eliminar', 'contactos', 'cv']
        ],
        [
            'usuario'  => 'comunicacion',
            'password' => 'periodico2024',
            'nombre'   => 'Gerencia de Comunicación Estratégica',
            'nivel'    => 'Nivel 2 — Editor de Sala de Prensa (Redactar, Editar, Borradores y Publicar)',
            'rol'      => 'prensa',
            'permisos' => ['prensa']
        ],
        [
            'usuario'  => 'comercializacion',
            'password' => 'AlcasaCom2026*',
            'nombre'   => 'Gerencia de Comercialización',
            'nivel'    => 'Nivel 3 — Gestor Exclusivo de Contactos Comerciales y Cotizaciones',
            'rol'      => 'contactos',
            'permisos' => ['contactos']
        ],
        [
            'usuario'  => 'talento',
            'password' => 'AlcasaCV2026*',
            'nombre'   => 'Gerencia de Personal / Talento Humano',
            'nivel'    => 'Nivel 4 — Gestor Exclusivo de Postulaciones de Empleo y CV',
            'rol'      => 'cv',
            'permisos' => ['cv']
        ]
    ];
    @file_put_contents($usersFile, json_encode($defaultUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function cargarUsuarios(string $file): array {
    if (!is_file($file)) return [];
    $data = json_decode((string)file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function cargarHistorial(string $file): array {
    if (!is_file($file)) return [];
    $data = json_decode((string)file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function registrarVersionHistorial(string $file, string $id, array $registro, string $usuario, string $notaAccion = 'Guardado'): array {
    $todo = cargarHistorial($file);
    $listaId = isset($todo[$id]) && is_array($todo[$id]) ? $todo[$id] : [];
    $numVer = count($listaId) + 1;
    $snap = [
        'version'      => $numVer,
        'fecha'        => date('d/m/Y H:i:s'),
        'usuario'      => $usuario,
        'accion'       => $notaAccion,
        'estado'       => $registro['estado'] ?? 'publicada',
        'datos'        => $registro
    ];
    array_unshift($listaId, $snap);
    $todo[$id] = array_slice($listaId, 0, 25);
    @file_put_contents($file, json_encode($todo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return $todo[$id];
}

function normalizarGaleriaImagenes($rawImagenes, string $fallbackPortada = '/assets/img/noticias/noticia-761.jpg', string $fallbackPie = ''): array {
    $out = [];
    if (is_string($rawImagenes) && trim($rawImagenes) !== '') {
        $decoded = json_decode($rawImagenes, true);
        if (is_array($decoded)) {
            $rawImagenes = $decoded;
        }
    }
    if (is_array($rawImagenes)) {
        foreach ($rawImagenes as $item) {
            if (count($out) >= MAX_IMAGES_PER_NEWS) break;
            if (is_string($item) && trim($item) !== '') {
                $out[] = ['url' => trim($item), 'pie' => ''];
            } elseif (is_array($item) && !empty($item['url'])) {
                $out[] = [
                    'url' => trim((string)$item['url']),
                    'pie' => trim((string)($item['pie'] ?? ''))
                ];
            }
        }
    }
    if (empty($out) && $fallbackPortada !== '') {
        $out[] = ['url' => $fallbackPortada, 'pie' => $fallbackPie];
    }
    return array_slice($out, 0, MAX_IMAGES_PER_NEWS);
}

function guardarArchivosImagenSubidos(string $keyName, string $noticiaId = 'temp'): array {
    $guardadas = [];
    if (empty($_FILES[$keyName])) return $guardadas;

    $imgDir = __DIR__ . '/../assets/img/noticias';
    if (!is_dir($imgDir)) {
        @mkdir($imgDir, 0755, true);
    }

    $files = $_FILES[$keyName];
    $isMulti = is_array($files['name']);
    $count = $isMulti ? count($files['name']) : 1;
    $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $noticiaId) ?: 'noti';

    for ($i = 0; $i < $count && count($guardadas) < MAX_IMAGES_PER_NEWS; $i++) {
        $err  = $isMulti ? ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) : ($files['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) continue;

        $size = (int)($isMulti ? $files['size'][$i] : $files['size']);
        $name = (string)($isMulti ? $files['name'][$i] : $files['name']);
        $tmp  = (string)($isMulti ? $files['tmp_name'][$i] : $files['tmp_name']);

        if ($size > 10 * 1024 * 1024) continue;
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;
        $extNorm = ($ext === 'jpeg') ? 'jpg' : $ext;

        $fileName = 'noticia-' . $safeId . '-' . time() . '-' . ($i + 1) . '-' . substr(md5($name . microtime()), 0, 5) . '.' . $extNorm;
        $absDest = $imgDir . '/' . $fileName;
        if (move_uploaded_file($tmp, $absDest)) {
            $guardadas[] = [
                'url' => '/assets/img/noticias/' . $fileName,
                'pie' => '',
                'abs' => $absDest
            ];
        }
    }
    return $guardadas;
}

function intentarLoginRemoto5001(string $user, string $pass): array {
    if (!function_exists('curl_init')) {
        return ['conectado' => false, 'detalle' => 'Extensión cURL no disponible'];
    }
    $cookieJar = sys_get_temp_dir() . '/alcasa_5001_session_' . session_id() . '.txt';
    $ch = curl_init(REMOTE_PRESS_BASE . '/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['username' => $user, 'password' => $pass]),
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $html = (string)curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200 && $html !== '' && stripos($html, 'Usuario o contraseña incorrectos') === false && stripos($html, 'logout') !== false) {
        return ['conectado' => true, 'detalle' => 'Autenticado también en servidor remoto :5001'];
    }
    return ['conectado' => false, 'detalle' => 'Modo local activo (servidor :5001 requiere credencial propia o solo lectura)'];
}

function publicarRemoto5001(array $noticia, ?string $imagenAbsPath): bool {
    if (!function_exists('curl_init')) return false;
    $cookieJar = sys_get_temp_dir() . '/alcasa_5001_session_' . session_id() . '.txt';
    if (!is_file($cookieJar)) return false;

    $textoCuerpo = $noticia['cuerpo'] ?? ($noticia['resumen'] ?? '');
    $postFields = [
        'titulo'    => $noticia['titulo'] ?? '',
        'subtitulo' => $noticia['subtitulo'] ?? ($noticia['categoria'] ?? ''),
        'categoria' => $noticia['categoria'] ?? 'Institucional',
        'resumen'   => $noticia['resumen'] ?? '',
        'contenido' => '<p>' . nl2br(htmlspecialchars((string)$textoCuerpo, ENT_QUOTES, 'UTF-8')) . '</p>'
    ];
    if ($imagenAbsPath && is_file($imagenAbsPath)) {
        $postFields['imagen'] = new CURLFile($imagenAbsPath);
    }

    $ch = curl_init(REMOTE_PRESS_BASE . '/nueva');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_COOKIEFILE     => $cookieJar,
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 6
    ]);
    curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code >= 200 && $code < 400);
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

if ($action === 'accounts') {
    $usuarios = cargarUsuarios($usersFile);
    echo json_encode([
        'success'  => true,
        'accounts' => $usuarios
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'status') {
    if (!empty($_SESSION['alcasa_user'])) {
        echo json_encode([
            'success'       => true,
            'authenticated' => true,
            'user'          => $_SESSION['alcasa_user']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success'       => true,
            'authenticated' => false
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($action === 'logout') {
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    echo json_encode(['success' => true, 'mensaje' => 'Sesión cerrada correctamente.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'login') {
    $in = input_data();
    $u = trim((string)($in['usuario'] ?? $in['username'] ?? ''));
    $p = trim((string)($in['password'] ?? ''));

    if ($u === '' || $p === '') {
        fail('Ingresa usuario y contraseña.');
    }

    $usuarios = cargarUsuarios($usersFile);
    $encontrado = null;
    foreach ($usuarios as $usr) {
        if (strcasecmp((string)$usr['usuario'], $u) === 0 && hash_equals((string)$usr['password'], $p)) {
            $encontrado = $usr;
            break;
        }
    }

    // Si no está en la tabla local, verificar si autentica directamente contra :5001/login
    $remoto = intentarLoginRemoto5001($u, $p);
    if (!$encontrado && $remoto['conectado']) {
        $encontrado = [
            'usuario'  => $u,
            'nombre'   => 'Editor Remoto Periódico Digital (:5001)',
            'nivel'    => 'Nivel 2 — Editor de Sala de Prensa (:5001)',
            'rol'      => 'prensa',
            'permisos' => ['prensa']
        ];
    }

    if (!$encontrado) {
        fail('Usuario o contraseña incorrectos.', 401);
    }

    $_SESSION['alcasa_user'] = [
        'usuario'         => $encontrado['usuario'],
        'nombre'          => $encontrado['nombre'],
        'nivel'           => $encontrado['nivel'],
        'rol'             => $encontrado['rol'],
        'permisos'        => $encontrado['permisos'],
        'remoto_5001'     => $remoto['conectado'],
        'remoto_detalle'  => $remoto['detalle']
    ];

    echo json_encode([
        'success' => true,
        'mensaje' => 'Bienvenido, ' . $encontrado['nombre'],
        'user'    => $_SESSION['alcasa_user']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// A partir de aquí se requiere sesión activa con permiso 'prensa'
if (empty($_SESSION['alcasa_user'])) {
    fail('Debes iniciar sesión para realizar esta acción.', 401);
}
$permisos = $_SESSION['alcasa_user']['permisos'] ?? [];
if (!in_array('prensa', $permisos, true) && ($_SESSION['alcasa_user']['rol'] ?? '') !== 'admin') {
    fail('Tu cuenta no tiene permisos para publicar o modificar noticias.', 403);
}

$listaLocal = [];
if (is_file($noticiasFile)) {
    $parsed = json_decode((string)file_get_contents($noticiasFile), true);
    if (is_array($parsed)) $listaLocal = $parsed;
}

if ($action === 'list_all') {
    $histAll = cargarHistorial($historialFile);
    // Invocar internamente noticias.php con include_all=1 para tener tanto las locales como las del feed :5001
    $_GET['include_all'] = '1';
    ob_start();
    include __DIR__ . '/noticias.php';
    $rawJson = (string)ob_get_clean();
    $feed = json_decode($rawJson, true);
    if (!is_array($feed)) {
        $feed = $listaLocal;
    }

    // Asegurar que cualquier borrador/archivada local que no esté en el feed se incluya arriba
    $seen = [];
    $merged = [];
    foreach ($listaLocal as $ln) {
        $lid = (string)($ln['id'] ?? '');
        if ($lid === '') continue;
        $seen[$lid] = true;
        $imgs = normalizarGaleriaImagenes($ln['imagenes'] ?? [], (string)($ln['imagen'] ?? '/assets/img/noticias/noticia-761.jpg'), (string)($ln['pie_foto'] ?? ''));
        $ln['imagenes'] = $imgs;
        $ln['imagen'] = $imgs[0]['url'] ?? ($ln['imagen'] ?? '/assets/img/noticias/noticia-761.jpg');
        $ln['estado'] = strtolower(trim((string)($ln['estado'] ?? 'publicada')));
        $ln['cuerpo'] = (string)($ln['cuerpo'] ?? ($ln['resumen'] ?? ''));
        $ln['autor'] = (string)($ln['autor'] ?? 'Gerencia de Comunicación Estratégica');
        $ln['etiquetas'] = is_array($ln['etiquetas'] ?? null) ? $ln['etiquetas'] : ['CVG Alcasa', 'Prensa'];
        $ln['versiones_count'] = isset($histAll[$lid]) && is_array($histAll[$lid]) ? count($histAll[$lid]) : 1;
        $ln['historial'] = $histAll[$lid] ?? [];
        $merged[] = $ln;
    }
    foreach ($feed as $fn) {
        $fid = (string)($fn['id'] ?? '');
        if ($fid === '' || isset($seen[$fid])) continue;
        $seen[$fid] = true;
        $imgs = normalizarGaleriaImagenes($fn['imagenes'] ?? [], (string)($fn['imagen'] ?? '/assets/img/noticias/noticia-761.jpg'), (string)($fn['pie_foto'] ?? ''));
        $fn['imagenes'] = $imgs;
        $fn['imagen'] = $imgs[0]['url'] ?? ($fn['imagen'] ?? '/assets/img/noticias/noticia-761.jpg');
        $fn['estado'] = strtolower(trim((string)($fn['estado'] ?? 'publicada')));
        $fn['cuerpo'] = (string)($fn['cuerpo'] ?? ($fn['resumen'] ?? ''));
        $fn['autor'] = (string)($fn['autor'] ?? 'Gerencia de Comunicación Estratégica');
        $fn['etiquetas'] = is_array($fn['etiquetas'] ?? null) ? $fn['etiquetas'] : ['CVG Alcasa', 'Prensa'];
        $fn['versiones_count'] = isset($histAll[$fid]) && is_array($histAll[$fid]) ? count($histAll[$fid]) : 1;
        $fn['historial'] = $histAll[$fid] ?? [];
        $merged[] = $fn;
    }

    echo json_encode([
        'success'  => true,
        'noticias' => $merged
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'upload_images') {
    $idTarget = trim((string)($_POST['id'] ?? $_GET['id'] ?? 'galeria'));
    $subidas = guardarArchivosImagenSubidos('imagenes', $idTarget);
    if (empty($subidas)) {
        $subidas = guardarArchivosImagenSubidos('imagen_archivo', $idTarget);
    }
    if (empty($subidas)) {
        fail('No se recibieron imágenes válidas (formatos permitidos: JPG, PNG, WEBP, máx. 10 MB c/u).');
    }
    $cleanList = array_map(function ($it) {
        return ['url' => $it['url'], 'pie' => $it['pie']];
    }, $subidas);

    echo json_encode([
        'success'  => true,
        'imagenes' => $cleanList,
        'mensaje'  => count($cleanList) . ' imagen(es) subida(s) al carrusel.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'history') {
    $idH = trim((string)($_GET['id'] ?? $_POST['id'] ?? ''));
    $histAll = cargarHistorial($historialFile);
    echo json_encode([
        'success'   => true,
        'id'        => $idH,
        'historial' => $histAll[$idH] ?? []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'create' || $action === 'update' || $action === 'save_draft') {
    $in = input_data();
    $idEdit = trim((string)($in['id'] ?? ''));
    $estado = strtolower(trim((string)($in['estado'] ?? ($action === 'save_draft' ? 'borrador' : 'publicada'))));
    if (!in_array($estado, ['publicada', 'borrador', 'revision', 'archivada'], true)) {
        $estado = 'publicada';
    }

    $titulo = clean($in['titulo'] ?? '', 260);
    $categoria = clean($in['categoria'] ?? 'Gestión Industrial', 90);
    $subtitulo = clean($in['subtitulo'] ?? '', 280);
    $resumen = clean($in['resumen'] ?? ($subtitulo ?: ''), 2500);
    $cuerpo = trim((string)($in['cuerpo'] ?? $resumen));
    if ($resumen === '' && $cuerpo !== '') {
        $resumen = clean(strip_tags($cuerpo), 420);
    }
    if ($subtitulo === '' && $resumen !== '') {
        $subtitulo = clean($resumen, 180);
    }

    $autor = clean($in['autor'] ?? ($_SESSION['alcasa_user']['nombre'] ?? 'Gerencia de Comunicación Estratégica'), 120);
    $programacion = clean($in['programacion'] ?? '', 60);
    $pieFoto = clean($in['pie_foto'] ?? '', 240);
    $urlExt = trim((string)($in['url'] ?? ''));
    $fechaInput = trim((string)($in['fecha'] ?? ''));

    // Etiquetas
    $etiquetasRaw = $in['etiquetas'] ?? [];
    if (is_string($etiquetasRaw)) {
        $decodedTags = json_decode($etiquetasRaw, true);
        if (is_array($decodedTags)) {
            $etiquetasRaw = $decodedTags;
        } else {
            $etiquetasRaw = array_filter(array_map('trim', explode(',', $etiquetasRaw)));
        }
    }
    $etiquetas = [];
    if (is_array($etiquetasRaw)) {
        foreach (array_slice($etiquetasRaw, 0, 15) as $tg) {
            $tClean = clean((string)$tg, 40);
            if ($tClean !== '') $etiquetas[] = $tClean;
        }
    }

    if ($titulo === '') {
        fail('El titular de la noticia es obligatorio.');
    }
    if ($resumen === '' && $cuerpo === '') {
        fail('Debes escribir una bajada/resumen o el cuerpo de la noticia.');
    }

    // Determinar ID
    if ($idEdit !== '') {
        $nuevoId = $idEdit;
    } else {
        $maxId = 775;
        foreach ($listaLocal as $item) {
            $num = (int)($item['id'] ?? 0);
            if ($num > $maxId) $maxId = $num;
        }
        $nuevoId = (string)($maxId + 1);
    }

    // Galería de hasta 20 imágenes (JSON enviado desde el editor + archivos nuevos subidos)
    $galeriaExistente = normalizarGaleriaImagenes(
        $in['imagenes_json'] ?? ($in['imagenes'] ?? []),
        trim((string)($in['imagen_actual'] ?? '')),
        $pieFoto
    );

    $nuevasSubidasMulti = guardarArchivosImagenSubidos('imagenes_archivos', $nuevoId);
    $nuevasSubidasSingle = guardarArchivosImagenSubidos('imagen_archivo', $nuevoId);
    $todasSubidas = array_merge($nuevasSubidasMulti, $nuevasSubidasSingle);

    $imagenAbsGuardada = null;
    foreach ($todasSubidas as $sub) {
        if ($imagenAbsGuardada === null && !empty($sub['abs'])) {
            $imagenAbsGuardada = $sub['abs'];
        }
        $galeriaExistente[] = [
            'url' => $sub['url'],
            'pie' => $pieFoto
        ];
    }

    // Filtrar placeholder vacío si se subieron imágenes reales y limitar a 20
    if (count($galeriaExistente) > 1) {
        $galeriaExistente = array_values(array_filter($galeriaExistente, function ($im) {
            return !empty($im['url']);
        }));
    }
    if (empty($galeriaExistente)) {
        $galeriaExistente = [['url' => '/assets/img/noticias/noticia-761.jpg', 'pie' => $pieFoto]];
    }
    $galeriaFinal = array_slice($galeriaExistente, 0, MAX_IMAGES_PER_NEWS);
    if ($pieFoto !== '' && empty($galeriaFinal[0]['pie'])) {
        $galeriaFinal[0]['pie'] = $pieFoto;
    }
    $imagenPortada = $galeriaFinal[0]['url'];

    $ahoraFecha = $fechaInput !== '' ? $fechaInput : date('d/m/Y H:i');
    $partesF = preg_split('/\s+/', $ahoraFecha, 2);
    $fechaSolo = $partesF[0] ?? date('d/m/Y');
    $horaSolo = $partesF[1] ?? date('H:i');

    $registro = [
        'id'           => $nuevoId,
        'estado'       => $estado,
        'categoria'    => $categoria ?: 'Gestión Industrial',
        'subtitulo'    => $subtitulo ?: $categoria,
        'titulo'       => $titulo,
        'resumen'      => $resumen,
        'cuerpo'       => $cuerpo,
        'imagen'       => $imagenPortada,
        'imagenes'     => $galeriaFinal,
        'pie_foto'     => $pieFoto ?: ($galeriaFinal[0]['pie'] ?? ''),
        'autor'        => $autor,
        'programacion' => $programacion,
        'etiquetas'    => $etiquetas,
        'fecha'        => $ahoraFecha,
        'fecha_solo'   => $fechaSolo,
        'hora_solo'    => $horaSolo,
        'url'          => $urlExt !== '' ? $urlExt : (REMOTE_PRESS_BASE . '/noticia/' . $nuevoId),
        'local_edit'   => true,
        'updated_at'   => date('Y-m-d H:i:s')
    ];

    $actualizado = false;
    foreach ($listaLocal as $k => $item) {
        if ((string)($item['id'] ?? '') === $nuevoId) {
            $listaLocal[$k] = $registro;
            $actualizado = true;
            break;
        }
    }
    if (!$actualizado) {
        array_unshift($listaLocal, $registro);
    }

    @file_put_contents($noticiasFile, json_encode($listaLocal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Si se republica una noticia que estaba oculta, quitarla de ocultas
    if ($estado === 'publicada' && is_file($ocultasFile)) {
        $oc = json_decode((string)file_get_contents($ocultasFile), true);
        if (is_array($oc) && in_array($nuevoId, $oc, true)) {
            $oc = array_values(array_diff($oc, [$nuevoId]));
            @file_put_contents($ocultasFile, json_encode($oc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    limpiarCachesNoticias();

    $accionHist = ($estado === 'borrador') ? 'Borrador guardado' : (($estado === 'archivada') ? 'Archivada' : ($actualizado ? 'Publicación actualizada' : 'Publicación inicial'));
    $historialId = registrarVersionHistorial(
        $historialFile,
        $nuevoId,
        $registro,
        (string)($_SESSION['alcasa_user']['usuario'] ?? 'editor'),
        $accionHist
    );

    $remotoOk = false;
    if ($estado === 'publicada' && !empty($_SESSION['alcasa_user']['remoto_5001'])) {
        $remotoOk = publicarRemoto5001($registro, $imagenAbsGuardada);
    }

    $registro['versiones_count'] = count($historialId);
    $registro['historial'] = $historialId;

    $msgEstado = $estado === 'borrador'
        ? 'Borrador guardado correctamente (no visible en el portal público hasta publicar).'
        : ($estado === 'archivada'
            ? 'Noticia movida al archivo.'
            : ($actualizado ? 'Noticia actualizada y publicada correctamente.' : 'Noticia publicada exitosamente en todo el portal.'));

    echo json_encode([
        'success'     => true,
        'mensaje'     => $msgEstado . ($remotoOk ? ' (Sincronizada también en :5001)' : ''),
        'noticia'     => $registro,
        'historial'   => $historialId,
        'remoto_sync' => $remotoOk
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'delete') {
    if (($_SESSION['alcasa_user']['rol'] ?? '') !== 'admin' && !in_array('prensa_eliminar', $permisos, true)) {
        fail('Solo el Super Administrador (Nivel 1) puede eliminar noticias permanentemente. Puedes cambiar su estado a Borrador o Archivada.', 403);
    }
    $in = input_data();
    $idDel = trim((string)($in['id'] ?? ''));
    if ($idDel === '') {
        fail('ID de noticia no especificado.');
    }

    $nuevas = [];
    foreach ($listaLocal as $item) {
        if ((string)($item['id'] ?? '') !== $idDel) {
            $nuevas[] = $item;
        }
    }
    @file_put_contents($noticiasFile, json_encode($nuevas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $ocultas = [];
    if (is_file($ocultasFile)) {
        $o = json_decode((string)file_get_contents($ocultasFile), true);
        if (is_array($o)) $ocultas = $o;
    }
    if (!in_array($idDel, $ocultas, true)) {
        $ocultas[] = $idDel;
        @file_put_contents($ocultasFile, json_encode($ocultas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    limpiarCachesNoticias();

    echo json_encode([
        'success' => true,
        'mensaje' => 'Noticia #' . $idDel . ' eliminada de la Sala de Prensa.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

fail('Acción no reconocida: ' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8'));
