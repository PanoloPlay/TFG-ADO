<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>
<?php
require_once "../GENERAL/auth_guard.php";
require_once "../BBDD/profile_queries.php";

$idUsuario = (int)$_SESSION['id_usuario'];
$nicknameSesion = $_SESSION['nickname'];

$data = getProfilePageData($BBDD, $idUsuario, $nicknameSesion);

$usuario = $data['usuario'];
if (!$usuario) {
    header("Location: ../AUTH/logout.php");
    exit;
}

$fechaRegistro = !empty($usuario['fecha_registro'])
    ? date('d/m/Y H:i', strtotime($usuario['fecha_registro']))
    : 'No disponible';

$initial = strtoupper(mb_substr($usuario['nickname'], 0, 1, 'UTF-8'));
$avatarSeed = crc32($usuario['nickname']);
$avatarClass = 'avatar-' . (($avatarSeed % 6) + 1);
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

    <link rel="stylesheet" href="../CSS/auth.css">
    <link rel="stylesheet" href="../CSS/profile.css">

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

    <section class="profile-hero">
        <div class="profile-cover"></div>

        <div class="profile-summary">
            <div class="profile-avatar <?= e($avatarClass) ?>">
                <?= e($initial) ?>
            </div>

            <div class="profile-main">
                <div class="profile-headline">
                    <h1><?= e($usuario['nombre_usuario']) ?></h1>
                    <span class="profile-status">
                        <span class="status-dot"></span>
                        En línea
                    </span>
                </div>

                <p class="profile-subtitle"><?= e($usuario['nickname']) ?></p>
                <p class="profile-bio">
                    <?= e($usuario['descripcion'] ?: 'Sin descripción todavía.') ?>
                </p>

                <div class="profile-badges">
                    <span>
                        <span class="material-symbols-outlined">mail</span>
                        <?= e($usuario['correo']) ?>
                    </span>
                    <span>
                        <span class="material-symbols-outlined">schedule</span>
                        Registrado: <?= e($fechaRegistro) ?>
                    </span>
                    <span>
                        <span class="material-symbols-outlined">translate</span>
                        Idioma: <?= e($usuario['id_idioma_principal']) ?>
                    </span>
                </div>
            </div>
        </div>
    </section>
    <section class="profile-feature">
        <article class="feature-card accent">
            <span class="material-symbols-outlined">bolt</span>
            <div>
                <strong>Estado de la cuenta</strong>
                <p>Perfil activo y listo para explorar la tienda.</p>
            </div>
        </article>

        <article class="feature-card">
            <span class="material-symbols-outlined">sports_esports</span>
            <div>
                <strong>Biblioteca</strong>
                <p><?= (int)$data['totalBiblioteca'] ?> juegos disponibles.</p>
            </div>
        </article>

        <article class="feature-card">
            <span class="material-symbols-outlined">workspace_premium</span>
            <div>
                <strong>Progreso</strong>
                <p><?= (int)$data['totalLogros'] ?> logros desbloqueados.</p>
            </div>
        </article>
    </section>
    <section class="stats-grid">
        <article class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">sports_esports</span>
            </div>
            <div>
                <span class="stat-value"><?= (int)$data['totalBiblioteca'] ?></span>
                <span class="stat-label">Juegos en biblioteca</span>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">group</span>
            </div>
            <div>
                <span class="stat-value"><?= (int)$data['totalAmigos'] ?></span>
                <span class="stat-label">Amigos</span>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">workspace_premium</span>
            </div>
            <div>
                <span class="stat-value"><?= (int)$data['totalLogros'] ?></span>
                <span class="stat-label">Logros</span>
            </div>
        </article>
    </section>

    <section class="profile-grid">
        <article class="panel wide">
            <div class="panel-head">
                <h2>
                    <span class="material-symbols-outlined">grid_view</span>
                    Biblioteca reciente
                </h2>
                <a href="#" class="panel-link">Ver todo</a>
            </div>

            <?php if ($data['biblioteca']): ?>
                <div class="game-grid">
                    <?php foreach ($data['biblioteca'] as $juego): ?>
                        <?php
                            $precioFinal = $juego['precio'];
                            if ($juego['descuento'] !== null && (float)$juego['descuento'] > 0) {
                                $precioFinal = (float)$juego['precio'] * (1 - ((float)$juego['descuento'] / 100));
                            }
                        ?>
                        <article class="game-card">
                            <div class="game-thumb">
                                <?= e(mb_substr($juego['nombre_juego'], 0, 1, 'UTF-8')) ?>
                            </div>

                            <div class="game-content">
                                <h3><?= e($juego['nombre_juego']) ?></h3>
                                <p><?= e($juego['desarrollador']) ?></p>

                                <div class="game-meta">
                                    <span>
                                        <span class="material-symbols-outlined">local_fire_department</span>
                                        <?= $juego['descuento'] ? e($juego['descuento']) . '% dto.' : 'Sin descuento' ?>
                                    </span>
                                    <span><?= number_format((float)$precioFinal, 2, ',', '.') ?> €</span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <p>Todavía no hay juegos en la biblioteca.</p>
                </div>
            <?php endif; ?>
        </article>

        <article class="panel">
            <div class="panel-head">
                <h2>
                    <span class="material-symbols-outlined">group</span>
                    Amigos
                </h2>
                <a href="#" class="panel-link">Ver todo</a>
            </div>

            <?php if ($data['amigos']): ?>
                <ul class="simple-list">
                    <?php foreach ($data['amigos'] as $amigo): ?>
                        <li>
                            <span class="dot"></span>
                            <?= e($amigo['amigo']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state small">
                    <span class="material-symbols-outlined">sentiment_dissatisfied</span>
                    <p>Todavía no tienes amigos aceptados.</p>
                </div>
            <?php endif; ?>
        </article>

        <article class="panel full">
            <div class="panel-head">
                <h2>
                    <span class="material-symbols-outlined">verified</span>
                    Logros recientes
                </h2>
                <a href="#" class="panel-link">Ver todo</a>
            </div>

            <?php if ($data['logros']): ?>
                <div class="achievement-list">
                    <?php foreach ($data['logros'] as $logro): ?>
                        <div class="achievement-item">
                            <div class="achievement-icon">
                                <span class="material-symbols-outlined">military_tech</span>
                            </div>

                            <div class="achievement-body">
                                <strong><?= e($logro['nombre_logro']) ?></strong>
                                <span><?= e($logro['nombre_juego']) ?></span>
                            </div>

                            <small><?= e(date('d/m/Y H:i', strtotime($logro['fecha_obtencion']))) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <span class="material-symbols-outlined">lock</span>
                    <p>Todavía no has desbloqueado logros.</p>
                </div>
            <?php endif; ?>
        </article>
    </section>
</main>

</body>
</html>