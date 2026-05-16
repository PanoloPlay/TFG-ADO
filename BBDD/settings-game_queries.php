<?php
$nickname = (string) $_SESSION['nickname'];
$fallbackImageUrl = '../MEDIA/IMG/juegos/fallback/default.jpg';

function getCurrentDeveloper(PDO $BBDD, string $nickname): ?array
{
    $sql = "SELECT id_desarrollador, nombre_desarrollador, nickname
            FROM Desarrollador
            WHERE nickname = :nickname
            LIMIT 1";
    $stmt = $BBDD->prepare($sql);
    $stmt->execute([':nickname' => $nickname]);
    $dev = $stmt->fetch(PDO::FETCH_ASSOC);

    return $dev ?: null;
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $items = array_diff(scandir($dir) ?: [], ['.', '..']);
    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

function deleteGameMedia(int $idJuego): void
{
    rrmdir(dirname(__DIR__) . '/MEDIA/IMG/juegos/' . $idJuego);
    rrmdir(dirname(__DIR__) . '/MEDIA/VIDEO/juegos/' . $idJuego);
}

function sanitizeMoney($value): ?string
{
    if ($value === '' || $value === null) {
        return null;
    }
    $value = str_replace(',', '.', (string) $value);
    if (!is_numeric($value)) {
        return null;
    }
    return number_format((float) $value, 2, '.', '');
}

function ensureDir(string $dir): void
{
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('No se ha podido crear el directorio: ' . $dir);
    }
}

function deleteExistingVariantFiles(string $dir, string $baseName): void
{
    foreach (glob($dir . '/' . $baseName . '.*') as $oldFile) {
        if (is_file($oldFile)) {
            @unlink($oldFile);
        }
    }
}

function getStaticGameImageUrl(int $idJuego, string $variant): string
{
    $variant = strtolower(trim($variant));

    $map = [
        'logo' => ['logo.png'],
        'header' => ['header.png', 'header.jpg', 'header.jpeg', 'header.webp'],
        'capsule' => ['capsule.png', 'capsule.jpg', 'capsule.jpeg', 'capsule.webp'],
        'background' => ['background.jpg', 'background.jpeg', 'background.webp', 'background.png'],
        'wide-cover' => ['wide-cover.jpg', 'wide-cover.jpeg', 'wide-cover.webp', 'wide-cover.png'],
        'banner' => ['banner.jpg', 'banner.jpeg', 'banner.webp', 'banner.png'],
        'cover' => ['cover.jpg', 'cover.jpeg', 'cover.webp', 'cover.png'],
        'icon' => ['icon.png', 'icon.jpg', 'icon.jpeg', 'icon.ico', 'icon.webp']
    ];

    if (!isset($map[$variant])) {
        return '../MEDIA/IMG/juegos/fallback/default.jpg';
    }

    if ($variant === 'icon' && function_exists('getGameImageUrl')) {
        return getGameImageUrl($idJuego, 'icon');
    }

    $baseFs = dirname(__DIR__) . '/MEDIA/IMG/juegos/' . $idJuego . '/icons/';
    foreach ($map[$variant] as $file) {
        if (is_file($baseFs . $file)) {
            return '../MEDIA/IMG/juegos/' . $idJuego . '/icons/' . $file;
        }
    }

    $fallbackFs = dirname(__DIR__) . '/MEDIA/IMG/juegos/fallback/';
    foreach ([$variant . '.jpg', $variant . '.jpeg', $variant . '.png', $variant . '.webp', $variant . '.ico'] as $file) {
        if (is_file($fallbackFs . $file)) {
            return '../MEDIA/IMG/juegos/fallback/' . $file;
        }
    }

    return '../MEDIA/IMG/juegos/fallback/default.jpg';
}

function getGameVideoUrl(int $idJuego, string $fileName): string
{
    $fileName = trim($fileName);
    if ($fileName === '') {
        return '../MEDIA/VIDEO/fallback/default.mp4';
    }

    $candidate = dirname(__DIR__) . '/MEDIA/VIDEO/juegos/' . $idJuego . '/' . $fileName;
    if (is_file($candidate)) {
        return '../MEDIA/VIDEO/juegos/' . $idJuego . '/' . rawurlencode($fileName);
    }

    $fallbackFs = dirname(__DIR__) . '/MEDIA/VIDEO/fallback/';
    foreach (['default.mp4', 'default.webm', 'default.ogg'] as $fallback) {
        if (is_file($fallbackFs . $fallback)) {
            return '../MEDIA/VIDEO/fallback/' . $fallback;
        }
    }

    return '../MEDIA/VIDEO/fallback/default.mp4';
}

