<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>

<?php
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

    $destacados = array_slice($juegos, 0, 3);
    $recientes = array_slice($juegos, 0, 5);
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<link rel="stylesheet" href="../CSS/index.css">

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

    <br>
    <form method="POST" action="./shopSearch.php" class="hero__panel-top">
        <span class="material-symbols-outlined">Buscar Juegos</span>
        <input type="search" name="search"><button type="submit" class="btn-secondary"><span class="material-symbols-outlined">search</span></button>
    </form>
    <br><br>

    <section class="hero">
       
        <aside class="hero__panel">
            
            <div class="hero__panel-top">
                <span class="material-symbols-outlined">sports_esports</span>
                <strong>Destacados de hoy</strong>
            </div>

            <?php if ($destacados): ?>
                <div class="feature-stack">
                    <?php foreach ($destacados as $juego): ?>
                        <?php
                            $precioFinal = $juego['precio'];
                            $descuento = (float)($juego['descuento'] ?? 0);
                            if ($descuento > 0) {
                                $precioFinal = (float)$juego['precio'] * (1 - ($descuento / 100));
                            }

                            $imgUrl = getGameImageUrl((int)$juego['id_juego'], 'icon');
                        ?>
                        <a href="game.php?id=<?= (int)$juego['id_juego'] ?>" class="feature-link">
                        <article class="feature-card">
                            <div class="feature-card__thumb"
                                 style="background-image: url('<?= e($imgUrl) ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                            </div>
                            <div class="feature-card__body">
                                <h3><?= e($juego['nombre_juego']) ?></h3>
                                <p><?= e($juego['desarrollador']) ?></p>
                                <span><?= number_format((float)$precioFinal, 2, ',', '.') ?> €</span>
                            </div>
                        </article>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-box large">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <p>No hay juegos cargados todavía.</p>
                </div>
            <?php endif; ?>
        </aside>
    </section>

    <section class="section">
        <div class="section__head">
            <h2>Últimos lanzamientos</h2>
            <a href="#">Ver todo</a>
        </div>

        <?php if ($juegos): ?>
            <div class="game-grid">
                <?php foreach ($juegos as $juego): ?>
                    <?php
                        $precioFinal = $juego['precio'];
                        $descuento = (float)($juego['descuento'] ?? 0);
                        if ($descuento > 0) {
                            $precioFinal = (float)$juego['precio'] * (1 - ($descuento / 100));
                        }

                        $imgUrl = getGameImageUrl((int)$juego['id_juego'], 'wide-cover');
                    ?>
                    <a href="game.php?id=<?= (int)$juego['id_juego'] ?>" class="game-link">
                    <article class="game-card">
                        <div class="game-card__art"
                             style="background-image: url('<?= e($imgUrl) ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                        </div>

                        <div class="game-card__info">
                            <h3><?= e($juego['nombre_juego']) ?></h3>
                            <p><?= e($juego['desarrollador']) ?></p>

                            <div class="game-card__meta">
                                <span><?= e($juego['descuento'] ? $juego['descuento'] . '% dto.' : 'Sin descuento') ?></span>
                                <strong><?= number_format((float)$precioFinal, 2, ',', '.') ?> €</strong>
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

    <section class="section section--split">
        <article class="panel">
            <div class="section__head">
                <h2>Recomendado</h2>
            </div>

            <?php if ($recientes): ?>
                <div class="recommend-list">
                    <?php foreach ($recientes as $juego): ?>
                        <?php
                            $imgUrl = getGameImageUrl((int)$juego['id_juego'], 'banner');
                        ?>
                        <a href="game.php?id=<?= (int)$juego['id_juego'] ?>" class="recommend-link">
                        <div class="recommend-item">
                            <div class="recommend-item__thumb"
                                 style="background-image: url('<?= e($imgUrl) ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                            </div>
                            <div class="recommend-item__body">
                                <strong><?= e($juego['nombre_juego']) ?></strong>
                                <span><?= e($juego['desarrollador']) ?></span>
                            </div>
                        </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-box">
                    <span class="material-symbols-outlined">thumb_up_off_alt</span>
                    <p>Todo listo para empezar.</p>
                </div>
            <?php endif; ?>
        </article>
    </section>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>

<?php require_once '../GENERAL/[Page_END].php'; ?>