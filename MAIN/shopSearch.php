<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/wishlist_queries.php';
require_once '../BBDD/gameSearch_queries.php';

$orden = $_POST['orden'] ?? 'ninguno';
$errorWishlist = '';

$search = $_POST["search"] ?? null;

$allCategories;
if (isset($_POST['minPrice'])) {
    $minPrice =  number_format((double) $_POST['minPrice'], 2) ?? 0;
}
else {
    $minPrice = 0;
}
if (isset($_POST['maxPrice'])) {
    if ($_POST['maxPrice'] != null) {
        $maxPrice = number_format((double) $_POST['maxPrice'], 2) ?? 999;
    }
    else {
        $maxPrice = 999;
    }
}
else {
    $maxPrice = 999;
}

if ($minPrice < 0) {
    $minPrice = 0;
}
if ($maxPrice == null) {
    $maxPrice = 999;
} 
else if ($maxPrice < $minPrice) {
    $maxPrice = $minPrice;
}

if (isset($_GET['discount'])) {
    $onlyDiscount = true;
    $_POST['onlyDiscount'] = true;
}
else {
    $onlyDiscount = isset($_POST['onlyDiscount']) ?? false;
}

if (isset($_GET['recent'])) {
    $onlyRecent = true;
    $_POST['onlyRecent'] = true;
}
else {
    $onlyRecent = isset($_POST['onlyRecent']) ?? true;
}

$genres = get_genres($BBDD);

if (isset($_GET['categorias'])) {
    $categories = [$_GET['categorias']];
}
else {
    $categories = [];
    foreach ($genres as $genre) {
        if (isset($_POST[$genre['Categoria']])) {
            array_push($categories, $_POST[$genre['Categoria']]);
        }
    }
}

$AllLanguages = get_languages($BBDD);

$languages = [];
foreach ($AllLanguages as $language) {
    if (isset($_POST[$language['id_idioma']])) {
        array_push($languages, $_POST[$language['id_idioma']]);
    }
}

if (isset($_POST['minDate']) && isset($_POST['maxDate'])) {
    if ($_POST['maxDate'] < $_POST['minDate']) {
        $_POST['maxDate'] = $_POST['minDate'];
    }
}

if (isset($_POST['minDate']) && isset($_POST['maxDate'])) {
    if ($_POST['maxDate'] < $_POST['minDate']) {
        $_POST['maxDate'] = $_POST['minDate'];
    }
}

$minDate = $_POST['minDate'] ?? "";
$maxDate = $_POST['maxDate'] ?? "";

$wishlist = shop_get_games($BBDD, '', $orden, $categories, $languages, $minPrice, $maxPrice, $onlyDiscount, $onlyRecent, $minDate, $maxDate);

