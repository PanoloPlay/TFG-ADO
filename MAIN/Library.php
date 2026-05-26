<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/library_queries.php';

require_once '../GENERAL/auth_guard.php';

$usuario = ['id_usuario' => $_SESSION['id_usuario'], 'nickname' => $_SESSION['nickname']];
$avatarData = getProfileAvatarData($usuario['nickname']);
$initial = $avatarData['initial'];
$avatarPath = $avatarData['avatarPath'];
$avatarClass = $avatarData['avatarClass'];

$idUsuario = (int) $_SESSION['id_usuario'];
$nickname  = (string) $_SESSION['nickname'];
$orden = $_GET['orden'] ?? 'nombre';
$errorLibrary = '';

try {
    // No hay reordenación personalizada en biblioteca.
} catch (Throwable $e) {
    $errorLibrary = $e->getMessage();
}

$library = library_get_items($BBDD, $idUsuario, $nickname, $orden);
?>

<?php include '../GENERAL/[html_START - head_START].php'; ?>
<link rel="stylesheet" href="../CSS/library.css">

<?php include '../GENERAL/[head_END - body_START - header - main_START].php'; ?>
<div class="library-container">
    <div class="library-hero">
        <div class="library-hero-content">
            <div class="library-profile">
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
                    <h1>Biblioteca de <?= e($nickname) ?></h1>
                    <div class="profile-badges">
                        <span class="badge badge-dark"><?= count($library) ?> juegos</span>
                    </div>
                </div>
            </div>
            <form method="get" class="library-sort-form">
                <label for="orden">Ordenar por:</label>
                <select name="orden" id="orden" class="library-select" onchange="this.form.submit()">
                    <option value="nombre(↑)" <?= $orden === 'nombre(↑)' ? 'selected' : '' ?>>Nombre ↑</option>
                    <option value="nombre(↓)" <?= $orden === 'nombre(↓)' ? 'selected' : '' ?>>Nombre ↓</option>
                    <option value="fecha(↑)" <?= $orden === 'fecha(↑)' ? 'selected' : '' ?>>Lanzamiento ↑</option>
                    <option value="fecha(↓)" <?= $orden === 'fecha(↓)' ? 'selected' : '' ?>>Lanzamiento ↓</option>
                    <option value="resenas(↑)" <?= $orden === 'resenas(↑)' ? 'selected' : '' ?>>Reseñas ↑</option>
                    <option value="resenas(↓)" <?= $orden === 'resenas(↓)' ? 'selected' : '' ?>>Reseñas ↓</option>
                    <option value="positivas" <?= $orden === 'positivas' ? 'selected' : '' ?>>Reseñas +</option>
                    <option value="negativas" <?= $orden === 'negativas' ? 'selected' : '' ?>>Reseñas -</option>
                </select>
            </form>
        </div>
    </div>

    <?php if ($errorLibrary): ?>
        <div class="library-alert alert-info">
            <?= e($errorLibrary) ?>
        </div>
    <?php endif; ?>

    <form class="library-search-form" onsubmit="return false;">
        <label for="library-search">Buscar juego:</label>
        <input
            type="search"
            id="library-search"
            class="library-search"
            placeholder="Escribe el nombre del juego..."
            autocomplete="off"
        >
    </form>

    <div id="library-empty-search" class="library-alert alert-info" hidden>
        No se han encontrado juegos con ese nombre.
    </div>

    <?php if (empty($library)): ?>
        <div class="library-alert alert-info">
            Tu biblioteca está vacía. ¡Añade juegos desde la tienda para verlos aquí!
        </div>
    <?php else: ?>
        <div id="library-list" class="library-list">
            <?php foreach ($library as $juego): ?>
                <?php $imgUrl = getGameImageUrl($juego['id_juego'], 'wide-cover'); ?>
                <div class="library-item" data-library-id="<?= (int)$juego['id_Biblioteca'] ?>"
                    data-search="<?= e($juego['nombre_juego'] . ' ' . ($juego['desarrollador'] ?? '')) ?>">

                    <a href="../MAIN/game.php?id=<?= (int)$juego['id_juego'] ?>" class="library-link" aria-label="Ver <?= e($juego['nombre_juego']) ?>">
                        <div class="library-capsule" style="background-image: url('<?= e($imgUrl) ?>');"></div>

                        <div class="library-info">
                            <h2><?= e($juego['nombre_juego']) ?></h2>
                            <div class="library-meta">
                                <span>Lanzamiento: <?= date('d M Y', strtotime($juego['fecha_publicacion'])) ?></span>
                                <span class="developer"><?= e($juego['desarrollador'] ?? 'Desconocido') ?></span>
                            </div>
                            <div class="library-reviews">
                                <?= renderReviewBadge($juego) ?>
                                <span class="review-count">(<?= (int)$juego['total_resenas'] ?>)</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="../JS/library.js" defer></script>

<?php include '../GENERAL/[main_END - footer].php'; ?>
<?php include '../GENERAL/[Page_END].php'; ?>