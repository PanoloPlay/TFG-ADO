<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>
<?php require_once '../BBDD/profile_queries.php'; ?>
<?php
$idSesion = (int) ($_SESSION['id_usuario'] ?? 0);
$nicknameSesion = $_SESSION['nickname'] ?? '';

if (empty($idSesion) || empty($nicknameSesion)) {
    header('Location: ../AUTH/login.php');
    exit;
}

$usuarioPerfil = getProfileUserByIdAndNickname($BBDD, $idSesion, $nicknameSesion);

if (!$usuarioPerfil) {
    header('Location: ../AUTH/login.php');
    exit;
}

$tusDeseos = getAllWishlistGames($BBDD, $idSesion, $nicknameSesion);
$totalDeseos = count($tusDeseos);
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<link rel="stylesheet" href="../CSS/profile.css">
<link rel="stylesheet" href="../CSS/library.css">
<link rel="stylesheet" href="../CSS/wishlist.css">

<script src="../JS/checkIfXExists.js" defer></script>
<script src="../JS/wishlist.js" defer></script>

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<section class="profile-topbar">
    <div class="profile-brand">
        <div class="brand-mark small">
            <span class="material-symbols-outlined">favorite</span>
        </div>
        <div>
            <strong>Mi lista de deseos</strong>
            <span><?= e($totalDeseos) ?> juegos</span>
        </div>
    </div>
</section>

<section class="site-main__shell">
    <article class="panel full wishlist-panel">
        <div class="wishlist-actions">
            <div class="wishlist-brand">
                <strong>Mis juegos guardados</strong>
                <span>Todos los juegos que has añadido a tu lista de deseos.</span>
            </div>
            <div class="filter-dropdown">
                <button type="button" class="btn-filter" id="btnFilterToggle">
                    <span class="material-symbols-outlined">filter_list</span>
                    Filtrar
                </button>
                <div class="filter-popover" id="filterPopover" aria-hidden="true">
                    <div class="popover-head">Campos visibles</div>
                    <label class="filter-option">
                        <input type="checkbox" value="name" checked>
                        Nombre
                    </label>
                    <label class="filter-option">
                        <input type="checkbox" value="price" checked>
                        Precio
                    </label>
                    <label class="filter-option">
                        <input type="checkbox" value="discount" checked>
                        Descuento
                    </label>
                    <label class="filter-option">
                        <input type="checkbox" value="date" checked>
                        Fecha de lanzamiento
                    </label>
                </div>
            </div>
        </div>

        <?php if (!empty($tusDeseos)): ?>
            <div class="wishlist-grid">
                <?php foreach ($tusDeseos as $juego): ?>
                    <?php
                        $precioFinal = (float) $juego['precio'];
                        $descuento = (float) ($juego['descuento'] ?? 0);
                        if ($descuento > 0) {
                            $precioFinal = $precioFinal * (1 - ($descuento / 100));
                        }
                        $fecha = !empty($juego['fecha_publicacion'])
                            ? date('d/m/Y', strtotime($juego['fecha_publicacion']))
                            : 'N/A';
                        $descuentoText = $descuento > 0
                            ? e(number_format($descuento, 0)) . '%'
                            : 'Sin descuento';
                    ?>
                    <article class="wishlist-card" data-juego-id="<?= (int) $juego['id_juego'] ?>">
                        <div class="game-thumb">
                            <?= e(mb_substr($juego['nombre_juego'], 0, 1, 'UTF-8')) ?>
                        </div>
                        <div class="game-content">
                            <h3 class="game-title field-name">
                                <a href="#" class="game-link"><?= e($juego['nombre_juego']) ?></a>
                            </h3>
                            <p class="game-developer"><?= e($juego['desarrollador']) ?></p>

                            <div class="game-meta">
                                <span class="meta-item field-date">
                                    <span class="material-symbols-outlined">event</span>
                                    <?= e($fecha) ?>
                                </span>
                                <span class="meta-item field-discount">
                                    <span class="material-symbols-outlined">local_fire_department</span>
                                    <?= $descuentoText ?>
                                </span>
                                <span class="meta-item field-price">
                                    <span class="material-symbols-outlined">paid</span>
                                    <?= number_format($precioFinal, 2, ',', '.') ?> €
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="material-symbols-outlined">inventory_2</span>
                <p>No hay juegos en tu lista de deseos por el momento.</p>
            </div>
        <?php endif; ?>
    </article>
</section>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>