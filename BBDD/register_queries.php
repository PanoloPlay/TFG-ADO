<?php
require_once "../BBDD/conexion.php";

function getIdiomas() {
    global $BBDD;

    $query = $BBDD->query("
        SELECT id_idioma, idioma 
        FROM Idiomas 
        ORDER BY idioma ASC
    ");

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function UserRecordexists($nickname, $correo) {
    global $BBDD;

    $query = $BBDD->prepare("
        SELECT 1
        FROM Usuarios
        WHERE nickname = ? OR correo = ?
        LIMIT 1
    ");

    $query->execute([$nickname, $correo]);
    return $query->fetchColumn();
}

function insertUser($nombre, $nickname, $correo, $hash, $descripcion, $idiomaPrincipal, $idiomaSecundario) {
    global $BBDD;

    $query = $BBDD->prepare("
        INSERT INTO Usuarios
        (nombre_usuario, nickname, correo, clave_acceso, fecha_registro, descripcion, id_idioma_principal, id_idioma_secundario)
        VALUES
        (?, ?, ?, ?, NOW(), ?, ?, ?)
    ");

    return $query->execute([
        $nombre,
        $nickname,
        $correo,
        $hash,
        $descripcion,
        $idiomaPrincipal,
        $idiomaSecundario
    ]);
}
?>