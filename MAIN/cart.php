<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/cart_queries.php';

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

        if (isset($_POST['buyAll'])) {
            cartBuyAll($BBDD, $idUsuario, $nickname);
            header('Location: cart.php?orden=' . urlencode($orden));
            exit;
        }
        
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'reorder') {
            $wishlistIds = $_POST['order'] ?? [];
            wishlist_reorder($BBDD, $idUsuario, $nickname, is_array($wishlistIds) ? $wishlistIds : []);
            
            if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['ok' => true]);
                exit;
            }
            header('Location: cart.php?orden=' . urlencode($orden));
            exit;
        }

        $idWishlist = (int) ($_POST['id_wishlist'] ?? 0);
        $nombreJuego = (string) ($_POST['nombre_juego'] ?? '');

        if ($idWishlist > 0 && $action === 'delete') {
            cart_delete_item($BBDD, $idUsuario, $nickname, $idWishlist);
            header('Location: cart.php?orden=' . urlencode($orden));
            exit;
        }
        if ($idWishlist > 0 && $nombreJuego != '' && $action === 'buy') {
            cartBuyOne($BBDD, $idUsuario, $nickname, $nombreJuego, $idWishlist);
            header('Location: cart.php?orden=' . urlencode($orden));
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

<style>
    #buyAllButton {
        border: none;
        padding: 5px 10px;
        border: 1px solid var(--color-greyneutral-7);
        background: linear-gradient(180deg, rgba(59, 158, 255, 0.20), rgba(59, 158, 255, 0.10));
        border-color: rgba(59, 158, 255, 0.28);
        color: var(--color-slate-12);
        cursor: pointer;
        border-radius: 5px;
    }
    #buyAllButton:hover {
        background: linear-gradient(180deg, rgba(59, 158, 255, 0.30), rgba(59, 158, 255, 0.15));
        border-color: rgba(59, 158, 255, 0.40);
    }
</style>

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
                    <h1>Carrito de compras de <?= e($nickname) ?></h1>
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
                    <option value="aleatorio" <?= $orden === 'aleatorio' ? 'selected' : '' ?>>Aleatorio</option>
                    <option value="nombre(↑)" <?= $orden === 'nombre(↑)' ? 'selected' : '' ?>>Nombre ↑</option>
                    <option value="nombre(↓)" <?= $orden === 'nombre(↓)' ? 'selected' : '' ?>>Nombre ↓</option>
                    <option value="precio(↑)" <?= $orden === 'precio(↑)' ? 'selected' : '' ?>>Precio ↑</option>
                    <option value="precio(↓)" <?= $orden === 'precio(↓)' ? 'selected' : '' ?>>Precio ↓</option>
                    <option value="descuento(↑)" <?= $orden === 'descuento(↑)' ? 'selected' : '' ?>>Descuento ↑</option>
                    <option value="descuento(↓)" <?= $orden === 'descuento(↓)' ? 'selected' : '' ?>>Descuento ↓</option>
                    <option value="fecha(↑)" <?= $orden === 'fecha(↑)' ? 'selected' : '' ?>>Lanzamiento ↑</option>
                    <option value="fecha(↓)" <?= $orden === 'fecha(↓)' ? 'selected' : '' ?>>Lanzamiento ↓</option>
                    <option value="resenas(↑)" <?= $orden === 'resenas(↑)' ? 'selected' : '' ?>>Reseñas ↑</option>
                    <option value="resenas(↓)" <?= $orden === 'resenas(↓)' ? 'selected' : '' ?>>Reseñas ↓</option>
                    <option value="positivas" <?= $orden === 'positivas' ? 'selected' : '' ?>>Reseñas +</option>
                    <option value="negativas" <?= $orden === 'negativas' ? 'selected' : '' ?>>Reseñas -</option>
                </select>
            </form>
        </div>
        <form style="justify-content: flex-end;" name="buyAllForm" method="post" onsubmit="return confirm('¿Estás seguro de que quieres comprar todos los artículos?');" class="wishlist-sort-form">
            <div style="color: #fff; border: none; padding: 5px 10px; background-color: #23262e; border-radius: 5px; margin-right: 10px;">
                <?php 
                    $sqlPriceGamesInCart = "
                        SELECT 
                            SUM(CASE 
                                    WHEN J.descuento > 0 THEN 
                                        GREATEST(0, J.precio - (J.precio * (J.descuento / 100))) 
                                    ELSE 
                                        J.precio 
                                END) AS total_price
                        FROM Carrito C
                        INNER JOIN Juegos J ON J.nombre_juego = C.nombre_juego
                        WHERE C.id_usuario = :id_usuario
                          AND C.nickname = :nickname
                    ";
                    $stmtPriceGamesInCart = $BBDD->prepare($sqlPriceGamesInCart);
                    $stmtPriceGamesInCart->execute([
                        ':id_usuario' => $idUsuario ?? 0,
                        ':nickname' => $nickname ?? '',
                    ]); 

                    $cartCount = $stmtPriceGamesInCart->fetch(PDO::FETCH_ASSOC)['total_price'] ?? 0;
                    echo $cartCount > 0 ? number_format($cartCount, 2) . "€" : "Gratis";
                ?>
            </div>
            <button type="submit" id="buyAllButton"
                    name="buyAll" value="usuario">
                Comprar todo
            </button>
        </form>
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
            Tu carrito está actualmente vacía. ¡Explora la tienda para añadir juegos!
        </div>
    <?php else: ?>
        <div id="wishlist-list" class="wishlist-list" data-save-url="cart.php?orden=<?= e($orden) ?>">
            <?php $posicion = 1; ?>
            <?php foreach ($wishlist as $juego): ?>
                <?php 
                    $hasDiscount = (float)$juego['descuento'] > 0;
                    $imgUrl = getGameImageUrl($juego['id_juego'], 'wide-cover');
                ?>
                    <div class="wishlist-item" data-wishlist-id="<?= (int)$juego['id_Carrito'] ?>"
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
                                        <span class="price-final"><?= ((float) $juego['precio'] <= 0 ? 'Gratis' : number_format($juego['precio'], 2, ',', '.') . '€') ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Botón Comprar -->
                            <form method="post" name="buyForm" onsubmit="return confirm('¿Comprar <?php echo e($juego['nombre_juego']); ?>?');" class="delete-form">
                                <input type="hidden" name="action" value="buy">
                                <input type="hidden" name="nombre_juego" value="<?= e($juego['nombre_juego']) ?>">
                                <input type="hidden" name="id_wishlist" value="<?= (int)$juego['id_Carrito'] ?>">
                                <button type="submit" class="btn-delete" aria-label="Comprar">
                                    <span class="material-symbols-outlined">shopping_cart</span>
                                </button>
                            </form>

                            <!-- Botón Eliminar -->
                            <form method="post" onsubmit="return confirm('¿Quitar de la lista?');" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_wishlist" value="<?= (int)$juego['id_Carrito'] ?>">
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

<script src="../JS/cart.js" defer></script>

<?php include '../GENERAL/[main_END - footer].php'; ?>
<?php include '../GENERAL/[Page_END].php'; ?>