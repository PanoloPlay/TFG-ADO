<?php
require_once "../BBDD/conexion.php";

session_start();

header('Content-Type: application/json; charset=utf-8');

function respondOk(string $message = 'OK', array $extra = []): void {
    echo json_encode(array_merge([
        'success' => true,
        'message' => $message
    ], $extra));
    exit();
}

function respondFail(string $message = 'No data found!'): void {
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'get_game') {

    if (empty($_POST['value_1'])) {
        respondFail();
    }

    $sql = "
        SELECT
            nombre_juego,
            descripcion,
            desarrollador,
            precio,
            descuento,
            fecha_publicacion
        FROM Juegos
        WHERE nombre_juego = :nmJuego
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        echo json_encode($data);
        exit();
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'check_bought') {

    if (empty($_SESSION["nickname"])) {
        respondFail();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        respondFail();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail();
    }

    $sql = "
        SELECT
            nickname,
            nombre_juego
        FROM Biblioteca
        WHERE
            nombre_juego = :nmJuego
            AND nickname = :nick
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);
    $stmt->bindParam(":nick", $_POST['value_2']);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        echo json_encode($data);
        exit();
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'user_comment') {

    if (empty($_SESSION["nickname"])) {
        respondFail();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        respondFail();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail();
    }

    $sql = "
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
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindValue(1, $_POST['value_1']);
    $stmt->bindValue(2, $_POST['value_2']);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        echo json_encode($data);
        exit();
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'get_comments') {

    if (empty($_POST['value_1'])) {
        respondFail();
    }

    $sql = "
        SELECT
            nickname,
            nombre_juego,
            id_idioma_comentario,
            valoracion,
            comentario,
            fechaPublicacion
        FROM Valoraciones
        WHERE
            nombre_juego = ?
        ORDER BY rand()
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindValue(1, $_POST['value_1']);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        echo json_encode($data);
        exit();
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'positive') {

    if (empty($_POST['value_1'])) {
        respondFail();
    }

    $sql = "
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
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindValue(1, $_POST['value_1']);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        echo json_encode($data);
        exit();
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'negative') {

    if (empty($_POST['value_1'])) {
        respondFail();
    }

    $sql = "
        SELECT
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

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        echo json_encode($data);
        exit();
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'get_languages') {

    if (empty($_SESSION["nickname"])) {
        respondFail();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        respondFail();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail();
    }

    $stmt = $BBDD->prepare("SELECT nickname, id_idioma_principal, id_idioma_secundario FROM Usuarios WHERE nickname = :nick");
    $stmt->bindValue(":nick", $_POST['value_2']);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        echo json_encode($data);
        exit();
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'buy_game') {

    if (empty($_SESSION["nickname"])) {
        respondFail('Sesión no válida');
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        respondFail('Faltan datos');
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail('Usuario no autorizado');
    }

    $sql = "
        SELECT id_usuario, nickname
        FROM Usuarios
        WHERE nickname = :nick
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nick", $_POST['value_2']);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        respondFail('Usuario no encontrado');
    }

    $sql = "
        SELECT id_juego, nombre_juego
        FROM Juegos
        WHERE nombre_juego = :nmJuego
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);
    $stmt->execute();

    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$game) {
        respondFail('Juego no encontrado');
    }

    $stmt = $BBDD->prepare("SELECT COUNT(*) FROM Biblioteca WHERE nickname = :nick AND nombre_juego = :nmJuego");
    $stmt->bindParam(":nick", $_POST['value_2']);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        respondFail('Ya tienes este juego en tu biblioteca');
    }

    $stmt = $BBDD->prepare("
        INSERT INTO Biblioteca (id_usuario, nickname, id_juego, nombre_juego)
        VALUES (:idUsuario, :nick, :idJuego, :nmJuego)
    ");
    $stmt->bindParam(":idUsuario", $user["id_usuario"]);
    $stmt->bindParam(":nick", $_POST['value_2']);
    $stmt->bindParam(":idJuego", $game["id_juego"]);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);

    $ok = $stmt->execute();

    if ($ok) {
        $stmt = $BBDD->prepare("DELETE FROM ListaDeseos WHERE nickname = :nick AND nombre_juego = :nmJuego");
        $stmt->bindParam(":nick", $_POST['value_2']);
        $stmt->bindParam(":nmJuego", $_POST['value_1']);
        $stmt->execute();

        respondOk('Compra realizada correctamente');
    }

    respondFail('No se pudo completar la compra');
}

