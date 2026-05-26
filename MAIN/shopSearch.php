<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/wishlist_queries.php';
require_once '../BBDD/gameSearch_queries.php';

$orden = $_POST['orden'] ?? 'ninguno';
$errorWishlist = '';

$search = $_POST["search"] ?? null;

$minPrice = 0;
$maxPrice = 1000;
$minPriceInputValue = '';
$maxPriceInputValue = '';

if (isset($_POST['minPrice']) && $_POST['minPrice'] !== '') {
    $minPriceInputValue = trim((string) $_POST['minPrice']);
    $minPrice = (int) $_POST['minPrice'];
    $minPrice = max(0, min(1000, round($minPrice / 5) * 5));
}

if (isset($_POST['maxPrice']) && $_POST['maxPrice'] !== '') {
    $maxPriceInputValue = trim((string) $_POST['maxPrice']);
    $maxPrice = (int) $_POST['maxPrice'];
    $maxPrice = max(0, min(1000, round($maxPrice / 5) * 5));
}

if ($minPrice < 0) {
    $minPrice = 0;
}
if ($maxPrice < $minPrice) {
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
    $onlyRecent = isset($_POST['onlyRecent']);
}

$genres = get_genres($BBDD);

$categories = [];
if (isset($_POST['genres']) && is_array($_POST['genres'])) {
    foreach ($_POST['genres'] as $genreOption) {
        $genreOption = trim((string) $genreOption);
        if ($genreOption !== '') {
            $categories[] = $genreOption;
        }
    }
}

$AllLanguages = get_languages($BBDD);

$languages = [];
if (isset($_POST['languages']) && is_array($_POST['languages'])) {
    foreach ($_POST['languages'] as $languageOption) {
        $languageOption = trim((string) $languageOption);
        if ($languageOption !== '') {
            $languages[] = $languageOption;
        }
    }
}

if (isset($_POST['minDate']) && isset($_POST['maxDate'])) {
    if ($_POST['maxDate'] < $_POST['minDate']) {
        $_POST['maxDate'] = $_POST['minDate'];
    }
}

$minDate = $_POST['minDate'] ?? "";
$maxDate = $_POST['maxDate'] ?? "";

$wishlist = shop_get_games($BBDD, $search ?? '', $orden, $categories, $languages, $minPrice, $maxPrice, $onlyDiscount, $onlyRecent, $minDate, $maxDate);