function detectUploadedMime(array $file): string
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return (string) $finfo->file($file['tmp_name']);
}

function saveStaticGameImage(array $file, int $idJuego, string $variant): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al subir la imagen: ' . $variant);
    }
    if (($file['size'] ?? 0) > 8 * 1024 * 1024) {
        throw new RuntimeException('La imagen ' . $variant . ' no puede superar 8 MB.');
    }

    $mime = detectUploadedMime($file);
    $extensionesPermitidas = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
    ];

    if (!isset($extensionesPermitidas[$mime])) {
        throw new RuntimeException('Formato no permitido para ' . $variant . '. Usa PNG, JPG/JPEG, WEBP o ICO.');
    }

    $dir = dirname(__DIR__) . '/MEDIA/IMG/juegos/' . $idJuego . '/icons/';
    ensureDir($dir);
    deleteExistingVariantFiles($dir, $variant);

    $ext = $extensionesPermitidas[$mime];
    $final = $dir . $variant . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $final)) {
        throw new RuntimeException('No se ha podido guardar la imagen ' . $variant . '.');
    }

    return '../MEDIA/IMG/juegos/' . $idJuego . '/icons/' . $variant . '.' . $ext;
}

function saveCarouselMedia(PDO $BBDD, array $file, int $idJuego, string $nombreJuego, int $orden, ?string $forceType = null): ?array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al subir un archivo del carrusel.');
    }

    $mime = detectUploadedMime($file);
    $imageExts = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
    $videoExts = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/ogg' => 'ogg'];

    $tipo = $forceType;
    $ext = null;

    if ($tipo === null) {
        if (isset($imageExts[$mime])) {
            $tipo = 'imagen';
            $ext = $imageExts[$mime];
        } elseif (isset($videoExts[$mime])) {
            $tipo = 'video';
            $ext = $videoExts[$mime];
        } else {
            throw new RuntimeException('Formato no permitido en el carrusel.');
        }
    } elseif ($tipo === 'imagen') {
        if (!isset($imageExts[$mime])) {
            throw new RuntimeException('El archivo del carrusel debe ser una imagen.');
        }
        $ext = $imageExts[$mime];
    } elseif ($tipo === 'video') {
        if (!isset($videoExts[$mime])) {
            throw new RuntimeException('El archivo del carrusel debe ser un vídeo.');
        }
        $ext = $videoExts[$mime];
    } else {
        throw new RuntimeException('Tipo de multimedia no válido.');
    }

    $subdir = ($tipo === 'video')
        ? dirname(__DIR__) . '/MEDIA/VIDEO/juegos/' . $idJuego . '/'
        : dirname(__DIR__) . '/MEDIA/IMG/juegos/' . $idJuego . '/carusel/';

    ensureDir($subdir);

    $safeBase = 'media_' . $idJuego . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destinoFisico = $subdir . $safeBase;

    if (!move_uploaded_file($file['tmp_name'], $destinoFisico)) {
        throw new RuntimeException('No se ha podido guardar el archivo del carrusel.');
    }

    $urlRelativa = ($tipo === 'video')
        ? '../MEDIA/VIDEO/juegos/' . $idJuego . '/' . $safeBase
        : '../MEDIA/IMG/juegos/' . $idJuego . '/carusel/' . $safeBase;

    $sql = "INSERT INTO MultimediaJuego (id_juego, nombre_juego, url_multimedia, tipo, numero_orden)
            VALUES (:id_juego, :nombre_juego, :url_multimedia, :tipo, :numero_orden)";
    $stmt = $BBDD->prepare($sql);
    $stmt->execute([
        ':id_juego' => $idJuego,
        ':nombre_juego' => $nombreJuego,
        ':url_multimedia' => $urlRelativa,
        ':tipo' => $tipo,
        ':numero_orden' => $orden,
    ]);

    return [
        'tipo' => $tipo,
        'url' => $urlRelativa,
        'orden' => $orden,
    ];
}

$developer = getCurrentDeveloper($BBDD, $nickname);
if (!$developer) {
    header('Location: developer-reguister.php');
    exit;
}

$nombreDesarrollador = $developer['nombre_desarrollador'];
$mensaje = '';
$error = '';
$modo = $_GET['modo'] ?? 'listar';
$editId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$categorias = [];
$idiomasJuego = [];
$categoriasSeleccionadas = [];
$idiomasSeleccionados = [];
$logrosJuego = [];

