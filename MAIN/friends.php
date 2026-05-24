<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>
<?php require_once '../BBDD/amigos_queries.php'; ?>
<?php require_once '../GENERAL/auth_guard.php'; ?>
<?php
$idSesion = (int) $_SESSION['id_usuario'];
$nicknameSesion = (string) $_SESSION['nickname'];

$codigoAmigos = getFriendsCode($BBDD, $idSesion, $nicknameSesion);
$amigosAceptados = getAcceptedFriends($BBDD, $idSesion, $nicknameSesion);

$mensaje = $_GET['message'] ?? null;
$error = $_GET['error'] ?? null;

$usuariosResultado = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_codigo'])) {
    $codigo = (int) trim($_POST['codigo'] ?? 0);
    if ($codigo > 0) {
        $usuario = getUserByFriendsCode($BBDD, $codigo);
        if ($usuario) {
            $usuariosResultado = [$usuario];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_nickname'])) {
    $texto = trim($_POST['nickname'] ?? '');
    if (strlen($texto) >= 2) {
        $usuariosResultado = searchUsersByNickname($BBDD, $texto);
    }
}
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>
<link rel="stylesheet" href="../CSS/friends.css">
<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<div class="friend-page">
    <div class="friend-header">
        <div>
            <h1>Añadir amigo</h1>
        </div>
    </div>

    <div class="friend-layout">
        <aside class="friend-sidebar">
            <div class="sidebar-links">
                <a href="user-friends.php" class="chip">
                    <span class="material-symbols-outlined">group</span>
                    Tus amigos
                </a>
                <a href="friends.php" class="chip active">
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
            <?php if (!empty($mensaje)): ?>
                <div class="empty-state" style="border-color: rgba(40, 167, 69, 0.35); background: rgba(40, 167, 69, 0.08); color: #d7f7e2; margin-bottom: 16px;">
                    <?= e($mensaje) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="empty-state" style="border-color: rgba(220, 53, 69, 0.35); background: rgba(220, 53, 69, 0.08); color: #ffe1e4; margin-bottom: 16px;">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-block">
                <div class="form-group">
                    <label>Tu código de amigo</label>
                    <input type="text" value="<?= e($codigoAmigos) ?>" readonly>
                    <small>Comparte este código para que otros te añadan.</small>
                </div>

                <div class="form-group">
                    <label for="codigo">Buscar por código de amigo</label>
                    <form method="POST" class="form-row">
                        <input type="text" id="codigo" name="codigo" placeholder="Introduce código">
                        <button type="submit" name="buscar_codigo" value="1" class="btn btn-primary">Buscar</button>
                    </form>
                </div>

                <div class="form-group">
                    <label for="nickname">Buscar por nombre de perfil</label>
                    <form method="POST" class="form-row">
                        <input type="text" id="nickname" name="nickname" placeholder="Introduce nombre de perfil">
                        <button type="submit" name="buscar_nickname" value="1" class="btn btn-primary">Buscar</button>
                    </form>
                </div>
            </div>

            <?php if (!empty($usuariosResultado)): ?>
                <div class="friend-grid">
                    <?php foreach ($usuariosResultado as $usuario): ?>
                        <?php
                        $avatarData = getProfileAvatarData($usuario['nickname']);
                        $avatarPath = $avatarData['avatarPath'];
                        $initial = $avatarData['initial'];
                        ?>
                        <article class="friend-card">
                            <a class="friend-card-link" href="../MAIN/profile.php?user=<?= e($usuario['nickname']) ?>">
                                <div class="friend-avatar" <?php if ($avatarPath): ?>style="background-image: url('<?= e($avatarPath) ?>'); background-size: cover; background-position: center;"<?php endif; ?> >
                                    <?php if (!$avatarPath): ?>
                                        <?= e($initial) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="friend-card-divider"></div>
                                <div class="friend-details">
                                    <strong><?= e($usuario['nombre_usuario']) ?></strong>
                                    <small>@<?= e($usuario['nickname']) ?></small>
                                    <div class="friend-extra"><?= e($usuario['descripcion'] ?? 'Sin descripción') ?></div>
                                </div>
                            </a>
                            <div class="friend-actions">
                                <form method="POST" action="../AJAX/amigos.php">
                                    <input type="hidden" name="action" value="add_friend">
                                    <input type="hidden" name="id_amigo" value="<?= (int) $usuario['id_usuario'] ?>">
                                    <input type="hidden" name="nick_amigo" value="<?= e($usuario['nickname']) ?>">
                                    <button type="submit" class="btn btn-primary">Enviar solicitud</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>
