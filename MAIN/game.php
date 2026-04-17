<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>

<?php
    if (!isset($_GET['nameGame']) || is_numeric($_GET['nameGame'])) {
        header('Location: ./');
        exit;
    }

    $stmt = $BBDD->prepare("
        SELECT
            id_juego,
            nombre_juego,
            descripcion,
            desarrollador,
            precio,
            descuento,
            fecha_publicacion
        FROM Juegos
        WHERE nombre_juego = ?
    ");
    $stmt->bindValue(1, $_GET['nameGame']);
    $stmt->execute();
    $juego = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($_SESSION['nickname'])) {
        $stmt = $BBDD->prepare("
        SELECT
            nickname,
            nombre_juego
        FROM Biblioteca
        WHERE 
            nombre_juego = ?
            AND nickname = ?
        ");
        $stmt->bindValue(1, $_GET['nameGame']);
        $stmt->bindValue(2, $_SESSION['nickname']);
        $stmt->execute();
        $Bought = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $BBDD->prepare("
        SELECT
            nickname,
            nombre_juego,
            id_idioma_comentario,
            valoracion,
            comentario
        FROM Valoraciones
        WHERE 
            nombre_juego = ?
            AND nickname = ?
        ");
        $stmt->bindValue(1, $_GET['nameGame']);
        $stmt->bindValue(2, $_SESSION['nickname']);
        $stmt->execute();
        $ValoracionUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <input type="hidden" id="hdnSession" data-value="<?php echo $_SESSION['nickname']; ?>" />;
        <?php
    }
    else {
        $Bought = true;
        $ValoracionUsuario = true;
        echo '<input type="hidden" id="hdnSession" data-value="" />';
    }

    
    $stmt = $BBDD->prepare("
    SELECT
        nickname,
        nombre_juego,
        id_idioma_comentario,
        valoracion,
        comentario
    FROM Valoraciones
    WHERE 
        nombre_juego = ?
    ORDER BY rand()
    ");
    $stmt->bindValue(1, $_GET['nameGame']);
    $stmt->execute();
    $Valoraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $BBDD->prepare("
    SELECT
        nickname,
        nombre_juego,
        id_idioma_comentario,
        valoracion,
        comentario
    FROM Valoraciones
    WHERE 
        nombre_juego = ?
        AND valoracion = 'positiva'
    ");
    $stmt->bindValue(1, $_GET['nameGame']);
    $stmt->execute();
    $positivas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<script src="../JS/game.js" defer></script>

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<?php

    if (!$juego) {
        ?>
        <div class="error-message">
            <h2>Juego no encontrado</h2>
            <p>Lo sentimos, el juego que buscas no existe.</p>
            <a href="./" class="btn">Volver al inicio</a>
        </div>
        <?php
        exit;
    }
?>

<h1><?php echo $juego[0]['nombre_juego']; ?></h1>
<aside class="game-hero">
    <p class="game-description"><?php echo $juego[0]['descripcion']; ?></p>
    <p class="game-developer">Desarrollador: <?php echo $juego[0]['desarrollador']; ?></p>
    <p class="game-release-date">Fecha de publicación: 
        <?php
            $date = new DateTime($juego[0]['fecha_publicacion']);
            echo $date->format('d F Y'); 
        ?>
    </p>
    <?php
        if (!$Valoraciones) {
            ?>
            <p class="game-rating-none">Aún no hay valoraciones para este juego.</p>
            <?php
        }
        else {
            $porcentajePositivas = round((count($positivas) / count($Valoraciones)) * 100, 2);
            ?>
            <p class="game-rating" id="game-rating-porcentage">Valoraciones Positivas: <?php echo $porcentajePositivas; ?>%</p>

            <?php
            if ($porcentajePositivas >= 80) {
            ?>
                <p class="game-rating" id="game-rating-excellent">Opiniones muy positivas</p>
            <?php
            }
            else if ($porcentajePositivas >= 60) {
            ?>
                <p class="game-rating" id="game-rating-positive">Opiniones positivas</p>
            <?php
            }
            else if ($porcentajePositivas >= 40) {
            ?>
                <p class="game-rating" id="game-rating-mixed">Opiniones mixtas</p>
            <?php
            }
            else if ($porcentajePositivas >= 20) {
            ?>
                <p class="game-rating" id="game-rating-negative">Opiniones negativas</p>
            <?php
            }
            else {
            ?>
                <p class="game-rating" id="game-rating-terrible">Opiniones muy negativas</p>
            <?php
            }
        }
    ?>
</aside>

<div class="purchase-section">
    <?php 
        if ($Bought) {
            ?>
                <div class="owned-message">
                    ¡Ya tienes este juego en tu biblioteca!
                    <div class="game-title">Descargar <?php echo $juego[0]['nombre_juego']; ?></div>
                    <button class="download-button">
                        Descargar
                    </button>
                </div>
            <?php
        } 
        else {
    ?>
        
        <div class="game-title">Comprar <?php echo $juego[0]['nombre_juego']; ?></div>
        
        <?php 
            if ($juego[0]['descuento'] > 0) {
                $precioFinal = (float)$juego[0]['precio'] * (1 - ((float)$juego[0]['descuento'] / 100));
        ?>
                <div class="discount">-<?php echo $juego[0]['descuento']; ?>%</div>
                <div class="price">
                    <del><?php echo number_format((float)$juego[0]['precio'], 2); ?> €</del>
                    <?php echo number_format($precioFinal, 2); ?> €
                </div>
        <?php
            } 
            else {
                ?>
                <div class="price">
                    <?php echo number_format($juego[0]['precio'], 2); ?>€
                </div>
        <?php
            }
        ?>
                <button class="buy-button">
                    Añadir al carrito
                </button>
            </div>
    <?php
        }
    ?>
</div>

<?php
    if ($Bought) {
        if (!$ValoracionUsuario) {
        ?>
            <div class="owned-make-comment">
                <p>Escribe tu opinión sobre <?php echo $juego[0]['nombre_juego']; ?>:</p>
                <textarea placeholder="Escribe tu comentario aquí..."></textarea>
                <button class="submit-comment-button">
                    Enviar comentario
                </button>
            </div>
        <?php
        }
        else {
        ?>
            <div class="owned-edit-comment">
                <p>Tu opinión sobre <?php echo $juego[0]['nombre_juego']; ?>:</p>
                <textarea placeholder="Edita tu comentario aquí..."><?php echo $ValoracionUsuario[0]['comentario']; ?></textarea>
                <button class="submit-comment-button">
                    Editar comentario
                </button>
                <button class="delete-comment-button">
                    Borrar comentario
                </button>
            </div>
        <?php
        }
    }

    foreach ($Valoraciones as $valoracion) {
        ?>
            <div class="user-review">
                <p class="review-user"><?php echo $valoracion['nickname']; ?>:</p>
                <p class="review-rating">Valoración: <?php echo ucfirst($valoracion['valoracion']); ?></p>
                <p class="review-comment"><?php echo $valoracion['comentario']; ?></p>
            </div>
        <?php
    }
?>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>

<?php require_once '../GENERAL/[Page_END].php'; ?>