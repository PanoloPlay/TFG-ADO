<?php
require_once "../BBDD/conexion.php";

session_start();

if (isset($_POST['action']) && $_POST['action'] === 'get_user') {

    if (empty($_SESSION["nickname"])) {
        echo json_encode("No data found!");
        exit();
    }

    if ($_SESSION["nickname"] != $_POST['value_1']) {
        echo json_encode("No data found!");
        exit();
    }

    $nickname = $_POST['value_1'];

    $stmt = $BBDD->prepare("SELECT `nickname` FROM `usuarios` WHERE `nickname` = :nick ");
    $stmt->bindParam(":nick", $nickname);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    } else {
        echo json_encode("No data found!");
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'get_library') {

    if (empty($_SESSION["nickname"])) {
        echo json_encode("No data found!");
        exit();
    }

    if ($_SESSION["nickname"] != $_POST['value_1']) {
        echo json_encode("No data found!");
        exit();
    }

    $nickname = $_POST['value_1'];

    $stmt = $BBDD->prepare("SELECT `nombre_juego` FROM `biblioteca` WHERE `nickname` = :nick ");
    $stmt->bindParam(":nick", $nickname);
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