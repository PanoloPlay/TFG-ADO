<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/amigos_queries.php';
require_once '../GENERAL/auth_guard.php';

$idSesion = (int) $_SESSION['id_usuario'];
$nicknameSesion = (string) $_SESSION['nickname'];
$amigos = getFriendUsers($BBDD, $idSesion, $nicknameSesion);
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>
<link rel="stylesheet" href="../CSS/friends.css">
<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<div class="friend-page">
    <div class="friend-header">
        <div>
            <h1>Tus amigos</h1>
        </div>
    </div>

    <div class="friend-layout">
        <aside class="friend-sidebar">
            <div class="sidebar-links">
                <a href="user-friends.php" class="chip active">
                    <span class="material-symbols-outlined">group</span>
                    Tus amigos
                </a>
                <a href="friends.php" class="chip">
                    <span class="material-symbols-outlined">person_add</span>
                    Añadir amigo
                </a>
                <a href="pending-requests.php" class="chip">
                    <span class="material-symbols-outlined">pending</span>
                    Solicitudes pendientes
                </a>
            </div>
        </aside>

        <main class="friend-main">
            <?php if (!empty($amigos)): ?>
                <div class="friend-grid">
                    <?php foreach ($amigos as $amigo): ?>
                        <?php
                        $avatarData = getProfileAvatarData($amigo['nickname']);
                        $avatarPath = $avatarData['avatarPath'];
                        $avatarClass = $avatarData['avatarClass'];
                        $initial = $avatarData['initial'];
                        ?>
                        <article class="friend-card">
                            <a class="friend-card-link" href="../MAIN/profile.php?user=<?= e($amigo['nickname']) ?>">
                                <div class="friend-avatar profile-avatar avatar-64px <?= e($avatarClass) ?>" <?php if ($avatarPath): ?>style="background-image: url('<?= e($avatarPath) ?>'); background-size: cover; background-position: center;"<?php endif; ?> >
                                    <?php if (!$avatarPath): ?>
                                        <?= e($initial) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="friend-card-divider"></div>
                                <div class="friend-details">
                                    <strong><?= e($amigo['nombre_usuario']) ?></strong>
                                    <small><?= e($amigo['nickname']) ?></small>
                                    <?php if (!empty($amigo['descripcion'])): ?>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No tienes amigos aceptados todavía. Usa "Añadir amigo" para comenzar.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>