// Etiquetas de precio para los controles deslizantes
$minPriceLabelText = 'Mínimo';
$maxPriceLabelText = 'Máximo';
if ($minPrice === 0 && $maxPrice === 0) {
    $minPriceLabelText = $maxPriceLabelText = 'Gratis';
} else {
    if ($minPrice > 0) {
        $minPriceLabelText = number_format($minPrice, 0, ',', '.') . '€';
    }
    if ($maxPrice < 1000) {
        $maxPriceLabelText = number_format($maxPrice, 0, ',', '.') . '€';
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
<link rel="stylesheet" href="../CSS/shopSearch.css">

<?php include '../GENERAL/[head_END - body_START - header - main_START].php'; ?>
<div class="wishlist-container">
    <div class="wishlist-hero">
        <div class="wishlist-hero-content row">
            <div class="wishlist-profile col-12">
                <div class="profile-info">
                    <h1>Tienda de Juegos</h1>
                    <div class="profile-badges">
                        <span id="shop-result-count" class="badge badge-dark"><?= count($wishlist) ?> artículos</span>
                        <span class="badge badge-primary">Orden <?php echo $orden ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="shopSearchForm" method="post" action="./shopSearch.php">
        <div class="shop-search-layout">
            <main class="shop-search-main">
                <div class="shop-search-top">
                    <div class="wishlist-search-form">
                        <label for="wishlist-search">Buscar juego:</label>
                        <div class="search-input-row">
                            <input
                                type="search"
                                id="wishlist-search"
                                name="search"
                                class="wishlist-search"
                                placeholder="Escribe el nombre del juego..."
                                autocomplete="off"
                                <?php if (isset($search)) { ?> value="<?= e($search) ?>"<?php }?>
                            >
                            <button type="submit" class="shop-search-submit">Buscar</button>
                        </div>
                    </div>
                    <div class="panel-card shop-search-order-card">
                        <label for="orden">Ordenar por:</label>
                        <select name="orden" id="orden" class="wishlist-select">
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
                    </div>
                </div>
                <div id="shop-search-results">
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
                                <span>Lanzamiento: <?= date('d M Y', strtotime($juego['fecha_publicacion'] ?? '')) == '01 Jan 1970' ? 'Por confirmarse' : date('d M Y', strtotime($juego['fecha_publicacion'] ?? '')) ?></span>
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
        </div>
    <?php endif; ?>
        </main>

        <aside class="shop-search-sidebar shop-search-filters">
            <details class="panel-card filter-dropdown" open>
                <summary class="panel-card__header filter-dropdown__summary">
                    <strong>Precio</strong>
                    <span class="filter-dropdown__icon material-symbols-outlined">expand_more</span>
                </summary>
                <div class="filter-dropdown__content">
                    <div class="price-range-labels">
                        <span class="price-range-label price-range-label--from">Desde <strong id="minPriceLabel"><?= e($minPriceLabelText) ?></strong></span>
                        <span class="price-range-label price-range-label--until">Hasta <strong id="maxPriceLabel"><?= e($maxPriceLabelText) ?></strong></span>
                        <span class="price-range-label price-range-label--free" id="freePriceLabel" <?= $minPrice === 0 && $maxPrice === 0 ? '' : 'hidden' ?>>
                            <strong>Gratis</strong>
                        </span>
                    </div>
                    <div class="price-range-sliders">
                        <input
                            type="number"
                            id="minPrice"
                            name="minPrice"
                            min="0"
                            max="1000"
                            step="0.01"
                            placeholder="Min"
                            value="<?= isset($_POST['minPrice']) ? e($minPriceInputValue) : '' ?>"
                            inputmode="decimal"
                            aria-label="Precio mínimo"
                        >
                        <input
                            type="number"
                            id="maxPrice"
                            name="maxPrice"
                            min="0"
                            max="1000"
                            step="0.01"
                            placeholder="Max"
                            value="<?= isset($_POST['maxPrice']) ? e($maxPriceInputValue) : '' ?>"
                            inputmode="decimal"
                            aria-label="Precio máximo"
                        >
                    </div>
                    <label class="checkbox-filter">
                        <input type="checkbox" class="categoria-checkbox" name="onlyDiscount" <?= isset($_POST['onlyDiscount']) ? 'checked' : '' ?>>
                        <span>Solo descuentos</span>
                    </label>
                </div>
            </details>

            <details class="panel-card filter-dropdown" open>
                <summary class="panel-card__header filter-dropdown__summary">
                    <strong>Publicación</strong>
                    <span class="filter-dropdown__icon material-symbols-outlined">expand_more</span>
                </summary>
                <div class="filter-dropdown__content">
                    <label class="checkbox-filter">
                        <input type="checkbox" class="categoria-checkbox" name="onlyRecent" <?= isset($_POST['onlyRecent']) ? 'checked' : '' ?> >
                        <span>Solo recientes</span>
                    </label>
                    <div class="filter-section-row">
                        <div class="filter-field">
                            <label for="minDate">Desde</label>
                            <input type="date" id="minDate" name="minDate" class="wishlist-select" <?php if (isset($_POST['minDate'])) { ?> value="<?= e($_POST['minDate']) ?>" <?php } ?> >
                        </div>
                        <div class="filter-field">
                            <label for="maxDate">Hasta</label>
                            <input type="date" id="maxDate" name="maxDate" class="wishlist-select" <?php if (isset($_POST['maxDate'])) { ?> value="<?= e($_POST['maxDate']) ?>" <?php } ?> >
                        </div>
                    </div>
                </div>
            </details>

            <?php if ($genres != null) { ?>
            <details class="panel-card filter-dropdown" open>
                <summary class="panel-card__header filter-dropdown__summary">
                    <strong>Géneros</strong>
                    <span class="filter-dropdown__icon material-symbols-outlined">expand_more</span>
                </summary>
                <div class="filter-dropdown__content">
                    <div class="search-box">
                        <span class="material-symbols-outlined search-box__icon">search</span>
                        <input
                            type="text"
                            class="form-control js-filter-input"
                            placeholder="Buscar categoría..."
                            data-filter-target="categoriesList"
                        >
                        <button
                            type="button"
                            class="btn-icon js-clear-filter"
                            data-filter-target="categoriesList"
                            title="Limpiar búsqueda"
                        >
                            <span class="material-symbols-outlined">refresh</span>
                        </button>
                    </div>
                    <div class="option-list option-list--scroll scrollbar-theme" id="categoriesList">
                        <?php foreach ($genres as $genre): ?>
                            <?php
                            $genreId = e($genre['Categoria']);
                            $checked = in_array((string) $genre['Categoria'], $categories, true);
                            ?>
                            <label class="option-item js-filter-item checkbox-filter">
                                <input
                                    type="checkbox"
                                    name="genres[]"
                                    class="categoria-checkbox"
                                    value="<?= $genreId ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                >
                                <span><?= e($genre['Categoria']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </details>
            <?php } ?>

            <?php if ($AllLanguages != null) { ?>
            <details class="panel-card filter-dropdown" open>
                <summary class="panel-card__header filter-dropdown__summary">
                    <strong>Idiomas</strong>
                    <span class="filter-dropdown__icon material-symbols-outlined">expand_more</span>
                </summary>
                <div class="filter-dropdown__content">
                    <div class="search-box">
                        <span class="material-symbols-outlined search-box__icon">search</span>
                        <input
                            type="text"
                            class="form-control js-filter-input"
                            placeholder="Buscar idioma..."
                            data-filter-target="languagesList"
                        >
                        <button
                            type="button"
                            class="btn-icon js-clear-filter"
                            data-filter-target="languagesList"
                            title="Limpiar búsqueda"
                        >
                            <span class="material-symbols-outlined">refresh</span>
                        </button>
                    </div>
                    <div class="option-list option-list--scroll scrollbar-theme" id="languagesList">
                        <?php foreach ($AllLanguages as $language): ?>
                            <?php
                            $languageId = e($language['id_idioma']);
                            $checked = in_array((string) $language['id_idioma'], $languages, true);
                            ?>
                            <label class="option-item js-filter-item checkbox-filter">
                                <input
                                    type="checkbox"
                                    name="languages[]"
                                    class="categoria-checkbox"
                                    value="<?= $languageId ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                >
                                <span><?= e($language['Idioma']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </details>
            <?php } ?>

        </aside>
        </div>
    </form>

<script src="../JS/wishlist.js" defer></script>
<script src="../JS/shopSearch.js" defer></script>

<?php include '../GENERAL/[main_END - footer].php'; ?>
<?php include '../GENERAL/[Page_END].php'; ?>