foreach ($genres as $genre) {
    if (isset($_POST[$genre['Categoria']])) {
        array_push($categories, $_POST[$genre['Categoria']]);
    }
}

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
        <div class="wishlist-hero-content row">
            <div class="wishlist-profile col-12">
                <div class="profile-info">
                    <h1>Tienda de Juegos</h1>
                    <div class="profile-badges">
                        <span class="badge badge-dark"><?= count($wishlist) ?> artículos</span>
                        <span class="badge badge-primary">Orden <?php echo $orden ?></span>
                    </div>
                </div>
            </div>
            <form id="formOG1" method="post" action="./shopSearch.php" class="wishlist-sort-form col-12">
                <div class="row">
                    <div class="col-10">
                        <label>Precio entre</label><br>
                        <input type="number" name="minPrice" step="0.01" placeholder="Mínimo" style="width: 45%;" class="wishlist-select" <?php if (isset($_POST['minPrice'])) { ?> value="<?php if($_POST['minPrice'] != null){(double) $_POST['minPrice'];} ?>" <?php } ?>></input> y
                        <input type="number" name="maxPrice" step="0.01" placeholder="Máximo" style="width: 45%;" class="wishlist-select" <?php if (isset($_POST['maxPrice'])) { ?> value="<?php (double) $_POST['maxPrice'] ?>" <?php } else { ?> 999 <?php }?>></input> €
                        <br><br>
                        <label class="checkbox-filter">
                            <input type="checkbox" class="categoria-checkbox" name="onlyDiscount" <?php if (isset($_POST['onlyDiscount'])) { ?> checked <?php } ?>>
                            <span>Solo Descuentos</span>
                        </label>
                        <label class="checkbox-filter">
                            <input type="checkbox" class="categoria-checkbox" name="onlyRecent" <?php if (isset($_POST['onlyRecent'])) { ?> checked <?php } ?>>
                            <span>Solo Recientemente</span>
                        </label>
                        <br><br>
                        <label>Publicado entre</label><br>
                        <input type="date" style="width: 45%;" class="wishlist-select" name="minDate" <?php if (isset($_POST['minDate'])) { ?> value="<?= e($_POST['minDate']) ?>" <?php } ?>></input> y
                        <input type="date" style="width: 45%;" class="wishlist-select" name="maxDate" <?php if (isset($_POST['maxDate'])) { ?> value="<?= e($_POST['maxDate']) ?>" <?php } ?>></input>
                        <?php if ($genres != null) { ?>
                        <br><br>
                        <label>Géneros</label><br>
                        <?php
                        foreach ($genres as $genre) {
                        ?>
                            <label class="checkbox-filter">
                                <input type="checkbox" name="<?= e($genre['Categoria']) ?>" class="categoria-checkbox" value="<?= e($genre['Categoria']) ?>" <?php if (in_array($genre['Categoria'], $categories)) { ?> checked <?php } ?>>
                                <span><?= e($genre['Categoria']) ?></span>
                            </label>
                        <?php
                        }}
                        ?>

                        <?php if ($AllLanguages != null) { ?>
                        <br><br>
                        <label>Géneros</label><br>
                        <?php
                        foreach ($AllLanguages as $language) {
                        ?>
                            <label class="checkbox-filter">
                                <input type="checkbox" name="<?= e($language['id_idioma']) ?>" class="categoria-checkbox" value="<?= e($language['id_idioma']) ?>" <?php if (in_array($language['id_idioma'], $languages)) { ?> checked <?php } ?>>
                                <span><?= e($language['Idioma']) ?> (<?= e($language['id_idioma']) ?>)</span>
                            </label>
                        <?php
                        }}
                        ?>
                    </div>
                    <div class="col-2">
                        <div class="row">
                            <div class="col-12">
                                <label for="orden">Ordenar por:</label><br>
                                <select name="orden" id="orden" class="wishlist-select" onchange="this.form.submit()">
                                    <option value="ninguno" <?= $orden === 'ninguno' ? 'selected' : '' ?>>Ninguno</option>
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
                                <br><br>
                                <button type="submit" class="wishlist-select">Filtrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <form id="formOG2" class="wishlist-search-form" onsubmit="return false;">
        <label for="wishlist-search">Buscar juego:</label>
        <input
            type="search"
            id="wishlist-search"
            class="wishlist-search"
            placeholder="Escribe el nombre del juego..."
            autocomplete="off"
            <?php if (isset($search)) { ?> value="<?= e($search) ?>"<?php }?>
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
                    <div class="wishlist-item" data-wishlist-id="<?= (int)$juego['id_juego'] ?>"
                        data-search="<?= e($juego['nombre_juego'] . ' ' . ($juego['desarrollador'] ?? '')) ?>">

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
                                        <?php 
                                            if ((number_format($juego['precio'], 2)) == 0) {
                                                ?>
                                                <span class="price-final">Gratis</span>
                                                <?php
                                            } 
                                            else {
                                                ?>
                                                <span class="price-final"><?= number_format($juego['precio'], 2) ?>€</span>
                                                <?php
                                            }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
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