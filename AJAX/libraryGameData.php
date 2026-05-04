<?php
require_once "../BBDD/conexion.php";

session_start();

if (isset($_POST['action']) && $_POST['action'] === 'get_user') {

    if (empty($_SESSION["nickname"])) {
        echo json_encode("No data found!");
        exit();
    }

    if (empty($_POST['value_1'])) {
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

    if (empty($_POST['value_1'])) {
        echo json_encode("No data found!");
        exit();
    }

    if ($_SESSION["nickname"] != $_POST['value_1']) {
        echo json_encode("No data found!");
        exit();
    }

    $nickname = $_POST['value_1'];

    $stmt = $BBDD->prepare("SELECT j.`nombre_juego`, j.`descripcion`, j.`fecha_publicacion`, j.`desarrollador`, j.`precio`, j.`descuento`, COUNT(v.`id_valoracion`) AS valoraciones, COUNT(CASE WHEN v.`valoracion` = 'positiva' THEN 1 END) AS valoraciones_positivas, AVG(CASE WHEN v.`valoracion` = 'positiva' THEN 1 ELSE 0 END) AS valoracion_media
                            FROM `biblioteca` AS b 
                            JOIN `juegos` AS j ON b.`id_juego` = j.`id_juego` 
                            JOIN `valoraciones` AS v ON b.`nombre_juego` = v.`nombre_juego`
                            WHERE b.`nickname` = :nick 
                            GROUP BY j.`nombre_juego`");
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

if (isset($_POST['action']) && $_POST['action'] === 'get_logros') {

    if (empty($_POST['value_1'])) {
        echo json_encode("No data found!");
        exit();
    }

    $nameJuego = $_POST['value_1'];

    $stmt = $BBDD->prepare("SELECT `nombre_logro`, `descripcion_logro`, `nombre_juego` 
                            FROM `logros` AS l
                            WHERE `nombre_juego` = :nmJuego AND `nombre_logro` NOT IN (SELECT `nombre_logro` FROM `logrosusuario` WHERE `nickname` = :nick AND `Logros_nombre_juego` = :nmJuego)");
    $stmt->bindParam(":nmJuego", $nameJuego);
    $stmt->bindParam(":nick", $_SESSION["nickname"]);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    } else {
        echo json_encode("No data found!");
        exit();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'get_logros_user') {

    if (empty($_POST['value_1'])) {
        echo json_encode("No data found!");
        exit();
    }

    $nameJuego = $_POST['value_1'];

    $stmt = $BBDD->prepare("SELECT lu.`nickname`, lu.`Logros_nombre_juego`, lu.`nombre_logro`, lu.`fecha_obtencion`, l.`descripcion_logro` 
                            FROM `logrosusuario` As lu
                            JOIN `logros` AS l ON lu.`nombre_logro` = l.`nombre_logro`
                            WHERE lu.`Logros_nombre_juego` = :nmJuego AND lu.`nickname` = :nick");
    $stmt->bindParam(":nmJuego", $nameJuego);
    $stmt->bindParam(":nick", $_SESSION["nickname"]);
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