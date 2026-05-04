<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<link rel="stylesheet" href="../CSS/game.css">

<script src="../JS/checkIfXExists.js" defer></script>
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

<div id="error-section">
</div>

<h1 id="big-game-title" class="game-title"></h1>
<aside class="game-hero">
    <p id="game-description"></p>
    <p id="game-developer"></p>
    <p id="game-release-date"></p>
    <p id="game-rating" class="game-rating_X"></p>
    <div id="game-categories">
    </div>
</aside>

<div id="carouselExampleIndicators" class="carousel slide w-50">
    <div id="carousel-container" class="carousel-inner">
        <?php
        if (isset($_GET["name"])) {

            $active = true;
            $exists = false;
            $gameName = $_GET["name"];
            $gameName = str_replace(".", "", $gameName);
            $gameName = str_replace(",", "", $gameName);
            $gameName = str_replace(":", "", $gameName);
            $gameName = str_replace(";", "", $gameName);

            $gameName = trim($gameName);

            $path = "../VIDEO/" . $gameName . "/*.*";
            $array = glob($path);

            foreach ($array as $value) {
                $finalValue = trim($value);
                if ($active) {
                    ?>
                    <div class="carousel-item active">
                    <?php
                    $active = false;
                    $exists = true;
                }
                else {
                    ?>
                    <div class="carousel-item">
                    <?php
                }
                ?>
                        <video class="video-carousel d-block w-100" controls>
                            <source src="<?php echo $finalValue ?>" type="video/mp4">
                            <source src="<?php echo $finalValue ?>" type="video/ogg">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                <?php
            }

            $path = "../IMG/juegos/" . $gameName . "/*.*";
            $array = glob($path);

            foreach ($array as $value) {
                $finalValue = trim($value);
                if ($active) {
                    ?>
                    <div class="carousel-item active">
                    <?php
                    $active = false;
                    $exists = true;
                }
                else {
                    ?>
                    <div class="carousel-item">
                    <?php
                }
                ?>
                        <img src="<?php echo $finalValue ?>" class="d-block w-100" alt="...">
                    </div>
                <?php
            }
            ?> </div> <?php
            
            if ($exists) {
                ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev" onclick="pauseVideoIfPlaying()">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next" onclick="pauseVideoIfPlaying()">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php
            }
        }
        ?>
</div>

<div id="purchase-section">
</div>

<div id="comment-section">
</div>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>

<?php require_once '../GENERAL/[Page_END].php'; ?>