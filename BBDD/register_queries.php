<?php

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

function claveAmigosExists($claveAmigos) {
    global $BBDD;

    $query = $BBDD->prepare("
        SELECT 1
        FROM Usuarios
        WHERE clave_amigos = ?
        LIMIT 1
    ");

    $query->execute([$claveAmigos]);
    return (bool) $query->fetchColumn();
}

function triggerClaveAmigos() {
    do {
        $claveAmigos = random_int(100000000, 999999999); // 9 dígitos
    } while (claveAmigosExists($claveAmigos));

    return $claveAmigos;
}

function insertUser($nombre, $nickname, $correo, $hash, $descripcion, $idiomaPrincipal, $idiomaSecundario, $claveAmigos) {
    global $BBDD;

    $query = $BBDD->prepare("
        INSERT INTO Usuarios
        (nombre_usuario, nickname, correo, clave_acceso, fecha_registro, descripcion, clave_amigos, id_idioma_principal, id_idioma_secundario)
        VALUES
        (?, ?, ?, ?, NOW(), ?, ?, ?, ?)
    ");

    return $query->execute([
        $nombre,
        $nickname,
        $correo,
        $hash,
        $descripcion,
        $claveAmigos,
        $idiomaPrincipal,
        $idiomaSecundario
    ]);
}
?>