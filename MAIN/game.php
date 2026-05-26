<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>
<?php require_once '../BBDD/game_queries.php'; ?>
<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<script src="../JS/checkIfXExists.js" defer></script>
<script src="../JS/carousel.js" defer></script>
<script src="../JS/game.js" defer></script>
<script src="../JS/game-alerts.js" defer></script>

<link rel="stylesheet" href="../CSS/carousel.css">
<link rel="stylesheet" href="../CSS/game.css">

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<?php
$gameId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mensaje = trim((string)($_GET['game_message'] ?? ''));
$tipoMensaje = in_array($_GET['game_message_type'] ?? '', ['success', 'danger', 'info', 'warning'], true)
    ? $_GET['game_message_type']
    : 'info';

$gameDataPhp = null;
$gameNamePhp = '';
$categories = [];
$comments = [];
$positiveCount = 0;
$negativeCount = 0;
$userBought = false;
$userReview = null;
$userNickname = $_SESSION['nickname'] ?? null;

$userLanguages = [
    'id_idioma_principal' => '',
    'id_idioma_secundario' => ''
];

if (!empty($userNickname)) {
    $userLanguages = getUserLanguages($BBDD, $userNickname);
}

if ($gameId > 0) {
    $gameDataPhp = getGameData($BBDD, $gameId);

    if ($gameDataPhp) {
        $gameNamePhp = $gameDataPhp['nombre_juego'];

        $categories = getGameCategories($BBDD, $gameId);
        $comments = getGameComments($BBDD, $gameNamePhp);
        $positiveCount = getPositiveRatingsCount($BBDD, $gameNamePhp);
        $negativeCount = getNegativeRatingsCount($BBDD, $gameNamePhp);

        if (!empty($userNickname)) {
            $userBought = hasUserBoughtGame($BBDD, $userNickname, $gameNamePhp);

            if ($userBought) {
                $userReview = getUserReview($BBDD, $userNickname, $gameNamePhp);
            }
        }
    }
}
function renderPrecioOriginal($precio, $descuento) {
    if ($descuento > 0) {
        return '<span class="price-original">' . number_format($precio, 2, ',', '.') . '€</span>';
    }
    return '';
}

function renderPrecioFinal($precio, $descuento) {
    $final = $precio - ($precio * ($descuento / 100));
    return '<span class="price-final">' . ($final <= 0 ? 'Gratis' : number_format($final, 2, ',', '.') . '€') . '</span>';
}
?>

<input type="hidden" id="hdnSession" data-value="<?php echo e($userNickname ?? ''); ?>" />
<input type="hidden" id="hdnGameId" data-value="<?php echo e((string)$gameId); ?>" />
<input type="hidden" id="hdnGameName" data-value="<?php echo e($gameNamePhp); ?>" />
<input type="hidden" id="hdnUserLangPrimary" data-value="<?php echo e($userLanguages['id_idioma_principal'] ?? ''); ?>" />
<input type="hidden" id="hdnUserLangSecondary" data-value="<?php echo e($userLanguages['id_idioma_secundario'] ?? ''); ?>" />

<div id="error-section"></div>

<div id="gameAlertContainer"
     data-alert-visible="<?php echo ($mensaje !== '') ? '1' : '0'; ?>"
     data-alert-type="<?php echo e($tipoMensaje); ?>"
     data-alert-message="<?php echo e($mensaje); ?>">
</div>

<?php if (!$gameDataPhp): ?>

    <section class="game-page">
        <div class="error-message">
            <span class="material-symbols-outlined">videogame_asset_off</span>
            <h2>Juego no encontrado</h2>
            <p>Lo sentimos, el juego que buscas no existe.</p>
            <a class="btn" href="./">Volver al inicio</a>
        </div>
    </section>

<?php else: ?>

<section class="game-page">
    <div class="game-main-content">

        <h1 id="big-game-title" class="game-title">
            <?php echo e($gameDataPhp['nombre_juego']); ?>
        </h1>

