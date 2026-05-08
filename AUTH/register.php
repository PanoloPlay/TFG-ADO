<?php require_once "../GENERAL/[General_REQUIRES].php"; ?>
<?php require_once "../BBDD/register_queries.php"; ?>

<?php
if (!empty($_SESSION['id_usuario'])) {
    go("../MAIN/profile.php");
}

$error = "";
$idiomas = getIdiomas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $idiomaPrincipal = $_POST['id_idioma_principal'] ?? '';
    $claveAmigos = triggerClaveAmigos();

    if ($nickname === '' || $correo === '' || $password === '' || $password2 === '' || $idiomaPrincipal === '') {
        $error = "Rellena los campos obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo no es válido.";
    } elseif (strlen($nickname) < 3) {
        $error = "El nickname debe tener al menos 3 caracteres.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $password2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        if (UserRecordexists($nickname, $correo)) {
            $error = "Ese nickname o correo ya existe.";
        } else {
            $idiomaSecundario = null;
            $descripcion = null;
            $nombre = $nickname;
            $hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                insertUser(
                    $nombre,
                    $nickname,
                    $correo,
                    $hash,
                    $descripcion,
                    $idiomaPrincipal,
                    $idiomaSecundario,
                    $claveAmigos
                );
                go("login.php?registered=1");
            } catch (PDOException $e) {
                $error = "No se ha podido crear la cuenta.";
            }
        }
    }
}
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<link rel="stylesheet" href="../CSS/index.css">
<link rel="stylesheet" href="../CSS/auth.css">
<script src="../JS/auth.js" defer></script>

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<section class="auth-page">
    <section class="auth-card wide">
        <div class="auth-brand">
            <div class="brand-mark">
                <div class="wishlist-capsule"
                    style="background-image: url(../MEDIA/IMG/juegos/fallback/default.jpg);');">
                </div>
            </div>

            <div class="auth-title">
                <h1>Crear cuenta</h1>
                <p>Haz tu perfil y empieza a probar la web.</p>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="register-form" class="auth-form" autocomplete="off">
            <label class="field">
                <span>Nickname</span>
                <div class="input-wrap">
                    <span class="material-symbols-outlined input-icon">person</span>
                    <input type="text" name="nickname" id="nickname"
                        value="<?= e($_POST['nickname'] ?? '') ?>"
                        placeholder="Nickname"
                        required>
                    
                </div>
                <small class="field-msg">Mínimo 3 caracteres.</small>
            </label>

            <label class="field">
                <span>Correo</span>
                <div class="input-wrap">
                    <span class="material-symbols-outlined input-icon">mail</span>
                    <input type="email" name="correo" id="correo"
                        value="<?= e($_POST['correo'] ?? '') ?>"
                        placeholder="tu@email.com"
                        required>
                </div>
                <small class="field-msg"></small>
            </label>

            <label class="field">
                <span>Contraseña</span>
                <div class="input-wrap">
                    <span class="material-symbols-outlined input-icon">lock</span>
                    <input type="password" name="password"
                        placeholder="••••••••••••"
                        required autocomplete="new-password">
                </div>
                <small class="field-msg">Mínimo 6 caracteres.</small>
            </label>

            <label class="field">
                <span>Repetir contraseña</span>
                <div class="input-wrap">
                <span class="material-symbols-outlined input-icon">lock</span>
                <input type="password" name="password2"
                        placeholder="••••••••••••"
                        required autocomplete="new-password">
                </div>
            </label>

            <label class="field">
                <span>Idioma principal</span>
                <div class="input-wrap">
                    <span class="material-symbols-outlined input-icon">translate</span>
                    <select name="id_idioma_principal" required>
                        <option value="">Selecciona un idioma</option>
                        <?php foreach ($idiomas as $idioma): ?>
                            <option value="<?= e($idioma['id_idioma']) ?>" <?= (($_POST['id_idioma_principal'] ?? '') === $idioma['id_idioma']) ? 'selected' : '' ?>>
                                <?= e($idioma['idioma']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </label>

            <button type="submit" class="btn-primary full">Registrarse</button>
        </form>

        <p class="auth-footer">
            ¿Ya tienes cuenta? <a class="shine" href="login.php">Entrar</a>
        </p>
    </section>
</section>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>