if (isset($_POST['action']) && $_POST['action'] === 'create_comment') {

    if (empty($_SESSION["nickname"])) {
        respondFail();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2']) || empty($_POST['value_3']) || empty($_POST['value_4'])) {
        respondFail();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail();
    }

    if ($_POST['value_3'] == "positiva" || $_POST['value_3'] == "negativa") {

        $sql = "
            SELECT id_usuario, nickname, id_idioma_principal
            FROM Usuarios
            WHERE nickname = :nick
        ";

        $stmt = $BBDD->prepare($sql);
        $stmt->bindParam(":nick", $_POST['value_2']);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            respondFail();
        }

        $sql = "
            SELECT id_juego, nombre_juego
            FROM Juegos
            WHERE nombre_juego = :nmJuego
        ";

        $stmt = $BBDD->prepare($sql);
        $stmt->bindParam(":nmJuego", $_POST['value_1']);
        $stmt->execute();

        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$game) {
            respondFail();
        }

        $idUserMainLanguage = $user["id_idioma_principal"];
        $nickname = $_POST['value_2'];
        $nameJuego = $_POST['value_1'];
        $comment = $_POST['value_4'];
        $rating = $_POST['value_3'];
        $currentTime = date('Y-m-d H:i:s');

        $stmt = $BBDD->prepare("
            INSERT INTO Valoraciones
            (nombre_juego, nickname, id_idioma_comentario, valoracion, comentario, fechaPublicacion)
            VALUES (:nmJuego, :nick, :idMainLanguage, :rating, :comment, :currentTime)
        ");
        $stmt->bindParam(":nmJuego", $nameJuego);
        $stmt->bindParam(":nick", $nickname);
        $stmt->bindParam(":idMainLanguage", $idUserMainLanguage);
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":currentTime", $currentTime);

        $ok = $stmt->execute();

        if ($ok) {
            respondOk('Comentario creado correctamente');
        }

        respondFail('No se pudo crear el comentario');
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'update_comment') {

    if (empty($_SESSION["nickname"])) {
        respondFail();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2']) || empty($_POST['value_3']) || empty($_POST['value_4'])) {
        respondFail();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail();
    }

    if ($_POST['value_3'] == "positiva" || $_POST['value_3'] == "negativa") {

        $sql = "
            SELECT id_usuario, nickname, id_idioma_principal
            FROM Usuarios
            WHERE nickname = :nick
        ";

        $stmt = $BBDD->prepare($sql);
        $stmt->bindParam(":nick", $_POST['value_2']);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            respondFail();
        }

        $sql = "
            SELECT id_juego, nombre_juego
            FROM Juegos
            WHERE nombre_juego = :nmJuego
        ";

        $stmt = $BBDD->prepare($sql);
        $stmt->bindParam(":nmJuego", $_POST['value_1']);
        $stmt->execute();

        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$game) {
            respondFail();
        }

        $idUserMainLanguage = $user["id_idioma_principal"];
        $nickname = $_POST['value_2'];
        $nameJuego = $_POST['value_1'];
        $comment = $_POST['value_4'];
        $rating = $_POST['value_3'];

        $stmt = $BBDD->prepare("
            UPDATE Valoraciones
            SET id_idioma_comentario = :idMainLanguage,
                valoracion = :rating,
                comentario = :comment
            WHERE nombre_juego = :nmJuego
              AND nickname = :nick
        ");
        $stmt->bindParam(":idMainLanguage", $idUserMainLanguage);
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":nmJuego", $nameJuego);
        $stmt->bindParam(":nick", $nickname);

        $ok = $stmt->execute();

        if ($ok) {
            respondOk('Comentario actualizado correctamente');
        }

        respondFail('No se pudo actualizar el comentario');
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_comment') {

    if (empty($_SESSION["nickname"])) {
        respondFail();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        respondFail();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail();
    }

    $nickname = $_POST['value_2'];
    $nameJuego = $_POST['value_1'];

    $stmt = $BBDD->prepare("DELETE FROM Valoraciones WHERE nombre_juego = :nmJuego AND nickname = :nick");
    $stmt->bindParam(":nmJuego", $nameJuego);
    $stmt->bindParam(":nick", $nickname);

    $ok = $stmt->execute();

    if ($ok) {
        respondOk('Comentario eliminado correctamente');
    }

    respondFail('No se pudo eliminar el comentario');
}