<section class="game-media-panel">
    <div id="carouselExampleIndicators" class="carousel slide game-carousel">
        <div id="carousel-container" class="carousel-inner">
            <?php
            $exists = false;
            $mediaItems = [];
            $gameId = (int)$gameDataPhp['id_juego'];
            $gameName = $gameDataPhp['nombre_juego'];

            try {
                $stmtMedia = $BBDD->prepare("
                    SELECT url_multimedia, tipo, numero_orden
                    FROM MultimediaJuego
                    WHERE id_juego = ? AND nombre_juego = ?
                    ORDER BY numero_orden ASC, id_multimedia ASC
                ");
                $stmtMedia->execute([$gameId, $gameName]);
                $mediaItems = $stmtMedia->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $mediaItems = [];
            }

            // Fallback: si no hay registros en la tabla, intenta leer del sistema de archivos
            if (empty($mediaItems)) {
                $allowedImageExt = '/\.(jpg|jpeg|png|webp|gif)$/i';
                $allowedVideoExt = '/\.(mp4|webm|ogg|avi|mov)$/i';

                $scanFolder = function (string $path, string $type, string $regex, string $prefix) use (&$mediaItems) {
                    if (!is_dir($path)) return;

                    $files = array_diff(scandir($path) ?: [], ['.', '..']);
                    foreach ($files as $file) {
                        $fullPath = $path . '/' . $file;
                        if (is_file($fullPath) && preg_match($regex, $file)) {
                            $mediaItems[] = [
                                'tipo' => $type,
                                'url_multimedia' => $prefix . rawurlencode($file),
                                'numero_orden' => 9999
                            ];
                        }
                    }
                };

                $videoPath = dirname(__DIR__) . '/MEDIA/VIDEO/juegos/' . $gameId;
                $scanFolder(
                    $videoPath,
                    'video',
                    $allowedVideoExt,
                    '../MEDIA/VIDEO/juegos/' . $gameId . '/'
                );

                $imagePathCarousel = dirname(__DIR__) . '/MEDIA/IMG/juegos/' . $gameId . '/carusel';
                $scanFolder(
                    $imagePathCarousel,
                    'imagen',
                    $allowedImageExt,
                    '../MEDIA/IMG/juegos/' . $gameId . '/carusel/'
                );

                $imagePathRoot = dirname(__DIR__) . '/MEDIA/IMG/juegos/' . $gameId;
                if (is_dir($imagePathRoot)) {
                    $files = array_diff(scandir($imagePathRoot) ?: [], ['.', '..']);
                    foreach ($files as $file) {
                        $fullPath = $imagePathRoot . '/' . $file;
                        if (is_file($fullPath) && preg_match($allowedImageExt, $file)) {
                            $mediaItems[] = [
                                'tipo' => 'imagen',
                                'url_multimedia' => '../MEDIA/IMG/juegos/' . $gameId . '/' . rawurlencode($file),
                                'numero_orden' => 9999
                            ];
                        }
                    }
                }

                usort($mediaItems, function ($a, $b) {
                    $oa = (int)($a['numero_orden'] ?? 9999);
                    $ob = (int)($b['numero_orden'] ?? 9999);
                    if ($oa === $ob) {
                        return strnatcasecmp((string)($a['url_multimedia'] ?? ''), (string)($b['url_multimedia'] ?? ''));
                    }
                    return $oa <=> $ob;
                });
            }

            foreach ($mediaItems as $index => $item) {
                $exists = true;
                $isActive = ($index === 0);
                $tipo = strtolower(trim((string)($item['tipo'] ?? 'imagen')));
                $url = trim((string)($item['url_multimedia'] ?? ''));

                if ($url === '') {
                    continue;
                }

                if ($tipo === 'video') {
                    $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? $url, PATHINFO_EXTENSION));
                    $mime = match ($ext) {
                        'webm' => 'video/webm',
                        'ogg'  => 'video/ogg',
                        default => 'video/mp4',
                    };
                    ?>
                    <div class="carousel-item <?php echo $isActive ? 'active' : ''; ?>">
                        <video class="video-carousel d-block w-100" controls playsinline preload="metadata">
                            <source src="<?php echo e($url); ?>" type="<?php echo e($mime); ?>">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="carousel-item <?php echo $isActive ? 'active' : ''; ?>">
                        <img src="<?php echo e($url); ?>"
                             class="d-block w-100 game-image"
                             alt="Game carousel image"
                             loading="lazy">
                    </div>
                    <?php
                }
            }

            if (!$exists) {
                ?>
                <div class="carousel-item active">
                    <img src="../MEDIA/IMG/juegos/fallback/default.jpg"
                         class="d-block w-100 game-image"
                         alt="Game placeholder">
                </div>
                <?php
            }
            ?>
        </div>

        <?php if ($exists && count($mediaItems) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        <?php endif; ?>
    </div>
</section>

        <section class="game-description-panel">
            <h2>Acerca del juego</h2>
            <p id="game-description"><?php echo e($gameDataPhp['descripcion'] ?? 'Descripción no disponible.'); ?></p>
        </section>

        <section class="game-comments-panel">
            <div class="section-title">
                <h2>Reseñas y comentarios</h2>
                <p class="reviews-stats">
                    <?php echo (int)$positiveCount; ?> positivas · <?php echo (int)$negativeCount; ?> negativas
                </p>
                <a href="comment.php?id=<?= (int)$gameId; ?>" class="chip chip-soft">Ver todos los comentarios</a>
            </div>
                    
            <div id="comment-section">
                <?php if ($userBought): ?>
                    <div class="user-comment-section">
                        <?php if (!$userReview): ?>
                            <div class="owned-make-comment review-form" id="comment-form-new">
                                <p>Escribe tu opinión sobre <?php echo e($gameDataPhp['nombre_juego']); ?> aquí:</p>

                                <textarea id="comment-input-new" placeholder="Escribe tu comentario aquí..."></textarea>

                                <div class="review-vote-group" id="review-vote-group-new">
                                    <input type="radio" name="review-rating-new" id="review-positive-new" value="positiva" checked>
                                    <label for="review-positive-new" class="review-vote-button review-vote-positive">
                                        <span class="material-symbols-outlined">thumb_up</span>
                                        Lo recomiendo
                                    </label>

                                    <input type="radio" name="review-rating-new" id="review-negative-new" value="negativa">
                                    <label for="review-negative-new" class="review-vote-button review-vote-negative">
                                        <span class="material-symbols-outlined">thumb_down</span>
                                        No lo recomiendo
                                    </label>
                                </div>

                                <button id="submit-comment-button" type="button">Enviar comentario</button>
                            </div>
                        <?php else: ?>
                            <div class="owned-edit-comment review-form" id="comment-form-edit">
                                <p>Tu opinión sobre <?php echo e($gameDataPhp['nombre_juego']); ?> está aquí:</p>

                                <textarea id="comment-input" placeholder="Escribe tu comentario aquí..." disabled><?php echo e($userReview['comentario']); ?></textarea>

                                <div class="review-vote-group is-disabled" id="review-vote-group-edit">
                                    <input
                                        type="radio"
                                        name="review-rating-edit"
                                        id="review-positive-edit"
                                        value="positiva"
                                        <?php echo (($userReview['valoracion'] ?? '') === 'positiva') ? 'checked' : ''; ?>
                                        disabled
                                    >
                                    <label for="review-positive-edit" class="review-vote-button review-vote-positive">
                                        <span class="material-symbols-outlined">thumb_up</span>
                                        Lo recomiendo
                                    </label>

                                    <input
                                        type="radio"
                                        name="review-rating-edit"
                                        id="review-negative-edit"
                                        value="negativa"
                                        <?php echo (($userReview['valoracion'] ?? '') === 'negativa') ? 'checked' : ''; ?>
                                        disabled
                                    >
                                    <label for="review-negative-edit" class="review-vote-button review-vote-negative">
                                        <span class="material-symbols-outlined">thumb_down</span>
                                        No lo recomiendo
                                    </label>
                                </div>

                                <button id="edit-comment-button" type="button">Editar</button>
                                <button id="save-comment-button" type="button" class="is-hidden" disabled>Guardar cambios</button>
                                <button id="delete-comment-button" type="button" class="is-hidden" disabled>Borrar comentario</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($userNickname)): ?>
                    <div class="reviews-toolbar">
                        <div class="reviews-toolbar-row d-flex flex-wrap gap-2 align-items-end">

                            <!-- Buscador -->
                            <div class="filter-item flex-grow-1" style="min-width: 250px;">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="material-symbols-outlined">search</span>
                                    </span>
                                    <input id="review-search" class="form-control" type="search" placeholder="Buscar en reseñas">
                                </div>
                            </div>

                            <!-- Filtro Valoración -->
                            <div class="filter-item">
                                <div class="dropdown">
                                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
                                            type="button"
                                            id="ratingDropdown"
                                            data-bs-toggle="dropdown">
                                        <span id="selected-rating-icon" class="material-symbols-outlined">star</span>
                                        <span id="selected-rating-text">Todas las valoraciones</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="ratingDropdown">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="all" data-icon="star">
                                                <span class="material-symbols-outlined">star</span>
                                                Todas las valoraciones
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="positiva" data-icon="thumb_up">
                                                <span class="material-symbols-outlined text-success">thumb_up</span>
                                                Positivas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="negativa" data-icon="thumb_down">
                                                <span class="material-symbols-outlined text-danger">thumb_down</span>
                                                Negativas
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Filtro Idioma -->
                            <div class="filter-item">
                                <div class="dropdown">
                                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
                                            type="button"
                                            id="languageDropdown"
                                            data-bs-toggle="dropdown">
                                        <span id="selected-language-icon" class="material-symbols-outlined">language</span>
                                        <span id="selected-language-text">Todos los idiomas</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="all" data-icon="language">
                                                <span class="material-symbols-outlined">language</span>
                                                Todos los idiomas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="my" data-icon="translate">
                                                <span class="material-symbols-outlined">translate</span>
                                                Tu idioma
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Ordenar -->
                            <div class="filter-item">
                                <div class="dropdown">
                                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
                                            type="button"
                                            id="sortDropdown"
                                            data-bs-toggle="dropdown">
                                        <span id="selected-sort-icon" class="material-symbols-outlined">sort</span>
                                        <span id="selected-sort-text">Más recientes</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="new" data-icon="schedule">
                                                <span class="material-symbols-outlined">schedule</span>
                                                Más recientes
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="old" data-icon="history">
                                                <span class="material-symbols-outlined">history</span>
                                                Más antiguas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="positive" data-icon="thumb_up">
                                                <span class="material-symbols-outlined text-success">thumb_up</span>
                                                Positivas primero
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="negative" data-icon="thumb_down">
                                                <span class="material-symbols-outlined text-danger">thumb_down</span>
                                                Negativas primero
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Rango de Fecha -->
                            <div class="filter-item date-range-wrapper">
                                <button id="review-date-toggle" class="btn btn-outline-light d-flex align-items-center gap-2" type="button">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                    Rango de fecha
                                </button>
                                <div id="date-dropdown" class="date-dropdown mt-2">
                                    <div class="d-flex gap-3">
                                        <div>
                                            <small class="text-muted">Desde:</small>
                                            <input id="review-date-from" type="date" class="form-control">
                                        </div>
                                        <div>
                                            <small class="text-muted">Hasta:</small>
                                            <input id="review-date-to" type="date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Limpiar Filtros -->
                            <div class="filter-item filter-reset">
                                <button id="review-reset" class="btn btn-outline-danger d-flex align-items-center gap-2" type="button">
                                    <span class="material-symbols-outlined">refresh</span>
                                    Limpiar
                                </button>
                            </div>

                        </div>
                    </div>
                <?php endif; ?>

                <div id="comment-list" class="comment-list">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <?php
                                $commentTimestamp = !empty($comment['fechaPublicacion']) ? strtotime($comment['fechaPublicacion']) : 0;
                                $commentLanguage = $comment['id_idioma_comentario'] ?? '';
                                $searchText = strtolower(trim(($comment['nombre_usuario'] ?? '') . ' ' . ($comment['comentario'] ?? '')));
                                $commentAvatarData = getProfileAvatarData($comment['nickname'] ?? $comment['nombre_usuario']);
                            ?>
                            <div
                                class="comment comment-card"
                                data-valoracion="<?php echo e($comment['valoracion']); ?>"
                                data-idioma="<?php echo e($commentLanguage); ?>"
                                data-timestamp="<?php echo e((string)$commentTimestamp); ?>"
                                data-search-text="<?php echo e($searchText); ?>"
                            >
                                <div class="comment-author-row d-flex align-items-start gap-2 mb-2">
                                    <div class="avatar-32px <?php echo e($commentAvatarData['avatarClass']); ?>"<?php if (!empty($commentAvatarData['avatarPath'])): ?> style="background-image: url('<?php echo e($commentAvatarData['avatarPath']); ?>');"<?php endif; ?>></div>
                                    <div class="comment-author-details">
                                        <p class="mb-1"><strong><?php echo e($comment['nombre_usuario']); ?></strong></p>
                                        <p class="mb-0 comment-meta">Valoración: <span class="rating-<?php echo e($comment['valoracion']); ?>"><?php echo e(ucfirst($comment['valoracion'])); ?></span></p>
                                    </div>
                                </div>
                                <div class="comment-body"><?php echo e($comment['comentario']); ?></div>
                                <p class="comment-date">Fecha: <?php echo e($comment['fechaPublicacion']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <?php endif; ?>
                </div>

                <p id="no-comment-results" class="no-comment-results" style="display:none;">No se han encontrado reseñas con esos filtros.</p>
            </div>
        </section>

    </div>

    <aside class="game-sidebar">

        <div class="game-purchase-card">
            <div class="purchase-header">
                <h3> <?php echo e($gameDataPhp['nombre_juego']); ?></h3>
            </div>
            <div id="purchase-section">
                <?php
                $price = (float)($gameDataPhp['precio'] ?? 0);
                $discount = (float)($gameDataPhp['descuento'] ?? 0);
                $finalPrice = $discount > 0 ? $price * (1 - $discount / 100) : $price;
                $hasDiscount = (float)$gameDataPhp['descuento'] > 0;
                $imgUrl = getGameImageUrl((int)$gameDataPhp['id_juego'], 'banner');
                ?>

                <hr class="separator">

                <div class="card-image" style="background-image: url('<?= e($imgUrl) ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>

                <?php if ($userBought): ?>
                    <div class="owned-message">¡Ya tienes este juego en tu biblioteca!</div>

                    <button class="download-button" type="button" onclick="window.location.href='./libraryGame.php?name=<?php echo urlencode($gameDataPhp['nombre_juego']); ?>'">
                        <span class="material-symbols-outlined">download</span>
                        <span>Descargar</span>
                    </button>
                <?php else: ?>
                    <div class="price-widget">
                        <?php if ($hasDiscount): ?>
                            <div class="price-discount-badge">-<?= (int)$gameDataPhp['descuento'] ?>%</div>
                            <div class="price-values">
                                <?= renderPrecioOriginal($gameDataPhp['precio'], $gameDataPhp['descuento']) ?>
                                <?= renderPrecioFinal($gameDataPhp['precio'], $gameDataPhp['descuento']) ?>
                            </div>
                        <?php else: ?>
                            <div class="price-values no-discount">
                                <span class="price-final">
                                    <?php
                                        $price = (float)$gameDataPhp['precio'];
                                        echo $price <= 0 ? 'Gratis' : number_format($price, 2, ',', '.') . '€';
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php
                    $inWishlist = false;
                    $inCart = false;
                    $fechaPublicacion = $gameDataPhp['fecha_publicacion'] ?? null;
                    $juegoDisponible = !empty($fechaPublicacion) && strtotime($fechaPublicacion) <= time();

                    if (!empty($userNickname)) {
                        try {
                            $stmtWishlist = $BBDD->prepare("
                                SELECT COUNT(*) AS in_wishlist
                                FROM ListaDeseos
                                WHERE nickname = ? AND nombre_juego = ?
                            ");
                            $stmtWishlist->execute([$userNickname, $gameNamePhp]);
                            $wishlistResult = $stmtWishlist->fetch(PDO::FETCH_ASSOC);
                            $inWishlist = !empty($wishlistResult) && ((int)($wishlistResult['in_wishlist'] ?? 0) > 0);
                        } catch (PDOException $e) {
                            $inWishlist = false;
                        }

                        try {
                            $stmtCart = $BBDD->prepare("
                                SELECT COUNT(*) AS in_cart
                                FROM Carrito
                                WHERE nickname = ? AND nombre_juego = ?
                            ");
                            $stmtCart->execute([$userNickname, $gameNamePhp]);
                            $cartResult = $stmtCart->fetch(PDO::FETCH_ASSOC);
                            $inCart = !empty($cartResult) && ((int)($cartResult['in_cart'] ?? 0) > 0);
                        } catch (PDOException $e) {
                            $inCart = false;
                        }
                    }
                    ?>

                    <?php if ($juegoDisponible): ?>
                        <button class="btn btn-primary" id="buy-button" type="button">
                            <span class="material-symbols-outlined">shopping_cart</span>
                            <span>Comprar</span>
                        </button>
                    <?php else: ?>
                        <div class="coming-soon-message">
                            <span class="material-symbols-outlined">schedule</span>
                            <span>Próximamente</span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($userNickname)): ?>
                        <?php if (!$inWishlist): ?>
                            <button class="wishlist-button" id="add-wishlist-button" type="button">
                                <span class="material-symbols-outlined">favorite</span>
                                <span>Añadir a la lista de deseos</span>
                            </button>
                        <?php else: ?>
                            <button class="wishlist-button" id="remove-wishlist-button" type="button">
                                <span class="material-symbols-outlined">heart_minus</span>
                                <span>Quitar de la lista de deseos</span>
                            </button>
                        <?php endif; ?>

                        <?php if ($juegoDisponible): ?>
                            <?php if (!$inCart): ?>
                                <button class="wishlist-button" id="add-cart-button" type="button">
                                    <span class="material-symbols-outlined">add_shopping_cart</span>
                                    <span>Añadir al carrito</span>
                                </button>
                            <?php else: ?>
                                <button class="wishlist-button" id="remove-cart-button" type="button">
                                    <span class="material-symbols-outlined">remove_shopping_cart</span>
                                    <span>Quitar del carrito</span>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="login-message">Inicia sesión para añadir a la lista de deseos</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="game-info-card">
            <h3>Información</h3>

            <div class="game-info-list">
                <div class="game-info-item">
                    <span class="label">Desarrollador</span>
                    <p id="game-developer"><?php echo e($gameDataPhp['desarrollador'] ?? 'Desarrollador desconocido.'); ?></p>
                </div>

                <div class="game-info-item">
                    <span class="label">Fecha lanzamiento</span>
                    <p id="game-release-date"><?php echo empty($gameDataPhp['fecha_publicacion']) ? 'Por confirmarse' : e($gameDataPhp['fecha_publicacion']); ?></p>
                </div>

                <div class="game-info-item">
                    <span class="label">Valoraciones</span>
                    <p id="game-rating"><?php echo (int)$positiveCount; ?> positivas</p>
                </div>
            </div>
        </div>

        <div class="game-category-card">
            <h3>Categorías</h3>
            <div id="game-categories" class="game-category-list">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <form action="../MAIN/shopSearch.php" method="post">
                            <input type="hidden" name="categorias" value="<?= e($cat['nombre_categoria']) ?>">
                            <button class="category-button" type="submit"><?php echo e($cat['nombre_categoria']); ?></button>
                        </form>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="game-category">Sin categorías</span>
                <?php endif; ?>
            </div>
        </div>

    </aside>
    
</section>

<?php endif; ?>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>