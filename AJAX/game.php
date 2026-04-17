<?php
require_once "../BBDD/conexion.php";

if (isset($_POST['action']) && $_POST['action'] === 'get_game') {

    $sql = 
    "SELECT
        id_juego,
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
        echo "No data found!";
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'check_bought') {

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
        echo "No data found!";
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'get_user_comment') {

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
        echo "No data found!";
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'get_comments') {

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
        echo "No data found!";
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'get_comments') {

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
        echo "No data found!";
        exit();
    }
}
?>