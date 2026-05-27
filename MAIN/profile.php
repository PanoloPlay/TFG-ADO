<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>
<?php
require_once "../BBDD/profile_queries.php";
require_once "../DEPENDENCIES/IMG_helper.php";

$idSesion = (int)($_SESSION['id_usuario'] ?? 0);
$nicknameSesion = $_SESSION['nickname'] ?? '';
$nicknameUrl = trim($_GET['user'] ?? '');

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
        $puedeVerContenido = sonAmigos($BBDD, $idSesion, $idPerfil)
            || sonAmigosDeAmigos($BBDD, $idSesion, $idPerfil);
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
                <?php else: ?>

                    <?php
                        $descripcionCompleta = $usuario['descripcion'] ?? '';

                        $tieneDescripcionLarga = mb_strlen($descripcionCompleta, 'UTF-8') > 198;

                        $descripcionCorta = $tieneDescripcionLarga
                            ? mb_substr($descripcionCompleta, 0, 198, 'UTF-8') . '...'
                            : $descripcionCompleta;
                    ?>

                    <p class="profile-bio">
                        <?= e($descripcionCorta) ?>
                    </p>

                    <?php if ($tieneDescripcionLarga): ?>
                        <div
                            class="bio-more-btn"
                            id="bioMoreBtn"
                            data-full-description='<?= json_encode($descripcionCompleta, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) ?>'
                        >
                            <span>Ver más Información</span>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>

            <?php if (!$perfilPrivado): ?>
                <div class="profile-badges">
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

<section class="profile-grid profile-grid--layout">

    <!-- Biblioteca -->
    <article class="panel panel-library">
        <div class="panel-head">
            <h2>
                <span class="material-symbols-outlined">grid_view</span>
                Biblioteca
                <span class="panel-count">
                    <?= (int)$data['totalBiblioteca'] ?>
                </span>
            </h2>
        </div>

        <?php if (!empty($data['biblioteca'])): ?>

            <div class="game-grid">

                <?php foreach ($data['biblioteca'] as $juego): ?>

                    <?php
                        $imgUrl = getGameImageUrl((int)$juego['id_juego'], 'icon');
                    ?>

                    <article class="game-card">

                        <div
                            class="game-thumb"
                            style="
                                background-image: url('<?= e($imgUrl) ?>');
                                background-size: cover;
                                background-position: center;
                                background-repeat: no-repeat;
                            "
                        ></div>

                        <div class="game-content">
                            <h3><?= e($juego['nombre_juego']) ?></h3>

                            <p><?= e($juego['desarrollador']) ?></p>

                            <div class="game-meta">
                                <span>
                                    <span class="material-symbols-outlined">military_tech</span>
                                    <?= (int)$juego['total_logros'] ?> logros
                                </span>
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

    <!-- Amigos -->
    <article class="panel panel-friends">

        <div class="panel-head">
            <h2>
                <span class="material-symbols-outlined">group</span>
                Amigos

                <span class="panel-count">
                    <?= (int)$data['totalAmigos'] ?>
                </span>
            </h2>
        </div>

        <?php if (!empty($data['amigos'])): ?>

            <ul class="simple-list">

                <?php foreach ($data['amigos'] as $amigo): ?>

                    <?php
                        $avatarAmigo = getProfileAvatarData($amigo['amigo_nickname']);

                        $initialAmigo = $avatarAmigo['initial'];
                        $avatarPathAmigo = $avatarAmigo['avatarPath'];
                        $avatarClassAmigo = $avatarAmigo['avatarClass'];
                    ?>

                    <li>

                        <div
                            class="avatar-32px <?= e($avatarClassAmigo) ?>"
                            <?php if ($avatarPathAmigo): ?>
                                style="
                                    background-image: url('<?= e($avatarPathAmigo) ?>');
                                    background-size: cover;
                                    background-position: center;
                                "
                            <?php endif; ?>
                        >
                            <?php if (!$avatarPathAmigo): ?>
                                <?= e($initialAmigo) ?>
                            <?php endif; ?>
                        </div>

                        <a
                            href="./profile.php?user=<?= e($amigo['amigo_nickname']) ?>"
                            class="friend-link"
                        >
                            <?= e($amigo['amigo_apodo']) ?>
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
</section>

<?php endif; ?>
<?php endif; ?>

<?php if (!$perfilNoEncontrado && !$perfilPrivado): ?>

<div class="bio-modal" id="bioModal" aria-hidden="true">

    <div class="bio-modal-content">

        <button type="button" class="bio-modal-close" id="bioModalClose">
            <span class="material-symbols-outlined">close</span>
        </button>

        <h3>Información completa</h3>

        <div class="bio-modal-body scrollbar-theme">
            <p id="bioModalText"></p>
        </div>

    </div>

</div>

<?php endif; ?>

<script src="../JS/profile.js"></script>

<?php
require_once '../GENERAL/[main_END - footer].php';
require_once '../GENERAL/[Page_END].php';
?>