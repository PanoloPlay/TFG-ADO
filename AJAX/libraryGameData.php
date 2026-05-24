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

    $stmt = $BBDD->prepare("SELECT `nickname` FROM `Usuarios` WHERE `nickname` = :nick ");
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

    $stmt = $BBDD->prepare("SELECT j.`id_juego`, j.`nombre_juego`, j.`descripcion`, j.`fecha_publicacion`, j.`desarrollador`, j.`precio`, j.`descuento`, COUNT(v.`id_valoracion`) AS valoraciones, COUNT(CASE WHEN v.`valoracion` = 'positiva' THEN 1 END) AS valoraciones_positivas, AVG(CASE WHEN v.`valoracion` = 'positiva' THEN 1 ELSE 0 END) AS valoracion_media
                            FROM `Biblioteca` AS b 
                            JOIN `Juegos` AS j ON b.`id_juego` = j.`id_juego` 
                            JOIN `Valoraciones` AS v ON b.`nombre_juego` = v.`nombre_juego`
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

    $stmt = $BBDD->prepare("SELECT `nombre_logro`, `descripcion_logro`, `rareza`, `nombre_juego` 
                            FROM `Logros` AS l
                            WHERE `nombre_juego` = :nmJuego AND `id_logro` NOT IN (SELECT `Logros_id_logro` FROM `LogrosUsuario` WHERE `nickname` = :nick AND `Logros_nombre_juego` = :nmJuego)");
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

    $sql = "
        SELECT 
            l.nombre_logro, l.descripcion_logro, l.rareza, l.nombre_juego, lu.fecha_obtencion 
        FROM 
            LogrosUsuario AS lu 
        JOIN 
            Logros AS l ON l.id_logro = lu.Logros_id_logro
        WHERE 
            lu.nickname = :nick 
            AND l.nombre_juego = :nmJuego 
    ";

    $stmt = $BBDD->prepare($sql);

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

if (isset($_POST['action']) && $_POST['action'] === 'fileExists') {

    if (file_exists($_POST['obj'])) {
        echo "exists";
        exit;
    }
    else {
        echo "Not Found";
        exit;
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'getFileName') {

    $directory = $_POST['obj'];
    $scanned_directory = array_diff(scandir($directory), array('..', '.'));

    foreach($scanned_directory as $key => $value) {
        if (file_exists($directory . $value)) {
            echo $value;
            exit;
        }
        else {
            echo "Not Found";
            exit;
        }
    }

    echo "Not Found";
    exit;
}
?>