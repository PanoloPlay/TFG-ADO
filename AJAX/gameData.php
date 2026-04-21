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
    FROM juegos
    WHERE nombre_juego = :nmJuego
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);
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
    FROM biblioteca
    WHERE
        nombre_juego = :nmJuego
        AND nickname = :nick
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);
    $stmt->bindParam(":nick", $_POST['value_2']);
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

if (isset($_POST['action']) && $_POST['action'] === 'buy_game') {

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
        id_usuario,
        nickname
    FROM usuarios
    WHERE nickname = :nick
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nick", $_POST['value_2']);
    $stmt->execute();

    if ($stmt->rowCount() != 1) {
        echo json_encode("No data found!");
        exit();
    }

    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql =
    "SELECT
        id_juego,
        nombre_juego
    FROM juegos
    WHERE nombre_juego = :nmJuego
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);
    $stmt->execute();

    $game = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($stmt->rowCount() != 1) {
        echo json_encode("No data found!");
        exit();
    }

    $idUser = $user[0]["id_usuario"];
    $nickname = $_POST['value_2'];
    $idJuego = $game[0]["id_juego"];
    $nameJuego = $_POST['value_1'];

    $stmt = $BBDD->prepare("INSERT INTO `biblioteca`(`id_usuario`, `nickname`, `id_juego`, `nombre_juego`) VALUES (:idUsuario, :nick, :idJuego, :nmJuego)");
    $stmt->bindParam(":idUsuario", $idUser);
    $stmt->bindParam(":nick", $nickname);
    $stmt->bindParam(":idJuego", $idJuego);
    $stmt->bindParam(":nmJuego", $nameJuego);
    $stmt->execute();

    echo json_encode("No data found!");
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'create_comment') {

    if (empty($_SESSION["nickname"])) {
        echo json_encode("No data found!");
        exit();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2']) || empty($_POST['value_3']) || empty($_POST['value_4'])) {
        echo json_encode("No data found!");
        exit();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        echo json_encode("No data found!");
        exit();
    }

    if ($_POST['value_3'] == "positiva" || $_POST['value_3'] == "negativa") {

        $sql =
        "SELECT
            id_usuario,
            nickname,
            id_idioma_principal
        FROM usuarios
        WHERE nickname = :nick
        ";

        $stmt = $BBDD->prepare($sql);
        $stmt->bindParam(":nick", $_POST['value_2']);
        $stmt->execute();

        if ($stmt->rowCount() != 1) {
            echo json_encode("No data found!");
            exit();
        }

        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sql =
        "SELECT
            id_juego,
            nombre_juego
        FROM juegos
        WHERE nombre_juego = :nmJuego
        ";

        $stmt = $BBDD->prepare($sql);
        $stmt->bindParam(":nmJuego", $_POST['value_1']);
        $stmt->execute();

        $game = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() != 1) {
            echo json_encode("No data found!");
            exit();
        }

        $idUserMainLanguage = $user[0]["id_idioma_principal"];
        $nickname = $_POST['value_2'];

        $nameJuego = $_POST['value_1'];

        $comment = $_POST['value_4'];

        $rating = $_POST['value_3'];

        $stmt = $BBDD->prepare("INSERT INTO `valoraciones`(`nombre_juego`, `nickname`, `id_idioma_comentario`, `valoracion`, `comentario`) VALUES (:nmJuego, :nick, :idMainLanguage, :rating, :comment)");
        $stmt->bindParam(":nmJuego", $nameJuego);
        $stmt->bindParam(":nick", $nickname);
        $stmt->bindParam(":idMainLanguage", $idUserMainLanguage);
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":comment", $comment);
        $stmt->execute();
    }

    echo json_encode("No data found!");
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'update_comment') {

    if (empty($_SESSION["nickname"])) {
        echo json_encode("No data found!");
        exit();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2']) || empty($_POST['value_3']) || empty($_POST['value_4'])) {
        echo json_encode("No data found!");
        exit();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        echo json_encode("No data found!");
        exit();
    }

    if ($_POST['value_3'] == "positiva" || $_POST['value_3'] == "negativa") {

        $sql =
        "SELECT
            id_usuario,
            nickname,
            id_idioma_principal
        FROM usuarios
        WHERE nickname = :nick
        ";

        $stmt = $BBDD->prepare($sql);
        $stmt->bindParam(":nick", $_POST['value_2']);
        $stmt->execute();

        if ($stmt->rowCount() != 1) {
            echo json_encode("No data found!");
            exit();
        }

        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sql =
        "SELECT
            id_juego,
            nombre_juego
        FROM juegos
        WHERE nombre_juego = :nmJuego
        ";

        $stmt = $BBDD->prepare($sql);
        $stmt->bindParam(":nmJuego", $_POST['value_1']);
        $stmt->execute();

        $game = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() != 1) {
            echo json_encode("No data found!");
            exit();
        }

        $idUserMainLanguage = $user[0]["id_idioma_principal"];
        $nickname = $_POST['value_2'];

        $nameJuego = $_POST['value_1'];

        $comment = $_POST['value_4'];

        $rating = $_POST['value_3'];

        $stmt = $BBDD->prepare("UPDATE `valoraciones` SET `id_idioma_comentario`=:idMainLanguage, `valoracion`=:rating, `comentario`=:comment WHERE `nombre_juego`=:nmJuego AND `nickname`=:nick");
        $stmt->bindParam(":idMainLanguage", $idUserMainLanguage);
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":nmJuego", $nameJuego);
        $stmt->bindParam(":nick", $nickname);
        $stmt->execute();
    }

    echo json_encode("No data found!");
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_comment') {

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

    $nickname = $_POST['value_2'];

    $nameJuego = $_POST['value_1'];

    $stmt = $BBDD->prepare("DELETE FROM `valoraciones` WHERE `nombre_juego`=:nmJuego AND `nickname`=:nick");
    
    $stmt->bindParam(":nmJuego", $nameJuego);
    $stmt->bindParam(":nick", $nickname);
    $stmt->execute();

    echo json_encode("No data found!");
    exit();
}
?>