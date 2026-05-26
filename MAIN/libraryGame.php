<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<link rel="stylesheet" href="../CSS/libraryGame.css">
<script src="../JS/checkIfXExists.js" defer></script>
<script src="../JS/libraryGame.js" defer></script>


<?php 
include_once '../GENERAL/auth_guard.php';
?>

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<input type="hidden" id="hdnSession" data-value="<?php echo $_SESSION['nickname']; ?>" />

<?php if (isset($_POST['name'])) { ?>
<input type="hidden" id="hdName" data-value="<?php echo $_POST['name']; ?>" />
<?php } ?>

<div id="error-section" style="display: none;">
    <section class="game-page">
        <div class="error-message">
            <span class="material-symbols-outlined">videogame_asset_off</span>
            <h2>Biblioteca vacia</h2>
            <p>No tienes juegos en tu libreria</p>
            <a class="btn" href="./">Volver al inicio</a>
        </div>
    </section>
</div>

<div class="row hero-library-game">
    <side id="library-side" class="col-3 library-side">
        <div id="library-side-body">
        </div>
    </side>

    <div id="library-main" class="col-9 library-main">
        <div id="library-main-header">
        </div>
        <div id="library-main-body">
            <div id="library-main-all-games">
            </div>
            <div id="library-main-achievements-all">
            </div>
            <div id="library-main-achievements-recent">
            </div>
        </div>
    </div>
</div> 

<?php require_once '../GENERAL/[main_END - footer].php'; ?>

<?php require_once '../GENERAL/[Page_END].php'; ?>