<?php
require_once "../BBDD/conexion.php";

session_start();

if (isset($_POST['action']) && $_POST['action'] === 'get_game') {

    if (empty($_POST['value_1'])) {
        echo json_encode("No data found!");
        exit();
    }

    $sql = 
    "SELECT
        nombre_juego,
        descripcion,
        desarrollador,
        precio,
        descuento,
        fecha_publicacion
    FROM Juegos
    WHERE nombre_juego = ?
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindValue(1, $_POST['value_1']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    } else {
        echo json_encode("No data found!");
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'check_bought') {

    if (empty($_SESSION["nickname"])) {
        echo json_encode("No data found!");
        exit();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        echo json_encode("No data found!");
        exit();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        echo json_encode("No data found!");
        exit();
    }
    

    $sql = 
    "SELECT
        nickname,
        nombre_juego
    FROM Biblioteca
    WHERE 
        nombre_juego = ?
        AND nickname = ?
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindValue(1, $_POST['value_1']);
    $stmt->bindValue(2, $_POST['value_2']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    } else {
        echo json_encode("No data found!");
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'user_comment') {

    if (empty($_SESSION["nickname"])) {
        echo json_encode("No data found!");
        exit();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        echo json_encode("No data found!");
        exit();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        echo json_encode("No data found!");
        exit();
    }

    $sql = 
    "SELECT
        nickname,
        nombre_juego,
        id_idioma_comentario,
        valoracion,
        comentario
    FROM Valoraciones
    WHERE 
        nombre_juego = ?
        AND nickname = ?
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindValue(1, $_POST['value_1']);
    $stmt->bindValue(2, $_POST['value_2']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    } else {
        echo json_encode("No data found!");
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'get_comments') {

    if (empty($_POST['value_1'])) {
        echo json_encode("No data found!");
        exit();
    }

    $sql = 
    "SELECT
        nickname,
        nombre_juego,
        id_idioma_comentario,
        valoracion,
        comentario
    FROM Valoraciones
    WHERE 
        nombre_juego = ?
    ORDER BY rand()
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindValue(1, $_POST['value_1']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    } else {
        echo json_encode("No data found!");
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'positive') {

    if (empty($_POST['value_1'])) {
        echo json_encode("No data found!");
        exit();
    }

    $sql = 
    "SELECT
        nickname,
        nombre_juego,
        id_idioma_comentario,
        valoracion,
        comentario
    FROM Valoraciones
    WHERE 
        nombre_juego = ?
        AND valoracion = 'positiva'
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindValue(1, $_POST['value_1']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    } else {
        echo json_encode("No data found!");
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'create_comment') {

    $sql = "INSERT INTO `valoraciones`
    (`id_valoracion`, `id_juego`, `nombre_juego`, `id_idioma_comentario`, `valoracion`, `comentario`, `id_usuario`, `nickname`) 
    VALUES (2,1,'Deltarune','es','positiva','new',3,'ccc')";

        $stmt = $BBDD->prepare($sql);
        
        $stmt->execute();

        echo json_encode("No data found!");
        exit();
}

if (isset($_POST['action']) && $_POST['action'] === '???') {

    if (empty($_POST['value_1'])) {
        echo json_encode("No data found!");
        exit();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        echo json_encode("No data found!");
        exit();
    }

    $sql = 
    "SELECT
        nickname,
        nombre_juego,
        id_idioma_comentario,
        valoracion,
        comentario
    FROM Valoraciones
    WHERE 
        nombre_juego = ?
        AND valoracion = 'negativa'
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindValue(1, $_POST['value_1']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    } else {
        echo json_encode("No data found!");
        exit();
    }
}
?>