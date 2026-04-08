<?php
require_once "../GENERAL/[General_REQUIRES].php";
require_once "../BBDD/login_queries.php";

if (!empty($_SESSION['id_usuario'])) {
    go("../MAIN/profile.php");
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = "Rellena todos los campos.";
    } else {
         $user = getUserByLogin($login);
        if ($user && password_verify($password, $user['clave_acceso'])) {
            session_regenerate_id(true);

            $_SESSION['id_usuario'] = (int)$user['id_usuario'];
            $_SESSION['nickname'] = $user['nickname'];
            $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
            $_SESSION['correo'] = $user['correo'];

            go("../MAIN/profile.php");
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    }
}
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

    <link rel="stylesheet" href="../CSS/index.css">
    <link rel="stylesheet" href="../CSS/auth.css">

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

        <section class="auth-card">
            <div class="auth-brand">
                <span class="brand-mark">TFG</span>
                <div>
                    <h1>Iniciar sesión</h1>
                    <p>Accede a tu biblioteca, amigos y perfil.</p>
                </div>
            </div>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert success">Cuenta creada correctamente. Ya puedes entrar.</div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form" autocomplete="off">
                <label>
                    Nickname o correo
                    <input type="text" name="login" value="<?= e($_POST['login'] ?? '') ?>" required>
                </label>

                <label>
                    Contraseña
                    <input type="password" name="password" required>
                </label>

                <button type="submit" class="btn-primary">Entrar</button>
            </form>

            <p class="auth-footer">
                ¿No tienes cuenta? <a class="shine" href="register.php">Crear una</a>
            </p>
        </section>
        
<?php require_once '../GENERAL/[main_END - footer].php'; ?>

<?php require_once '../GENERAL/[Page_END].php'; ?>