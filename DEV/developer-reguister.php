<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../GENERAL/auth_guard.php';

$nickname = $_SESSION['nickname'] ?? '';
$message = '';
$error = '';

if ($nickname === '') {
    header('Location: ../AUTH/login.php');
    exit;
}

$stmtDeveloper = $BBDD->prepare("SELECT id_desarrollador, nombre_desarrollador FROM Desarrollador WHERE nickname = :nickname LIMIT 1");
$stmtDeveloper->execute([':nickname' => $nickname]);
$existingDeveloper = $stmtDeveloper->fetch(PDO::FETCH_ASSOC);

if ($existingDeveloper) {
    header('Location: settings-game.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $developerName = trim((string) ($_POST['developer_name'] ?? ''));

    if ($developerName === '') {
        $error = 'Debes indicar el nombre de tu perfil de desarrollador.';
    } else {
        $stmtInsert = $BBDD->prepare("INSERT INTO Desarrollador (nombre_desarrollador, nickname) VALUES (:nombre_desarrollador, :nickname)");
        $stmtInsert->execute([
            ':nombre_desarrollador' => $developerName,
            ':nickname' => $nickname,
        ]);

        header('Location: settings-game.php');
        exit;
    }
}

require_once '../GENERAL/[html_START - head_START].php';
?>
<link rel="stylesheet" href="../CSS/settings-game.css">
<?php
require_once '../GENERAL/[head_END - body_START - header - main_START].php';
?>

<div class="site-main__shell developer-panel-page" style="max-width: 900px; margin: 0 auto;">
    <header class="developer-panel__hero">
        <div>
            <h1>Registro de Desarrollador</h1>
            <p>Tu cuenta aún no tiene un perfil de desarrollador. Crea uno para poder administrar tus juegos.</p>
        </div>
    </header>

    <main class="developer-panel__content">
        <?php if ($message): ?>
            <div class="alert-custom alert-success"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-custom alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <section class="panel-card">
            <div class="panel-card__header">
                <h3 class="panel-card__title">Crear perfil de desarrollador</h3>
            </div>

            <form method="post" class="settings-game-form" id="developerRegisterForm">
                <div class="form-field">
                    <label class="form-label" for="nickname">Nickname</label>
                    <input type="text" id="nickname" class="form-control" value="<?= e($nickname) ?>" readonly>
                </div>

                <div class="form-field">
                    <label class="form-label" for="developer_name">Nombre del desarrollador</label>
                    <input type="text" id="developer_name" name="developer_name" class="form-control" placeholder="Ej. Estudio Lotus" value="<?= e($_POST['developer_name'] ?? '') ?>" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary btn-md">
                        <span class="material-symbols-outlined">save</span>
                        Crear perfil
                    </button>
                    <a href="../MAIN/index.html" class="btn-secondary btn-md">
                        <span class="material-symbols-outlined icon-cancel">cancel</span>
                        Volver
                    </a>
                </div>
            </form>
        </section>
    </main>
</div>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>
