<?php require_once "../GENERAL/[General_REQUIRES].php"; ?>
<?php require_once "../BBDD/register_queries.php"; ?>

<?php

if (!empty($_SESSION['id_usuario'])) {
    go("../MAIN/profile.php");
}

$error = "";

// Idiomas disponibles
$idiomas = getIdiomas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_usuario'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idiomaPrincipal = $_POST['id_idioma_principal'] ?? '';
    $idiomaSecundario = $_POST['id_idioma_secundario'] ?? '';

    if ($nombre === '' || $nickname === '' || $correo === '' || $password === '' || $password2 === '' || $idiomaPrincipal === '') {
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
            if ($idiomaSecundario === '') {
                $idiomaSecundario = null;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                insertUser(
                    $nombre,
                    $nickname,
                    $correo,
                    $hash,
                    $descripcion,
                    $idiomaPrincipal,
                    $idiomaSecundario
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

        <section class="auth-card wide">
            <div class="auth-brand">
                <span class="brand-mark">TFG</span>
                <div>
                    <h1>Crear cuenta</h1>
                    <p>Haz tu perfil y empieza a probar la web.</p>
                </div>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="register-form" class="auth-form grid" autocomplete="off">
                <label>
                    Nombre
                    <input type="text" name="nombre_usuario" value="<?= e($_POST['nombre_usuario'] ?? '') ?>" required>
                </label>

                <label>
                    Nickname
                    <input type="text" name="nickname" id="nickname" value="<?= e($_POST['nickname'] ?? '') ?>" required>
                    <small id="nick-msg" class="field-msg"></small>
                </label>

                <label>
                    Correo
                    <input type="email" name="correo" id="correo" value="<?= e($_POST['correo'] ?? '') ?>" required>
                    <small id="mail-msg" class="field-msg"></small>
                </label>

                <label>
                    Contraseña
                    <input type="password" name="password" required>
                </label>

                <label>
                    Repetir contraseña
                    <input type="password" name="password2" required>
                </label>

                <label class="full">
                    Descripción
                    <textarea name="descripcion" rows="4" placeholder="Cuéntanos algo sobre ti..."><?= e($_POST['descripcion'] ?? '') ?></textarea>
                </label>

                <label>
                    Idioma principal
                    <select name="id_idioma_principal" required>
                        <option value="">Selecciona un idioma</option>
                        <?php foreach ($idiomas as $idioma): ?>
                            <option value="<?= e($idioma['id_idioma']) ?>" <?= (($_POST['id_idioma_principal'] ?? '') === $idioma['id_idioma']) ? 'selected' : '' ?>>
                                <?= e($idioma['idioma']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Idioma secundario
                    <select name="id_idioma_secundario">
                        <option value="">Ninguno</option>
                        <?php foreach ($idiomas as $idioma): ?>
                            <option value="<?= e($idioma['id_idioma']) ?>" <?= (($_POST['id_idioma_secundario'] ?? '') === $idioma['id_idioma']) ? 'selected' : '' ?>>
                                <?= e($idioma['idioma']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <button type="submit" class="btn-primary full">Registrarse</button>
            </form>

            <p class="auth-footer">
                ¿Ya tienes cuenta? <a class="shine" href="login.php">Entrar</a>
            </p>
        </section>
        
<?php require_once '../GENERAL/[main_END - footer].php'; ?>

<?php require_once '../GENERAL/[Page_END].php'; ?>