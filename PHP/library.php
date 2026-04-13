<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>
<?php
require_once '../GENERAL/avatar_helpers.php';
require_once "../BBDD/profile_queries.php";

// Verificar que el usuario está autenticado
$idSesion = (int) ($_SESSION['id_usuario'] ?? 0);
$nicknameSesion = $_SESSION['nickname'] ?? '';

// Si no hay sesión válida, redirigir al loginy salir
if (empty($idSesion) || empty($nicknameSesion)) {
    header("Location: ../AUTH/login.php");
    exit;
}
// Obtener los datos del usuario para mostrar en el perfil
$usuarioPerfil = getProfileUserByIdAndNickname($BBDD, $idSesion, $nicknameSesion);

// Si no se encuentra el usuario, redirigir al login y salir
if (!$usuarioPerfil) {
    header("Location: ../AUTH/login.php");
    exit;
}

// Obtener todos los juegos de la biblioteca del usuario y las categorías disponibles para los filtros
$todosJuegos = getAllLibraryGames($BBDD, $idSesion, $nicknameSesion);
$todasCategorias = getAllCategories($BBDD);

// Inicialmente, se muestran todos los juegos sin aplicar ningún filtro
$juegosActuales = $todosJuegos;
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<link rel="stylesheet" href="../CSS/profile.css">
<link rel="stylesheet" href="../CSS/library.css">

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<section class="profile-topbar">
    <div class="profile-brand">
        <div class="brand-mark small">
            <span class="material-symbols-outlined">library_books</span>
        </div>
        <div>
            <strong>Mi Biblioteca</strong>
            <span><?= count($todosJuegos) ?> juegos</span>
        </div>
    </div>
</section>

<section class="site-main__shell">
    <article class="panel full">
        <div class="panel-head">
            <h2>
                <span class="material-symbols-outlined">sports_esports</span>
                Mis Juegos
            </h2>
        </div>

        <div class="filter-section">
            <div class="filter-header">
                <h3>Filtrar por categoría</h3>
                <button class="btn-clear-filters" id="btnLimpiarFiltros">
                    <span class="material-symbols-outlined">close</span>
                    Limpiar filtros
                </button>
            </div>
            <div class="filter-categories">
                <!-- Se generan las categorías disponibles para filtrar los juegos de la biblioteca -->
                <?php foreach ($todasCategorias as $categoria): ?>
                    <label class="checkbox-filter">
                        <input type="checkbox" class="categoria-checkbox" value="<?= (int) $categoria['id_categoria'] ?>"
                            data-categoria="<?= e($categoria['categoria']) ?>">
                        <span><?= e($categoria['categoria']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Se muestran los juegos de la biblioteca, inicialmente sin aplicar filtros -->
        <?php if (!empty($juegosActuales)): ?>
            <div class="game-grid game-grid-library">
                <?php foreach ($juegosActuales as $juego): ?>
                    <?php
                    $precioFinal = (float) $juego['precio'];
                    if ($juego['descuento'] !== null && (float) $juego['descuento'] > 0) {
                        $precioFinal = (float) $juego['precio'] * (1 - ((float) $juego['descuento'] / 100));
                    }
                    // Se muestra una descripción corta del juego, limitando a 150 caracteres
                    $descripcion = !empty($juego['descripcion'])
                        ? mb_substr($juego['descripcion'], 0, 150, 'UTF-8')
                        : 'Sin descripción';
                    if (strlen($juego['descripcion']) > 150) {
                        $descripcion .= '...';
                    }
                    // Se formatea la fecha de publicación del juego, mostrando "N/A" si no está disponible
                    $fecha = !empty($juego['fecha_publicacion'])
                        ? date('d/m/Y', strtotime($juego['fecha_publicacion']))
                        : 'N/A';
                    ?>
                    <!-- Tarjeta de juego que muestra la información básica del juego, como nombre, desarrollador, descripción corta, fecha de publicación, descuento y precio final -->
                    <article class="game-card game-card-library" data-juego-id="<?= (int) $juego['id_juego'] ?>">
                        <div class="game-thumb">
                            <?= e(mb_substr($juego['nombre_juego'], 0, 1, 'UTF-8')) ?>
                        </div>

                        <div class="game-content">
                            <!-- Se muestra el nombre del juego, el desarrollador, una descripción corta, la fecha de publicación, el descuento aplicado (si lo hay) y el precio final después de aplicar el descuento -->
                            <h3><?= e($juego['nombre_juego']) ?></h3>
                            <p class="game-developer"><?= e($juego['desarrollador']) ?></p>
                            <p class="game-description"><?= e($descripcion) ?></p>

                            <div class="game-meta">
                                <span class="game-date">
                                    <span class="material-symbols-outlined">event</span>
                                    <?= e($fecha) ?>
                                </span>
                                <span class="game-discount">
                                    <span class="material-symbols-outlined">local_fire_department</span>
                                    <?= !empty($juego['descuento']) ? e($juego['descuento']) . '% dto.' : 'Sin descuento' ?>
                                </span>
                                <span class="game-price">
                                    <?= number_format((float) $precioFinal, 2, ',', '.') ?> €
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="material-symbols-outlined">inventory_2</span>
                <p>No hay juegos en tu biblioteca o no coinciden con los filtros seleccionados.</p>
            </div>
        <?php endif; ?>
    </article>
</section>

<script src="../JS/library-filtro.js" defer></script>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>