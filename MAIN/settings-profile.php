<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/settings_profile_queries.php';

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nickname'])) {
    header('Location: ../AUTH/login.php');
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];
$nicknameSesion = (string) $_SESSION['nickname'];

$mensaje = '';
$tipoMensaje = 'success';
$usuario = null;
$idiomas = [];
$rutaAvatarSubido = null;

try {
    $idiomas = getSettingsProfileIdiomas($BBDD);
    $usuario = getSettingsProfileUser($BBDD, $idUsuario, $nicknameSesion);

    if (!$usuario) {
        throw new Exception('No se ha podido cargar la cuenta del usuario.');
    }

    $avatarData = getProfileAvatarData($usuario['nickname']);
    $initial = $avatarData['initial'];
    $avatarPath = $avatarData['avatarPath'];
    $avatarClass = $avatarData['avatarClass'];

    $safeNickname = basename($usuario['nickname'] ?? '');
    $avatarDefaultInitial = mb_strtoupper(mb_substr($safeNickname !== '' ? $safeNickname : 'U', 0, 1, 'UTF-8'), 'UTF-8');
    $avatarSeed = crc32($safeNickname !== '' ? $safeNickname : 'U');
    $avatarDefaultClass = 'avatar-' . (($avatarSeed % 6) + 1);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $visibilidad = $_POST['visibilidad'] ?? 'publico';
        $id_idioma_principal = trim($_POST['id_idioma_principal'] ?? '');
        $id_idioma_secundario = trim($_POST['id_idioma_secundario'] ?? '');
        $password_actual = $_POST['password_actual'] ?? '';
        $password_nueva = $_POST['password_nueva'] ?? '';
        $password_repetida = $_POST['password_repetida'] ?? '';
        $borrarAvatar = (($_POST['borrar_avatar'] ?? '0') === '1');

        if ($nombre_usuario === '') {
            throw new Exception('El nombre de usuario no puede estar vacío.');
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El correo electrónico no es válido.');
        }

        $visibilidadesValidas = ['publico', 'privado', 'solo_amigos', 'amigos_de_amigos'];
        if (!in_array($visibilidad, $visibilidadesValidas, true)) {
            throw new Exception('La visibilidad seleccionada no es válida.');
        }

        if ($id_idioma_principal === '') {
            throw new Exception('Debes seleccionar un idioma principal.');
        }

        if ($id_idioma_secundario !== '' && $id_idioma_secundario === $id_idioma_principal) {
            throw new Exception('El idioma secundario no puede ser igual al principal.');
        }

        $BBDD->beginTransaction();

        updateSettingsProfileBase(
            $BBDD,
            $nombre_usuario,
            $correo,
            ($descripcion !== '' ? $descripcion : null),
            $visibilidad,
            $id_idioma_principal,
            ($id_idioma_secundario !== '' ? $id_idioma_secundario : null),
            $idUsuario,
            $nicknameSesion
        );

        $passwordCambiada = false;

        if ($password_actual !== '' || $password_nueva !== '' || $password_repetida !== '') {
            if ($password_actual === '' || $password_nueva === '' || $password_repetida === '') {
                throw new Exception('Para cambiar la contraseña debes completar los 3 campos.');
            }

            if ($password_nueva !== $password_repetida) {
                throw new Exception('Las nuevas contraseñas no coinciden.');
            }

            if (mb_strlen($password_nueva) < 6) {
                throw new Exception('La nueva contraseña debe tener al menos 6 caracteres.');
            }

            if (!password_verify($password_actual, $usuario['clave_acceso'])) {
                throw new Exception('La contraseña actual no es correcta.');
            }

            $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            updateSettingsProfilePassword($BBDD, $hash, $idUsuario, $nicknameSesion);
            $passwordCambiada = true;
        }

        $nicknameAvatarSeguro = preg_replace('/[^a-zA-Z0-9_-]/', '', $usuario['nickname']);
        if ($nicknameAvatarSeguro === '') {
            throw new Exception('El nickname no es válido para guardar el avatar.');
        }

        $directorioBase = __DIR__ . '/../IMG/usuarios';

        if (!is_dir($directorioBase) && !mkdir($directorioBase, 0775, true) && !is_dir($directorioBase)) {
            throw new Exception('No se ha podido crear la carpeta de avatares.');
        }

        if ($borrarAvatar) {
            foreach (glob($directorioBase . '/' . $nicknameAvatarSeguro . '.*') as $archivoAntiguo) {
                if (is_file($archivoAntiguo)) {
                    unlink($archivoAntiguo);
                }
            }
        }

        if (!empty($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $avatar = $_FILES['avatar'];

            if ($avatar['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error al subir la imagen del avatar.');
            }

            if ($avatar['size'] > 5 * 1024 * 1024) {
                throw new Exception('La imagen del avatar no puede superar 5 MB.');
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($avatar['tmp_name']);

            $extensionesPermitidas = [
                'image/png'  => 'png',
                'image/jpeg' => 'jpg',
            ];

            if (!isset($extensionesPermitidas[$mime])) {
                throw new Exception('Solo se permiten imágenes PNG, JPG y JPEG.');
            }

            foreach (glob($directorioBase . '/' . $nicknameAvatarSeguro . '.*') as $archivoAntiguo) {
                if (is_file($archivoAntiguo)) {
                    unlink($archivoAntiguo);
                }
            }

            $ext = $extensionesPermitidas[$mime];
            $rutaFinal = $directorioBase . '/' . $nicknameAvatarSeguro . '.' . $ext;

            if (!move_uploaded_file($avatar['tmp_name'], $rutaFinal)) {
                throw new Exception('No se ha podido guardar la imagen del avatar.');
            }

            $rutaAvatarSubido = $rutaFinal;
        }

        $BBDD->commit();

        $mensaje = $passwordCambiada
            ? 'Perfil, avatar y contraseña actualizados correctamente.'
            : 'Perfil y avatar actualizados correctamente.';

        $tipoMensaje = 'success';

        $usuario = getSettingsProfileUser($BBDD, $idUsuario, $nicknameSesion);
        $avatarData = getProfileAvatarData($usuario['nickname']);
        $initial = $avatarData['initial'];
        $avatarPath = $avatarData['avatarPath'];
        $avatarClass = $avatarData['avatarClass'];
    }
} catch (PDOException $e) {
    if (isset($BBDD) && $BBDD->inTransaction()) {
        $BBDD->rollBack();
    }

    if ($rutaAvatarSubido && is_file($rutaAvatarSubido)) {
        unlink($rutaAvatarSubido);
    }

    if (($e->getCode() ?? '') === '23000') {
        $mensaje = 'No se ha podido guardar. El correo ya está en uso o algún dato no cumple las restricciones.';
    } else {
        $mensaje = 'Error de base de datos al guardar la configuración.';
    }
    $tipoMensaje = 'danger';
} catch (Exception $e) {
    if (isset($BBDD) && $BBDD->inTransaction()) {
        $BBDD->rollBack();
    }

    if ($rutaAvatarSubido && is_file($rutaAvatarSubido)) {
        unlink($rutaAvatarSubido);
    }

    $mensaje = $e->getMessage();
    $tipoMensaje = 'danger';
}

$inicial = mb_strtoupper(mb_substr($usuario['nickname'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8');
$fechaRegistro = !empty($usuario['fecha_registro'])
    ? date('d/m/Y H:i', strtotime($usuario['fecha_registro']))
    : 'No disponible';

require_once '../GENERAL/[html_START - head_START].php';
?>
<link rel="stylesheet" href="../CSS/settings-profile.css">
<?php
require_once '../GENERAL/[head_END - body_START - header - main_START].php';
?>

<div class="site-main__shell settings-page"
     data-avatar-default-initial="<?= e($avatarDefaultInitial) ?>"
     data-avatar-default-class="<?= e($avatarDefaultClass) ?>">
    <div class="settings-hero">
        <div>
            <h1>Configuración de la cuenta</h1>
            <p>Edita tu perfil, privacidad e idiomas.</p>
        </div>

        <a href="../MAIN/profile.php?usuario=<?php echo e($usuario['nickname']); ?>" class="chip chip-soft">
            Volver al perfil
        </a>
    </div>

    <div class="settings-layout">
        <aside class="settings-sidebar">
            <div class="profile-badge">
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
                <div class="profile-name">
                    <strong><?php echo e($usuario['nombre_usuario']); ?></strong>
                    <small><?php echo e($usuario['nickname']); ?></small>
                </div>
            </div>

            <div class="sidebar-meta">
                <div class="meta-item">
                    <span>Registro</span>
                    <strong><?php echo e($fechaRegistro); ?></strong>
                </div>

                <div class="meta-item">
                    <span>Visibilidad</span>
                    <strong>
                        <?php echo (($usuario['visibilidad'] ?? '') === 'publico') ? 'Público' : ''; ?>
                        <?php echo (($usuario['visibilidad'] ?? '') === 'privado') ? 'Privado' : ''; ?>
                        <?php echo (($usuario['visibilidad'] ?? '') === 'solo_amigos') ? 'Solo amigos' : ''; ?>
                        <?php echo (($usuario['visibilidad'] ?? '') === 'amigos_de_amigos') ? 'Amigos de amigos' : ''; ?>
                    </strong>
                </div>

                <div class="meta-item">
                    <span>Idioma principal</span>
                    <strong><?php echo e($usuario['id_idioma_principal']); ?></strong>
                </div>
            </div>

            <div class="sidebar-links">
                <a href="#seccion-avatar" class="chip active">
                    <span class="material-symbols-outlined">account_circle</span>
                    Avatar
                </a>
                <a href="#seccion-datos" class="chip">
                    <span class="material-symbols-outlined">article_person</span>
                    Datos
                </a>
                <a href="#seccion-privacidad" class="chip">
                    <span class="material-symbols-outlined">security</span>
                    Privacidad e Idioma
                </a>
                <a href="#seccion-password" class="chip">
                    <span class="material-symbols-outlined">key</span>
                    Seguridad y acceso
                </a>
            </div>
        </aside>

        <div class="settings-panel <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'no-anim' : ''; ?>">
            <form method="POST" action="" enctype="multipart/form-data">

                <section class="section" id="seccion-avatar">
                    <div class="section-title">Avatar</div>

                    <div class="grid-2">
                        <div class="field field-readonly">
                            <label>Vista previa actual</label>

                            <div style="margin-top: 8px;">
                                <div
                                    id="avatarPreviewBox-184px"
                                    class="profile-avatar <?= e($avatarClass) ?>"
                                    data-initial="<?= e($initial) ?>"
                                    <?php if ($avatarPath): ?>
                                        style="background-image: url('<?= e($avatarPath) ?>'); background-size: cover; background-position: center;"
                                    <?php endif; ?>
                                >
                                    <?php if (!$avatarPath): ?>
                                        <span><?= e($initial) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="hint">184px</div>
                            </div>

                            <div style="margin-top: 8px;">
                                <div
                                    id="avatarPreviewBox-64px"
                                    class="profile-avatar <?= e($avatarClass) ?>"
                                    data-initial="<?= e($initial) ?>"
                                    <?php if ($avatarPath): ?>
                                        style="background-image: url('<?= e($avatarPath) ?>'); background-size: cover; background-position: center;"
                                    <?php endif; ?>
                                >
                                    <?php if (!$avatarPath): ?>
                                        <span><?= e($initial) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="hint">64px</div>
                            </div>

                            <div style="margin-top: 8px;">
                                <div
                                    id="avatarPreviewBox-32px"
                                    class="profile-avatar <?= e($avatarClass) ?>"
                                    data-initial="<?= e($initial) ?>"
                                    <?php if ($avatarPath): ?>
                                        style="background-image: url('<?= e($avatarPath) ?>'); background-size: cover; background-position: center;"
                                    <?php endif; ?>
                                >
                                    <?php if (!$avatarPath): ?>
                                        <span><?= e($initial) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="hint">32px</div>
                            </div>
                        </div>

                        <div class="field">
                            <label for="avatar">Subir imagen de avatar</label>
                            <input
                                type="file"
                                id="avatar"
                                name="avatar"
                                accept=".png,.jpg,.jpeg,image/png,image/jpeg"
                            >
                            <div class="hint">
                                La imagen debe ser cuadrada y tener al menos 184 píxeles por lado. Formatos permitidos: PNG, JPG y JPEG.
                            </div>

                            <input type="hidden" name="borrar_avatar" id="borrarAvatarInput" value="0">

                            <?php if ($avatarPath): ?>
                                <button
                                    type="button"
                                    id="deleteAvatarBtn"
                                    class="chip delet-avatar"
                                >
                                    <span class="material-symbols-outlined">delete</span>
                                    Eliminar imagen
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div id="cropper-wrapper" class="cropper-wrapper" style="display: none;">
                        <div class="cropper-header">Ajusta tu avatar</div>
                        <div class="cropper-body">
                            <img id="avatarCropImage" src="" alt="Recortar avatar">
                        </div>
                        <div class="cropper-footer">
                            <button type="button" class="chip" id="avatarCropCancel">Cancelar</button>
                            <button type="button" class="chip chip-soft" id="avatarCropApply">Usar recorte</button>
                        </div>
                    </div>
                </section>

                <section class="section" id="seccion-datos">
                    <div class="section-title">Datos del perfil</div>

                    <div class="grid-2">
                        <div class="field">
                            <label for="nombre_usuario">Nombre de usuario</label>
                            <input
                                type="text"
                                id="nombre_usuario"
                                name="nombre_usuario"
                                value="<?php echo e($usuario['nombre_usuario']); ?>"
                                required
                            >
                        </div>

                        <div class="field field-readonly">
                            <label for="nickname">Nickname</label>
                            <input type="text" id="nickname" value="<?php echo e($usuario['nickname']); ?>" readonly>
                            <div class="hint">Se mantiene fijo y no se podrá cambiar.</div>
                        </div>
                    </div>

                    <div class="field">
                        <label for="descripcion">Descripción</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="5"
                            placeholder="Escribe una breve descripción..."
                        ><?php echo e($usuario['descripcion'] ?? ''); ?></textarea>
                    </div>
                </section>

                <section class="section" id="seccion-privacidad">
                    <div class="section-title">Privacidad e idiomas</div>

                    <div class="grid-3">
                        <div class="field">
                            <label for="visibilidad">Visibilidad</label>
                            <select id="visibilidad" name="visibilidad" required>
                                <option value="publico" <?php echo (($usuario['visibilidad'] ?? '') === 'publico') ? 'selected' : ''; ?>>Público</option>
                                <option value="privado" <?php echo (($usuario['visibilidad'] ?? '') === 'privado') ? 'selected' : ''; ?>>Privado</option>
                                <option value="solo_amigos" <?php echo (($usuario['visibilidad'] ?? '') === 'solo_amigos') ? 'selected' : ''; ?>>Solo amigos</option>
                                <option value="amigos_de_amigos" <?php echo (($usuario['visibilidad'] ?? '') === 'amigos_de_amigos') ? 'selected' : ''; ?>>Amigos de amigos</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="id_idioma_principal">Idioma principal</label>
                            <select id="id_idioma_principal" name="id_idioma_principal" required>
                                <option value="">Selecciona un idioma</option>
                                <?php foreach ($idiomas as $idioma): ?>
                                    <option value="<?php echo e($idioma['id_idioma']); ?>"
                                        <?php echo (($usuario['id_idioma_principal'] ?? '') === $idioma['id_idioma']) ? 'selected' : ''; ?>>
                                        <?php echo e($idioma['idioma']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="id_idioma_secundario">Idioma secundario</label>
                            <select id="id_idioma_secundario" name="id_idioma_secundario">
                                <option value="">Ninguno</option>
                                <?php foreach ($idiomas as $idioma): ?>
                                    <option value="<?php echo e($idioma['id_idioma']); ?>"
                                        <?php echo (($usuario['id_idioma_secundario'] ?? '') === $idioma['id_idioma']) ? 'selected' : ''; ?>>
                                        <?php echo e($idioma['idioma']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="section" id="seccion-password">
                    <div class="section-title">Cambiar Correo</div>

                    <div class="field">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" value="<?php echo e($usuario['correo']); ?>" required>
                    </div>

                    <div class="section-title">Cambiar contraseña</div>

                    <div class="grid-3">
                        <div class="field">
                            <label for="password_actual">Contraseña actual</label>
                            <input
                                type="password"
                                id="password_actual"
                                name="password_actual"
                                placeholder="Solo si vas a cambiarla"
                            >
                        </div>

                        <div class="field">
                            <label for="password_nueva">Nueva contraseña</label>
                            <input
                                type="password"
                                id="password_nueva"
                                name="password_nueva"
                                placeholder="Mínimo 6 caracteres"
                            >
                        </div>

                        <div class="field">
                            <label for="password_repetida">Repetir nueva contraseña</label>
                            <input
                                type="password"
                                id="password_repetida"
                                name="password_repetida"
                                placeholder="Repite la nueva contraseña"
                            >
                        </div>
                    </div>
                </section>

                <div class="form-actions">
                    <button type="submit" class="chip chip-soft">Guardar cambios</button>
                    <a href="../MAIN/settings-profile.php" class="chip">Cancelar</a>
                </div>
            </form>

            <?php if ($mensaje !== ''): ?>
                <div class="alert-custom alert-<?php echo e($tipoMensaje); ?>">
                    <?php echo e($mensaje); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="avatarCropModal" tabindex="-1" aria-labelledby="avatarCropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarCropModalLabel">Recortar avatar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="text-center">
                    <img id="avatarCropImage" alt="Imagen para recortar" style="max-width: 100%; display: block;">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="avatarCropApply">Usar recorte</button>
            </div>
        </div>
    </div>
</div>

<script src="../JS/settings-profile-tabs.js"></script>
<script src="../JS/settings-alerts.js"></script>
<script src="../JS/settings-profile-avatar.js"></script>

<?php
require_once '../GENERAL/[main_END - footer].php';
require_once '../GENERAL/[Page_END].php';
?>