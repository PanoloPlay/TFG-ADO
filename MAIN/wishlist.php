<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<script src="../JS/checkIfXExists.js" defer></script>
<script src="../JS/wishlist.js" defer></script>

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

<h6>Mi lista de deseos</h6>
<p id="count-wishgames"></p>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>

<?php require_once '../GENERAL/[Page_END].php'; ?>