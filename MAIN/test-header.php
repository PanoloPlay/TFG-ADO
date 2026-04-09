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
    $recientes = array_slice($juegos, 3, 5);
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<link rel="stylesheet" href="../CSS/index.css">

<?php require_once '../GENERAL/test_header_dropdown.php'; ?>

<section class="hero">
        <div class="hero__content">
            <p class="eyebrow">Inspirado en Steam</p>
            <h1>Tu biblioteca, tus juegos y tu perfil en una sola tienda.</h1>
            <p class="hero__text">
                Una experiencia oscura, rápida y limpia para gestionar juegos, amigos y logros con un estilo cercano a Steam.
            </p>

            <div class="hero__actions">
                <?php if (!empty($_SESSION['id_usuario'])): ?>
                    <a href="./MAIN/profile.php" class="btn-primary">Ir a mi perfil</a>
                <?php else: ?>
                    <a href="./AUTH/login.php" class="btn-primary">Iniciar sesión</a>
                    <a href="./AUTH/register.php" class="btn-secondary">Crear cuenta</a>
                <?php endif; ?>
            </div>
        </div>

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
                        ?>
                        <article class="feature-card">
                            <div class="feature-card__thumb">
                                <?= e(mb_substr($juego['nombre_juego'], 0, 1, 'UTF-8')) ?>
                            </div>
                            <div class="feature-card__body">
                                <h3><?= e($juego['nombre_juego']) ?></h3>
                                <p><?= e($juego['desarrollador']) ?></p>
                                <span><?= number_format((float)$precioFinal, 2, ',', '.') ?> €</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-box">
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
                    ?>
                    <article class="game-card">
                        <div class="game-card__art">
                            <?= e(mb_substr($juego['nombre_juego'], 0, 1, 'UTF-8')) ?>
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
                <h2>Acceso rápido</h2>
            </div>

            <div class="quick-grid">
                <a href="#" class="quick-card">
                    <span class="material-symbols-outlined">category</span>
                    <strong>Categorías</strong>
                    <small>Explora por género</small>
                </a>

                <a href="#" class="quick-card">
                    <span class="material-symbols-outlined">local_fire_department</span>
                    <strong>Ofertas</strong>
                    <small>Juegos con descuento</small>
                </a>

                <a href="#" class="quick-card">
                    <span class="material-symbols-outlined">library_books</span>
                    <strong>Biblioteca</strong>
                    <small>Tu colección</small>
                </a>

                <a href="#" class="quick-card">
                    <span class="material-symbols-outlined">group</span>
                    <strong>Amigos</strong>
                    <small>Conecta y juega</small>
                </a>
            </div>
        </article>

        <article class="panel">
            <div class="section__head">
                <h2>Recomendado</h2>
            </div>

            <?php if ($recientes): ?>
                <div class="recommend-list">
                    <?php foreach ($recientes as $juego): ?>
                        <div class="recommend-item">
                            <div class="recommend-item__thumb">
                                <?= e(mb_substr($juego['nombre_juego'], 0, 1, 'UTF-8')) ?>
                            </div>
                            <div class="recommend-item__body">
                                <strong><?= e($juego['nombre_juego']) ?></strong>
                                <span><?= e($juego['desarrollador']) ?></span>
                            </div>
                        </div>
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