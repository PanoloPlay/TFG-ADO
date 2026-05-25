<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/amigos_queries.php';
require_once '../GENERAL/auth_guard.php';

$idSesion = (int) $_SESSION['id_usuario'];
$nicknameSesion = (string) $_SESSION['nickname'];
$mensaje = $_GET['message'] ?? null;
$error = $_GET['error'] ?? null;

$enviadas = getSentFriendRequests($BBDD, $idSesion, $nicknameSesion);
$recibidas = getReceivedFriendRequests($BBDD, $idSesion, $nicknameSesion);
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>
<link rel="stylesheet" href="../CSS/friends.css">
<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<div class="friend-page">
    <div class="friend-header">
        <div>
            <h1>Solicitudes pendientes</h1>
            <p>Aquí verás las solicitudes que has enviado y las que has recibido, con acciones para aceptar o rechazar.</p>
        </div>
    </div>

    <div class="friend-layout">
        <aside class="friend-sidebar">
            <div class="sidebar-links">
                <a href="user-friends.php" class="chip">
                    <span class="material-symbols-outlined">group</span>
                    Tus amigos
                </a>
                <a href="friends.php" class="chip">
                    <span class="material-symbols-outlined">person_add</span>
                    Añadir amigo
                </a>
                <a href="pending-requests.php" class="chip active">
                    <span class="material-symbols-outlined">pending</span>
                    Solicitudes pendientes
                </a>
            </div>
        </aside>

        <main class="friend-main">
            <?php if ($mensaje): ?>
                <div class="empty-state" style="border-color: rgba(40, 167, 69, 0.35); background: rgba(40, 167, 69, 0.08); color: #d7f7e2;">
                    <?= e($mensaje) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="empty-state" style="border-color: rgba(220, 53, 69, 0.35); background: rgba(220, 53, 69, 0.08); color: #ffe1e4;">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <section style="margin-bottom: 24px;">
                <h2>Solicitudes enviadas</h2>
                <?php if (!empty($enviadas)): ?>
                    <div class="friend-grid">
                        <?php foreach ($enviadas as $solicitud): ?>
                            <?php
                            $avatarData = getProfileAvatarData($solicitud['nickname']);
                            $avatarPath = $avatarData['avatarPath'];
                            $avatarClass = $avatarData['avatarClass'];
                            $initial = $avatarData['initial'];
                            ?>
                            <article class="friend-card">
                                <a class="friend-card-link" href="../MAIN/profile.php?user=<?= e($solicitud['nickname']) ?>">
                                    <div class="friend-avatar profile-avatar avatar-64px <?= e($avatarClass) ?>" <?php if ($avatarPath): ?>style="background-image: url('<?= e($avatarPath) ?>'); background-size: cover; background-position: center;"<?php endif; ?> >
                                        <?php if (!$avatarPath): ?>
                                            <?= e($initial) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="friend-card-divider"></div>
                                    <div class="friend-details">
                                        <strong><?= e($solicitud['nombre_usuario']) ?></strong>
                                        <small><?= e($solicitud['nickname']) ?></small>
                                        <div class="friend-extra">Solicitud enviada</div>
                                    </div>
                                </a>
                                <div class="friend-actions">
                                    <form method="POST" action="../AJAX/amigos.php" style="display: inline-block;">
                                        <input type="hidden" name="action" value="cancel_request">
                                        <input type="hidden" name="id_amistad" value="<?= (int) $solicitud['id_amistad'] ?>">
                                        <button type="submit" class="btn btn-danger">Cancelar</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No tienes solicitudes enviadas pendientes.</p>
                    </div>
                <?php endif; ?>
            </section>

            <section>
                <h2>Solicitudes recibidas</h2>
                <?php if (!empty($recibidas)): ?>
                    <div class="friend-grid">
                        <?php foreach ($recibidas as $solicitud): ?>
                            <?php
                            $avatarData = getProfileAvatarData($solicitud['nickname']);
                            $avatarPath = $avatarData['avatarPath'];
                            $avatarClass = $avatarData['avatarClass'];
                            $initial = $avatarData['initial'];
                            ?>
                            <article class="friend-card">
                                <a class="friend-card-link" href="../MAIN/profile.php?user=<?= e($solicitud['nickname']) ?>">
                                    <div class="friend-avatar profile-avatar avatar-64px <?= e($avatarClass) ?>" <?php if ($avatarPath): ?>style="background-image: url('<?= e($avatarPath) ?>'); background-size: cover; background-position: center;"<?php endif; ?> >
                                        <?php if (!$avatarPath): ?>
                                            <?= e($initial) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="friend-card-divider"></div>
                                    <div class="friend-details">
                                        <strong><?= e($solicitud['nombre_usuario']) ?></strong>
                                        <small><?= e($solicitud['nickname']) ?></small>
                                        <div class="friend-extra">Solicitud recibida</div>
                                    </div>
                                </a>
                                <div class="friend-actions">
                                    <form method="POST" action="../AJAX/amigos.php" style="display: inline-block;">
                                        <input type="hidden" name="action" value="accept_request">
                                        <input type="hidden" name="id_amistad" value="<?= (int) $solicitud['id_amistad'] ?>">
                                        <button type="submit" class="btn btn-success">Aceptar</button>
                                    </form>
                                    <form method="POST" action="../AJAX/amigos.php" style="display: inline-block;">
                                        <input type="hidden" name="action" value="reject_request">
                                        <input type="hidden" name="id_amistad" value="<?= (int) $solicitud['id_amistad'] ?>">
                                        <button type="submit" class="btn btn-danger">Rechazar</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No hay solicitudes recibidas pendientes.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>
