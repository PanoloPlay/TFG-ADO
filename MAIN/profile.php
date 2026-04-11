<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>
<?php
require_once '../GENERAL/avatar_helpers.php';
require_once "../GENERAL/auth_guard.php";
require_once "../BBDD/profile_queries.php";

$idSesion = (int)($_SESSION['id_usuario'] ?? 0);
$nicknameSesion = $_SESSION['nickname'] ?? '';
$nicknameUrl = trim($_GET['usuario'] ?? '');

$perfilNoEncontrado = false;
$perfilPrivado = false;
$puedeVerContenido = false;

$data = [
    'usuario' => null,
    'totalBiblioteca' => 0,
    'totalAmigos' => 0,
    'totalLogros' => 0,
    'biblioteca' => [],
    'amigos' => [],
    'logros' => []
];

if ($nicknameUrl !== '') {
    $usuarioPerfil = getProfileUserByNickname($BBDD, $nicknameUrl);
} else {
    $usuarioPerfil = getProfileUserByIdAndNickname($BBDD, $idSesion, $nicknameSesion);
}

if (!$usuarioPerfil) {
    $perfilNoEncontrado = true;
} else {
    $idPerfil = (int)$usuarioPerfil['id_usuario'];
    $visibilidad = $usuarioPerfil['visibilidad'] ?? 'publico';

    if ($idSesion === $idPerfil) {
        $puedeVerContenido = true;
    } elseif ($visibilidad === 'publico') {
        $puedeVerContenido = true;
    } elseif ($visibilidad === 'privado') {
        $puedeVerContenido = false;
    } elseif ($visibilidad === 'solo_amigos') {
        $puedeVerContenido = sonAmigos($BBDD, $idSesion, $idPerfil);
    } elseif ($visibilidad === 'amigos_de_amigos') {
        $puedeVerContenido = sonAmigos($BBDD, $idSesion, $idPerfil) || sonAmigosDeAmigos($BBDD, $idSesion, $idPerfil);
    }

    if ($puedeVerContenido) {
        $data = getProfilePageData($BBDD, $idPerfil, $usuarioPerfil['nickname']);
    } else {
        $data['usuario'] = $usuarioPerfil;
        $perfilPrivado = true;
    }
}

$usuario = $data['usuario'] ?? null;

if (!$perfilNoEncontrado && $usuario) {
    $fechaRegistro = !empty($usuario['fecha_registro'])
        ? date('d/m/Y H:i', strtotime($usuario['fecha_registro']))
        : 'No disponible';

    $avatarData = getProfileAvatarData($usuario['nickname']);
    $initial = $avatarData['initial'];
    $avatarPath = $avatarData['avatarPath'];
    $avatarClass = $avatarData['avatarClass'];
}
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<link rel="stylesheet" href="../CSS/auth.css">
<link rel="stylesheet" href="../CSS/profile.css">

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<?php if ($perfilNoEncontrado): ?>

    <section class="profile-hero">
        <div class="empty-state">
            <span class="material-symbols-outlined">person_off</span>
            <p>No se ha podido encontrar este usuario.</p>
        </div>
    </section>

<?php else: ?>

    <section class="profile-hero">
        <div class="profile-cover"></div>

        <div class="profile-summary">
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

            <div class="profile-main">
                <div class="profile-data">
                    <div class="profile-headline">
                        <h1><?= e($usuario['nombre_usuario']) ?></h1>
                    </div>
                    <p class="profile-subtitle"><?= e($usuario['nickname']) ?></p>

                <?php if ($perfilPrivado): ?>
                    <p class="profile-bio private">
                        Perfil privado.
                    </p>
                </div>
                <?php else: ?>
                    <p class="profile-bio">
                        <?= e($usuario['descripcion'] ?: 'Sin descripción todavía.') ?>
                    </p>
                </div>
                    <div class="profile-badges">
                        <span>
                            <span class="material-symbols-outlined">mail</span>
                            <?= e($usuario['correo']) ?>
                        </span>
                        <div class="other-badges">
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
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if ($perfilPrivado): ?>

        <section class="profile-grid">
            <article class="panel full">
                <div class="panel-head">
                    <h2>
                        <span class="material-symbols-outlined">lock</span>
                        Contenido privado
                    </h2>
                </div>

                <div class="empty-state">
                    <span class="material-symbols-outlined">lock</span>
                    <p>Este perfil es privado.</p>
                </div>
            </article>
        </section>

    <?php else: ?>

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

                <?php if (!empty($data['biblioteca'])): ?>
                    <div class="game-grid">
                        <?php foreach ($data['biblioteca'] as $juego): ?>
                            <?php
                                $precioFinal = (float)$juego['precio'];
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
                                            <?= !empty($juego['descuento']) ? e($juego['descuento']) . '% dto.' : 'Sin descuento' ?>
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

                <?php if (!empty($data['amigos'])): ?>
                    <ul class="simple-list">
                        <?php foreach ($data['amigos'] as $amigo): ?>
                            <li>
                                <span class="dot"></span>
                                <a href="./profile.php?usuario=<?= e($amigo['amigo']) ?>" class="friend-link">
                                    <?= e($amigo['amigo']) ?>
                                </a>
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

                <?php if (!empty($data['logros'])): ?>
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

    <?php endif; ?>

<?php endif; ?>
</main>

</body>
</html>