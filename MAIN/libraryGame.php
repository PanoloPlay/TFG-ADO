<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<script src="../JS/checkIfXExists.js" defer></script>
<script src="../JS/libraryGame.js" defer></script>

<style>
    .curSelected {
        background-color: #000000;
        color: white;
    }
</style>

<?php 
include_once '../GENERAL/auth_guard.php';
?>

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<input type="hidden" id="hdnSession" data-value="<?php echo $_SESSION['nickname']; ?>" />

<div id="error-section">
</div>

<div class="row hero-library-game" style="height: 82vh;">
    <side id="library-side" class="col-3" style="overflow-y: auto; height: 100%;">
        <div id="library-side-body">
        </div>
    </side>

    <div id="library-main" class="col-9" style="overflow-y: auto; height: 100%;">
        <div id="library-main-header">
        </div>
        <div id="library-main-body">
            <div id="library-main-all-games">
            </div>
            <div id="library-main-achievements-recent">
            </div>
            <div id="library-main-achievements-all">
            </div>
        </div>
    </div>
</div> 

<?php require_once '../GENERAL/[main_END - footer].php'; ?>

<?php require_once '../GENERAL/[Page_END].php'; ?>