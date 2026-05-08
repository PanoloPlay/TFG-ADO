<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/wishlist_queries.php';

require_once '../GENERAL/auth_guard.php';

$usuario = ['id_usuario' => $_SESSION['id_usuario'], 'nickname' => $_SESSION['nickname']];
$avatarData = getProfileAvatarData($usuario['nickname']);
$initial = $avatarData['initial'];
$avatarPath = $avatarData['avatarPath'];
$avatarClass = $avatarData['avatarClass'];

$idUsuario = (int) $_SESSION['id_usuario'];
$nickname  = (string) $_SESSION['nickname'];
$orden = $_GET['orden'] ?? 'usuario';
$errorWishlist = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'reorder') {
            $wishlistIds = $_POST['order'] ?? [];
            wishlist_reorder($BBDD, $idUsuario, $nickname, is_array($wishlistIds) ? $wishlistIds : []);
            
            if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['ok' => true]);
                exit;
            }
            header('Location: wishlist.php?orden=' . urlencode($orden));
            exit;
        }

        $idWishlist = (int) ($_POST['id_wishlist'] ?? 0);
        if ($idWishlist > 0 && $action === 'delete') {
            wishlist_delete_item($BBDD, $idUsuario, $nickname, $idWishlist);
            header('Location: wishlist.php?orden=' . urlencode($orden));
            exit;
        }
    }
} catch (Throwable $e) {
    $errorWishlist = $e->getMessage();
}

$wishlist = wishlist_get_items($BBDD, $idUsuario, $nickname, $orden);

// Formateadores auxiliares
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

<?php include '../GENERAL/[html_START - head_START].php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.7/Sortable.min.js"></script>
<link rel="stylesheet" href="../CSS/wishlist.css">

<?php include '../GENERAL/[head_END - body_START - header - main_START].php'; ?>
<div class="wishlist-container">
    <div class="wishlist-hero">
        <div class="wishlist-hero-content">
            <div class="wishlist-profile">
                <div class="profile-badge">
                    <div
                        class="profile-avatar <?= e($avatarClass) ?>"
                        <?php if ($avatarPath): ?>
                            style="background-image: url('<?= e($avatarPath) ?>'); background-size: cover; background-position: center;"
                        <?php endif; ?>
                    >
                        <?php if (!$avatarPath): ?>
                            <?= e($initial) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="profile-info">
                    <h1>Lista de deseos de <?= e($nickname) ?></h1>
                    <div class="profile-badges">
                        <span class="badge badge-dark"><?= count($wishlist) ?> artículos</span>
                        <span class="badge badge-primary">Orden personalizado</span>
                    </div>
                </div>
            </div>
            <form method="get" class="wishlist-sort-form">
                <label for="orden">Ordenar por:</label>
                <select name="orden" id="orden" class="wishlist-select" onchange="this.form.submit()">
                    <option value="usuario" <?= $orden === 'usuario' ? 'selected' : '' ?>>Tu clasificación</option>
                    <option value="nombre" <?= $orden === 'nombre' ? 'selected' : '' ?>>Nombre</option>
                    <option value="precio" <?= $orden === 'precio' ? 'selected' : '' ?>>Precio</option>
                    <option value="descuento" <?= $orden === 'descuento' ? 'selected' : '' ?>>Descuento</option>
                    <option value="fecha" <?= $orden === 'fecha' ? 'selected' : '' ?>>Lanzamiento</option>
                </select>
            </form>
        </div>
    </div>
    <form class="wishlist-search-form" onsubmit="return false;">
        <label for="wishlist-search">Buscar juego:</label>
        <input
            type="search"
            id="wishlist-search"
            class="wishlist-search"
            placeholder="Escribe el nombre del juego..."
            autocomplete="off"
        >
    </form>
    <div id="wishlist-empty-search" class="wishlist-alert alert-info" hidden>
        No se han encontrado juegos con ese nombre.
    </div>

    <div id="wishlist-status" class="wishlist-alert hidden"></div>

    <?php if (empty($wishlist)): ?>
        <div class="wishlist-alert alert-info">
            Tu lista de deseos está actualmente vacía. ¡Explora la tienda para añadir juegos!
        </div>
    <?php else: ?>
        <div id="wishlist-list" class="wishlist-list" data-save-url="wishlist.php?orden=<?= e($orden) ?>">
            <?php $posicion = 1; ?>
            <?php foreach ($wishlist as $juego): ?>
                <?php 
                    $hasDiscount = (float)$juego['descuento'] > 0;
                    $imgUrl = getGameImageUrl($juego['id_juego'], 'wide-cover');
                ?>
                    <div class="wishlist-item" data-wishlist-id="<?= (int)$juego['id_Wishlist'] ?>"
                        data-search="<?= e($juego['nombre_juego'] . ' ' . ($juego['desarrollador'] ?? '')) ?>">
                        <!-- Handle de arrastre (Solo visible si el orden es 'usuario') -->
                        <?php if ($orden === 'usuario'): ?>
                        <div class="wishlist-handle">
                            <span class="material-symbols-outlined drag-icon">reorder</span>
                            <input type="number" class="wishlist-rank-input" value="<?= $posicion ?>" data-original-value="<?= $posicion ?>">
                        </div>
                        <?php endif; ?>
                        <a href="../MAIN/game.php?id=<?= (int)$juego['id_juego'] ?>" class="wishlist-link" aria-label="Ver <?= e($juego['nombre_juego']) ?>">
                        <!-- Miniatura del Juego -->
                        <div class="wishlist-capsule" alt="Banner"
                            style="background-image: url('<?= e($imgUrl)?>'); background-size: cover; background-position: center;">
                        </div>

                        <!-- Información Principal -->
                        <div class="wishlist-info">
                            <h2><?= e($juego['nombre_juego']) ?></h2>
                            <div class="wishlist-meta">
                                <span>Lanzamiento: <?= date('d M Y', strtotime($juego['fecha_publicacion'])) ?></span>
                                <span class="developer"><?= e($juego['desarrollador'] ?? 'Desconocido') ?></span>
                            </div>
                            <div class="wishlist-reviews">
                                <?= renderReviewBadge($juego) ?>
                                <span class="review-count">(<?= (int)$juego['total_resenas'] ?>)</span>
                            </div>
                        </div>

                        <!-- Bloque de Precio y Acciones -->
                        <div class="wishlist-actions">
                            <div class="price-widget">
                                <?php if ($hasDiscount): ?>
                                    <div class="price-discount-badge">-<?= (int)$juego['descuento'] ?>%</div>
                                    <div class="price-values">
                                        <?= renderPrecioOriginal($juego['precio'], $juego['descuento']) ?>
                                        <?= renderPrecioFinal($juego['precio'], $juego['descuento']) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="price-values no-discount">
                                        <span class="price-final"><?= number_format($juego['precio'], 2) ?>€</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Botón Eliminar -->
                            <form method="post" onsubmit="return confirm('¿Quitar de la lista?');" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_wishlist" value="<?= (int)$juego['id_Wishlist'] ?>">
                                <button type="submit" class="btn-delete" aria-label="Eliminar de la lista">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                        </a>
                    </div>
                <?php $posicion++; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="../JS/wishlist.js" defer></script>

<?php include '../GENERAL/[main_END - footer].php'; ?>
<?php include '../GENERAL/[Page_END].php'; ?>