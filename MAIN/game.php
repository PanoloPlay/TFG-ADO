<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<script src="../JS/game.js" defer></script>

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<?php if (isset($_SESSION['nickname'])) { ?>
<input type="hidden" id="hdnSession" data-value="<?php echo $_SESSION['nickname']; ?>" />
<?php 
    }
    else {
        ?>
        <input type="hidden" id="hdnSession" data-value="" />
        <?php
    }
?>

<?php
    
?>

<div id="error-section">
</div>

<h1 id="big-game-title" class="game-title"></h1>
<aside class="game-hero">
    <p id="game-description"></p>
    <p id="game-developer"></p>
    <p id="game-release-date"></p>
    <p id="game-rating" class="game-rating_X"></p>
</aside>

<div id="purchase-section">
</div>

<div id="comment-section">
</div>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>

<?php require_once '../GENERAL/[Page_END].php'; ?>