if (isset($_POST['action']) && $_POST['action'] === 'add_wishlist') {

    if (empty($_SESSION["nickname"])) {
        respondFail('Sesión no válida');
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        respondFail('Faltan datos');
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail('Usuario no autorizado');
    }

    $sql = "
        SELECT id_usuario, nickname
        FROM Usuarios
        WHERE nickname = :nick
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nick", $_POST['value_2']);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        respondFail('Usuario no encontrado');
    }

    $sql = "
        SELECT id_juego, nombre_juego
        FROM Juegos
        WHERE nombre_juego = :nmJuego
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);
    $stmt->execute();

    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$game) {
        respondFail('Juego no encontrado');
    }

    $stmt = $BBDD->prepare("SELECT COUNT(*) FROM ListaDeseos WHERE nickname = :nick AND nombre_juego = :nmJuego");
    $stmt->bindParam(":nick", $_POST['value_2']);
    $stmt->bindParam(":nmJuego", $_POST['value_1']);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        respondFail('Este juego ya está en tu lista de deseos');
    }

    $idUser = $user["id_usuario"];
    $nickname = $_POST['value_2'];
    $idGame = $game["id_juego"];
    $nameJuego = $_POST['value_1'];

    $stmt = $BBDD->prepare("
        INSERT INTO ListaDeseos (id_usuario, nickname, id_juego, nombre_juego)
        VALUES (:idUser, :nick, :idJuego, :nmJuego)
    ");

    $stmt->bindParam(":idUser", $idUser);
    $stmt->bindParam(":nick", $nickname);
    $stmt->bindParam(":idJuego", $idGame);
    $stmt->bindParam(":nmJuego", $nameJuego);

    $ok = $stmt->execute();

    if ($ok) {
        respondOk('Añadido a la lista de deseos');
    }

    respondFail('No se pudo añadir a la lista de deseos');
}

if (isset($_POST['action']) && $_POST['action'] === 'remove_wishlist') {

    if (empty($_SESSION["nickname"])) {
        respondFail();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        respondFail();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail();
    }

    $nickname = $_POST['value_2'];
    $nameJuego = $_POST['value_1'];

    $stmt = $BBDD->prepare("DELETE FROM ListaDeseos WHERE nombre_juego = :nmJuego AND nickname = :nick");
    $stmt->bindParam(":nmJuego", $nameJuego);
    $stmt->bindParam(":nick", $nickname);

    $ok = $stmt->execute();

    if ($ok) {
        respondOk('Eliminado de la lista de deseos');
    }

    respondFail('No se pudo eliminar de la lista de deseos');
}

if (isset($_POST['action']) && $_POST['action'] === 'get_wishlist') {

    if (empty($_SESSION["nickname"])) {
        respondFail();
    }

    if (empty($_POST['value_1']) || empty($_POST['value_2'])) {
        respondFail();
    }

    if ($_SESSION["nickname"] != $_POST['value_2']) {
        respondFail();
    }

    $nickname = $_POST['value_2'];
    $nameJuego = $_POST['value_1'];

    $stmt = $BBDD->prepare("SELECT * FROM ListaDeseos WHERE nombre_juego = :nmJuego AND nickname = :nick");
    $stmt->bindParam(":nmJuego", $nameJuego);
    $stmt->bindParam(":nick", $nickname);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        echo json_encode($data);
        exit();
    }

    respondFail();
}

if (isset($_POST['action']) && $_POST['action'] === 'get_game_categories') {

    if (empty($_POST['value_1'])) {
        respondFail();
    }

    $nameJuego = $_POST['value_1'];

    $stmt = $BBDD->prepare("SELECT categoria FROM Categorias_Juego WHERE nombre_juego = :nmJuego");
    $stmt->bindParam(":nmJuego", $nameJuego);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        echo json_encode($data);
        exit();
    }

    respondFail();
}

respondFail('Acción no reconocida');
?>