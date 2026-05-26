<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>

<?php
function formatFinalPrice(float $price, float $discount = 0): string
{
    $final = $price * (1 - ($discount / 100));
    $final = max(0, $final);

    return $final <= 0
        ? 'Gratis'
        : number_format($final, 2, ',', '.') . ' €';
}

function formatOriginalPrice(float $price): string
{
    return number_format($price, 2, ',', '.') . ' €';
}

// Ofertas: solo juegos con descuento
$stmt = $BBDD->prepare("
    SELECT
        id_juego,
        nombre_juego,
        descripcion,
        desarrollador,
        precio,
        descuento,
        fecha_publicacion
    FROM Juegos
    WHERE COALESCE(descuento, 0) > 0
    ORDER BY COALESCE(descuento, 0) DESC, fecha_publicacion DESC
    LIMIT 3
");
$stmt->execute();
$destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Últimos lanzamientos
$stmt = $BBDD->prepare("
    SELECT
        id_juego,
        nombre_juego,
        descripcion,
        desarrollador,
        precio,
        descuento,
        fecha_publicacion
    FROM Juegos
    ORDER BY fecha_publicacion DESC
    LIMIT 8
");
$stmt->execute();
$juegos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<link rel="stylesheet" href="../CSS/index.css">

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<section class="panel panel--search">

    <form method="POST" action="./shopSearch.php" class="wishlist-search-form">
        <label for="search">Buscar juego:</label>
        <div class="wishlist-search-form__group">
            <div class="wishlist-search-form__field">
                <input
                    id="search"
                    type="search"
                    name="search"
                    class="wishlist-search"
                    placeholder="Buscar juegos..."
                    autocomplete="off"
                >
            </div>
            <button type="submit" class="btn-secondary wishlist-search__button">
                <span class="material-symbols-outlined wishlist-search__icon">search</span>
                Buscar
            </button>
        </div>
    </form>
</section>

<section class="hero">
    <aside class="hero__panel">
        <div class="hero__panel-top">
            <span class="material-symbols-outlined">local_offer</span>
            <strong>Ofertas</strong>
            <a href="../MAIN/shopSearch.php?discount">Ver todo</a>
        </div>
        
        <?php if (!empty($destacados)): ?>
            <div class="feature-stack">
                <?php foreach ($destacados as $juego): ?>
                    <?php
                        $descuento = (float)($juego['descuento'] ?? 0);
                        $imgUrl = getGameImageUrl((int)$juego['id_juego'], 'icon');
                        $precioOriginal = (float)$juego['precio'];
                        $precioFinal = $precioOriginal * (1 - ($descuento / 100));
                        $precioFinal = max(0, $precioFinal);
                    ?>
                    <a href="game.php?id=<?= (int)$juego['id_juego'] ?>" class="feature-link">
                        <article class="feature-card">
                            <div
                                class="feature-card__thumb"
                                style="background-image: url('<?= e($imgUrl) ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"
                            ></div>

                            <div class="feature-card__body">
                                <h3><?= e($juego['nombre_juego']) ?></h3>
                                <p><?= e($juego['desarrollador']) ?></p>

                                <div class="price-widget">
                                    <?php if ($descuento > 0): ?>
                                        <div class="price-discount-badge">-<?= (int)$juego['descuento'] ?>%</div>
                                        <div class="price-values">
                                            <span class="price-original"><?= formatOriginalPrice($precioOriginal) ?></span>
                                            <strong class="price-final"><?= $precioFinal <= 0 ? 'Gratis' : formatFinalPrice($precioOriginal, $descuento) ?></strong>
                                        </div>
                                    <?php else: ?>
                                        <div class="price-values no-discount">
                                            <strong class="price-final"><?= formatFinalPrice($precioOriginal, $descuento) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-box large">
                <span class="material-symbols-outlined">inventory_2</span>
                <p>No hay ofertas activas ahora mismo.</p>
            </div>
        <?php endif; ?>
    </aside>
</section>

<section class="section">
    <div class="section__head">
        <h2>Últimos lanzamientos</h2>
        <a href="../MAIN/shopSearch.php?recent">Ver todo</a>
    </div>

    <?php if (!empty($juegos)): ?>
        <div class="game-grid">
            <?php foreach ($juegos as $juego): ?>
                <?php
                    $descuento = (float)($juego['descuento'] ?? 0);
                    $imgUrl = getGameImageUrl((int)$juego['id_juego'], 'wide-cover');
                    $precioOriginal = (float)$juego['precio'];
                ?>
                <a href="game.php?id=<?= (int)$juego['id_juego'] ?>" class="game-link">
                    <article class="game-card">
                        <div
                            class="game-card__art"
                            style="background-image: url('<?= e($imgUrl) ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;"
                        ></div>

                        <div class="game-card__info">
                            <h3><?= e($juego['nombre_juego']) ?></h3>
                            <p><?= e($juego['desarrollador']) ?></p>

                            <div class="game-card__meta">
                                <div class="price-widget">
                                    <?php if ($descuento > 0): ?>
                                        <div class="price-discount-badge">-<?= (int)$juego['descuento'] ?>%</div>
                                        <div class="price-values">
                                            <span class="price-original"><?= formatOriginalPrice($precioOriginal) ?></span>
                                            <strong class="price-final"><?= formatFinalPrice($precioOriginal, $descuento) ?></strong>
                                        </div>
                                    <?php else: ?>
                                        <div class="price-values no-discount">
                                            <strong class="price-final"><?= formatFinalPrice($precioOriginal, $descuento) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-box large">
            <span class="material-symbols-outlined">search_off</span>
            <p>No hay juegos disponibles todavía.</p>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>