try {
    $stmtCategorias = $BBDD->query("
        SELECT id_categoria, categoria
        FROM Categorias
        ORDER BY categoria ASC
    ");
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

    $stmtIdiomas = $BBDD->query("
        SELECT id_idioma, idioma
        FROM Idiomas
        ORDER BY idioma ASC
    ");
    $idiomasJuego = $stmtIdiomas->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $categorias = [];
    $idiomasJuego = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'guardar_juego') {
            $idJuego = isset($_POST['id_juego']) && $_POST['id_juego'] !== '' ? (int) $_POST['id_juego'] : null;
            $nombreJuego = trim((string) ($_POST['nombre_juego'] ?? ''));
            $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
            $fechaPublicacion = trim((string) ($_POST['fecha_publicacion'] ?? ''));
            $precio = sanitizeMoney($_POST['precio'] ?? null);
            $descuento = sanitizeMoney($_POST['descuento'] ?? null);
            $categoriasPost = $_POST['categorias'] ?? [];
            $idiomasPost = $_POST['idiomas'] ?? [];

            if ($nombreJuego === '' || $fechaPublicacion === '') {
                throw new RuntimeException('El nombre del juego y la fecha de publicación son obligatorios.');
            }

            $BBDD->beginTransaction();

            if ($idJuego) {
                $sql = "UPDATE Juegos
                        SET nombre_juego = :nombre_juego,
                            descripcion = :descripcion,
                            fecha_publicacion = :fecha_publicacion,
                            desarrollador = :desarrollador,
                            precio = :precio,
                            descuento = :descuento
                        WHERE id_juego = :id_juego
                          AND desarrollador = :desarrollador";
                $stmt = $BBDD->prepare($sql);
                $stmt->execute([
                    ':nombre_juego' => $nombreJuego,
                    ':descripcion' => $descripcion !== '' ? $descripcion : null,
                    ':fecha_publicacion' => $fechaPublicacion,
                    ':desarrollador' => $nombreDesarrollador,
                    ':precio' => $precio,
                    ':descuento' => $descuento,
                    ':id_juego' => $idJuego,
                ]);
            } else {
                $sql = "INSERT INTO Juegos (nombre_juego, descripcion, fecha_publicacion, desarrollador, precio, descuento)
                        VALUES (:nombre_juego, :descripcion, :fecha_publicacion, :desarrollador, :precio, :descuento)";
                $stmt = $BBDD->prepare($sql);
                $stmt->execute([
                    ':nombre_juego' => $nombreJuego,
                    ':descripcion' => $descripcion !== '' ? $descripcion : null,
                    ':fecha_publicacion' => $fechaPublicacion,
                    ':desarrollador' => $nombreDesarrollador,
                    ':precio' => $precio,
                    ':descuento' => $descuento,
                ]);
                $idJuego = (int) $BBDD->lastInsertId();
            }

            if (!$idJuego) {
                throw new RuntimeException('No se ha podido obtener el ID del juego.');
            }

            $delCategorias = $BBDD->prepare("
                DELETE FROM CategoriasJuego
                WHERE id_juego = :id_juego
                  AND nombre_juego = :nombre_juego
            ");
            $delCategorias->execute([
                ':id_juego' => $idJuego,
                ':nombre_juego' => $nombreJuego,
            ]);

            if (!empty($categoriasPost)) {
                $insCategoria = $BBDD->prepare("
                    INSERT INTO CategoriasJuego (id_categoria, categoria, id_juego, nombre_juego)
                    SELECT c.id_categoria, c.categoria, :id_juego, :nombre_juego
                    FROM Categorias c
                    WHERE c.id_categoria = :id_categoria
                    LIMIT 1
                ");

                foreach ($categoriasPost as $idCategoria) {
                    $insCategoria->execute([
                        ':id_juego' => $idJuego,
                        ':nombre_juego' => $nombreJuego,
                        ':id_categoria' => (int) $idCategoria,
                    ]);
                }
            }

            $delIdiomas = $BBDD->prepare("
                DELETE FROM IdiomasJuego
                WHERE id_juego = :id_juego
                  AND nombre_juego = :nombre_juego
            ");
            $delIdiomas->execute([
                ':id_juego' => $idJuego,
                ':nombre_juego' => $nombreJuego,
            ]);

            if (!empty($idiomasPost)) {
                $insIdioma = $BBDD->prepare("
                    INSERT INTO IdiomasJuego (id_juego, nombre_juego, id_idioma)
                    VALUES (:id_juego, :nombre_juego, :id_idioma)
                ");

                foreach ($idiomasPost as $idIdioma) {
                    $insIdioma->execute([
                        ':id_juego' => $idJuego,
                        ':nombre_juego' => $nombreJuego,
                        ':id_idioma' => (string) $idIdioma,
                    ]);
                }
            }

            $staticVariants = ['logo', 'header', 'capsule', 'background', 'wide-cover', 'banner', 'cover', 'icon'];
            foreach ($staticVariants as $variant) {
                $key = 'img_' . $variant;
                if (!empty($_FILES[$key]) && (int) ($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    saveStaticGameImage($_FILES[$key], $idJuego, $variant);
                }
            }

            $carouselFiles = $_FILES['carousel_files'] ?? null;
            $carouselOrders = $_POST['carousel_orders'] ?? [];
            $carouselTypes = $_POST['carousel_types'] ?? [];

            if (is_array($carouselFiles) && isset($carouselFiles['name']) && is_array($carouselFiles['name'])) {
                $count = count($carouselFiles['name']);
                for ($i = 0; $i < $count; $i++) {
                    if (($carouselFiles['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $file = [
                        'name' => $carouselFiles['name'][$i],
                        'type' => $carouselFiles['type'][$i],
                        'tmp_name' => $carouselFiles['tmp_name'][$i],
                        'error' => $carouselFiles['error'][$i],
                        'size' => $carouselFiles['size'][$i],
                    ];

                    $orden = isset($carouselOrders[$i]) && $carouselOrders[$i] !== '' ? (int) $carouselOrders[$i] : ($i + 1);
                    $forceType = $carouselTypes[$i] ?? null;
                    if ($forceType === '') {
                        $forceType = null;
                    }
                    saveCarouselMedia($BBDD, $file, $idJuego, $nombreJuego, $orden, $forceType);
                }
            }

            $BBDD->commit();
            $mensaje = $idJuego ? 'Juego actualizado correctamente.' : 'Juego creado correctamente.';
            $modo = 'listar';
        }

        if ($accion === 'guardar_logros') {
            $idJuego = (int) ($_POST['id_juego'] ?? 0);
            $achievementIds = $_POST['achievement_ids'] ?? [];
            $achievementNames = $_POST['achievement_names'] ?? [];
            $achievementDescriptions = $_POST['achievement_descriptions'] ?? [];
            $achievementRarities = $_POST['achievement_rarities'] ?? [];
            $achievementOrders = $_POST['achievement_orders'] ?? [];

            if ($idJuego <= 0) {
                throw new RuntimeException('ID de juego inválido.');
            }

            $checkGame = $BBDD->prepare("SELECT id_juego, nombre_juego FROM Juegos WHERE id_juego = :id_juego AND desarrollador = :desarrollador LIMIT 1");
            $checkGame->execute([
                ':id_juego' => $idJuego,
                ':desarrollador' => $nombreDesarrollador,
            ]);
            $juegoDb = $checkGame->fetch(PDO::FETCH_ASSOC);

            if (!$juegoDb) {
                throw new RuntimeException('No puedes editar logros de un juego que no es tuyo.');
            }

            $nombreJuegoActual = (string) $juegoDb['nombre_juego'];
            $BBDD->beginTransaction();

            $idsMantener = [];
            $total = max(count($achievementNames), count($achievementDescriptions), count($achievementRarities), count($achievementOrders), count($achievementIds));

            for ($i = 0; $i < $total; $i++) {
                $idLogro = isset($achievementIds[$i]) ? (int) $achievementIds[$i] : 0;
                $nombreLogro = trim((string) ($achievementNames[$i] ?? ''));
                $descripcionLogro = trim((string) ($achievementDescriptions[$i] ?? ''));
                $rareza = trim((string) ($achievementRarities[$i] ?? ''));
                $orden = isset($achievementOrders[$i]) && $achievementOrders[$i] !== '' ? (int) $achievementOrders[$i] : ($i + 1);

                if ($nombreLogro === '' && $descripcionLogro === '' && $rareza === '' && $idLogro === 0) {
                    continue;
                }

                if ($nombreLogro === '') {
                    throw new RuntimeException('Todos los logros deben tener nombre.');
                }

                if (!in_array($rareza, ['cobre', 'plata', 'oro', 'platino', 'lotus'], true)) {
                    throw new RuntimeException('Rareza no válida en uno de los logros.');
                }

                if ($idLogro > 0) {
                    $stmtUpdate = $BBDD->prepare("UPDATE Logros SET nombre_logro = :nombre_logro, descripcion_logro = :descripcion_logro, rareza = :rareza, identificador_unico = CONCAT(id_logro, '_', :id_juego, '_', :rareza) WHERE id_logro = :id_logro AND id_juego = :id_juego");
                    $stmtUpdate->execute([
                        ':nombre_logro' => $nombreLogro,
                        ':descripcion_logro' => $descripcionLogro !== '' ? $descripcionLogro : null,
                        ':rareza' => $rareza,
                        ':id_juego' => $idJuego,
                        ':id_logro' => $idLogro,
                    ]);
                    $idsMantener[] = $idLogro;
                } else {
                    $stmtInsert = $BBDD->prepare("INSERT INTO Logros (nombre_logro, descripcion_logro, id_juego, nombre_juego, rareza) VALUES (:nombre_logro, :descripcion_logro, :id_juego, :nombre_juego, :rareza)");
                    $stmtInsert->execute([
                        ':nombre_logro' => $nombreLogro,
                        ':descripcion_logro' => $descripcionLogro !== '' ? $descripcionLogro : null,
                        ':id_juego' => $idJuego,
                        ':nombre_juego' => $nombreJuegoActual,
                        ':rareza' => $rareza,
                    ]);
                    $newId = (int) $BBDD->lastInsertId();
                    $stmtUpdateUid = $BBDD->prepare("UPDATE Logros SET identificador_unico = CONCAT(id_logro, '_', :id_juego, '_', :rareza) WHERE id_logro = :id_logro AND id_juego = :id_juego");
                    $stmtUpdateUid->execute([
                        ':id_juego' => $idJuego,
                        ':rareza' => $rareza,
                        ':id_logro' => $newId,
                    ]);
                    $idsMantener[] = $newId;
                }
            }

            if (!empty($idsMantener)) {
                $placeholders = implode(',', array_fill(0, count($idsMantener), '?'));
                $sqlDelete = "DELETE FROM Logros WHERE id_juego = ? AND nombre_juego = ? AND id_logro NOT IN ($placeholders)";
                $stmtDelete = $BBDD->prepare($sqlDelete);
                $params = array_merge([$idJuego, $nombreJuegoActual], $idsMantener);
                $stmtDelete->execute($params);
            } else {
                $stmtDelete = $BBDD->prepare("DELETE FROM Logros WHERE id_juego = :id_juego AND nombre_juego = :nombre_juego");
                $stmtDelete->execute([
                    ':id_juego' => $idJuego,
                    ':nombre_juego' => $nombreJuegoActual,
                ]);
            }

            $BBDD->commit();
            $mensaje = 'Logros guardados correctamente.';
            $modo = 'logros';
            $editId = $idJuego;
        }

        if ($accion === 'eliminar_juego') {
            $idJuego = (int) ($_POST['id_juego'] ?? 0);
            if ($idJuego <= 0) {
                throw new RuntimeException('ID de juego inválido.');
            }

            $BBDD->beginTransaction();

            $check = $BBDD->prepare("SELECT id_juego FROM Juegos WHERE id_juego = :id_juego AND desarrollador = :desarrollador");
            $check->execute([':id_juego' => $idJuego, ':desarrollador' => $nombreDesarrollador]);
            if (!$check->fetchColumn()) {
                throw new RuntimeException('No puedes borrar un juego que no es tuyo.');
            }

            $del = $BBDD->prepare("DELETE FROM Juegos WHERE id_juego = :id_juego AND desarrollador = :desarrollador");
            $del->execute([':id_juego' => $idJuego, ':desarrollador' => $nombreDesarrollador]);

            $BBDD->commit();
            deleteGameMedia($idJuego);
            $mensaje = 'Juego y archivos multimedia eliminados correctamente.';
            $modo = 'listar';
        }
    } catch (Throwable $e) {
        if ($BBDD->inTransaction()) {
            $BBDD->rollBack();
        }
        $error = $e->getMessage();
    }
}

$juegoEdit = null;
if (in_array($modo, ['editar', 'logros'], true) && $editId > 0) {
    $stmt = $BBDD->prepare("SELECT * FROM Juegos WHERE id_juego = :id_juego AND desarrollador = :desarrollador LIMIT 1");
    $stmt->execute([':id_juego' => $editId, ':desarrollador' => $nombreDesarrollador]);
    $juegoEdit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    if (!$juegoEdit) {
        $error = 'No se encontró el juego a editar.';
        $modo = 'listar';
    }
}

$stmt = $BBDD->prepare("SELECT id_juego, nombre_juego, descripcion, fecha_publicacion, precio, descuento
                       FROM Juegos
                       WHERE desarrollador = :desarrollador
                       ORDER BY fecha_publicacion DESC, id_juego DESC");
$stmt->execute([':desarrollador' => $nombreDesarrollador]);
$juegos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($modo === 'logros' && $juegoEdit) {
    $stmt = $BBDD->prepare("SELECT id_logro, nombre_logro, descripcion_logro, rareza, identificador_unico
                           FROM Logros
                           WHERE id_juego = :id_juego
                             AND nombre_juego = :nombre_juego
                           ORDER BY id_logro ASC");
    $stmt->execute([
        ':id_juego' => (int) $juegoEdit['id_juego'],
        ':nombre_juego' => (string) $juegoEdit['nombre_juego'],
    ]);
    $logrosJuego = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$multimediaEdit = [];
if ($juegoEdit) {
    $stmt = $BBDD->prepare("SELECT id_multimedia, id_juego, nombre_juego, url_multimedia, tipo, numero_orden
                           FROM MultimediaJuego
                           WHERE id_juego = :id_juego AND nombre_juego = :nombre_juego
                           ORDER BY numero_orden ASC, id_multimedia ASC");
    $stmt->execute([
        ':id_juego' => (int) $juegoEdit['id_juego'],
        ':nombre_juego' => (string) $juegoEdit['nombre_juego'],
    ]);
    $multimediaEdit = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtCategoriasSeleccionadas = $BBDD->prepare("
        SELECT id_categoria
        FROM CategoriasJuego
        WHERE id_juego = :id_juego
          AND nombre_juego = :nombre_juego
    ");
    $stmtCategoriasSeleccionadas->execute([
        ':id_juego' => (int) $juegoEdit['id_juego'],
        ':nombre_juego' => (string) $juegoEdit['nombre_juego'],
    ]);
    $categoriasSeleccionadas = array_map('intval', $stmtCategoriasSeleccionadas->fetchAll(PDO::FETCH_COLUMN));

    $stmtIdiomasSeleccionados = $BBDD->prepare("
        SELECT id_idioma
        FROM IdiomasJuego
        WHERE id_juego = :id_juego
          AND nombre_juego = :nombre_juego
    ");
    $stmtIdiomasSeleccionados->execute([
        ':id_juego' => (int) $juegoEdit['id_juego'],
        ':nombre_juego' => (string) $juegoEdit['nombre_juego'],
    ]);
    $idiomasSeleccionados = array_map('strval', $stmtIdiomasSeleccionados->fetchAll(PDO::FETCH_COLUMN));
}

$staticFields = [
    'logo' => ['label' => 'Logo', 'fit' => 'contain', 'crop' => false, 'ratio' => '16 / 9'],
    'header' => ['label' => 'Header', 'fit' => 'cover', 'crop' => true, 'ratio' => '92 / 43', 'width' => 920, 'height' => 430, 'type' => 'image/jpeg'],
    'capsule' => ['label' => 'Capsule', 'fit' => 'cover', 'crop' => true, 'ratio' => '77 / 29', 'width' => 770, 'height' => 290, 'type' => 'image/jpeg'],
    'background' => ['label' => 'Background', 'fit' => 'cover', 'crop' => true, 'ratio' => '3840 / 1240', 'width' => 3840, 'height' => 1240, 'type' => 'image/jpeg'],
    'wide-cover' => ['label' => 'Wide cover', 'fit' => 'cover', 'crop' => true, 'ratio' => '92 / 43', 'width' => 920, 'height' => 430, 'type' => 'image/jpeg'],
    'banner' => ['label' => 'Banner', 'fit' => 'cover', 'crop' => true, 'ratio' => '92 / 43', 'width' => 920, 'height' => 430, 'type' => 'image/jpeg'],
    'cover' => ['label' => 'Cover', 'fit' => 'cover', 'crop' => true, 'ratio' => '6 / 9', 'width' => 600, 'height' => 900, 'type' => 'image/jpeg'],
    'icon' => ['label' => 'Icono', 'fit' => 'cover', 'crop' => true, 'ratio' => '1 / 1', 'width' => 64, 'height' => 64, 'type' => 'image/png'],
];