<?php
require_once "../BBDD/conexion.php";

function getUserByLogin($login) {
    global $BBDD;

    $query = $BBDD->prepare("
        SELECT id_usuario, nombre_usuario, nickname, correo, clave_acceso
        FROM Usuarios
        WHERE nickname = ? OR correo = ?
        LIMIT 1
    ");
    
    $query->execute([$login, $login]);
    return $query->fetch(PDO::FETCH_ASSOC);
}
?>