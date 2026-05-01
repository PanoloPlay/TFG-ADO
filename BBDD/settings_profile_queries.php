<?php

function getSettingsProfileUser(PDO $BBDD, int $idUsuario, string $nickname): ?array
{
    $query = $BBDD->prepare("
        SELECT
            id_usuario,
            nombre_usuario,
            nickname,
            correo,
            fecha_registro,
            descripcion,
            visibilidad,
            id_idioma_principal,
            id_idioma_secundario,
            clave_acceso
        FROM Usuarios
        WHERE id_usuario = ? AND nickname = ?
        LIMIT 1
    ");
    $query->execute([$idUsuario, $nickname]);

    $usuario = $query->fetch(PDO::FETCH_ASSOC);
    return $usuario ?: null;
}

function getSettingsProfileIdiomas(PDO $BBDD): array
{
    $query = $BBDD->prepare("
        SELECT id_idioma, idioma
        FROM Idiomas
        ORDER BY idioma ASC
    ");
    $query->execute();

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function updateSettingsProfileBase(
    PDO $BBDD,
    string $nombreUsuario,
    string $correo,
    ?string $descripcion,
    string $visibilidad,
    string $idiomaPrincipal,
    ?string $idiomaSecundario,
    int $idUsuario,
    string $nickname
): void {
    $query = $BBDD->prepare("
        UPDATE Usuarios
        SET nombre_usuario = ?,
            correo = ?,
            descripcion = ?,
            visibilidad = ?,
            id_idioma_principal = ?,
            id_idioma_secundario = ?
        WHERE id_usuario = ? AND nickname = ?
    ");

    $query->execute([
        $nombreUsuario,
        $correo,
        $descripcion,
        $visibilidad,
        $idiomaPrincipal,
        $idiomaSecundario,
        $idUsuario,
        $nickname
    ]);
}

function updateSettingsProfilePassword(
    PDO $BBDD,
    string $hashNuevaPassword,
    int $idUsuario,
    string $nickname
): void {
    $query = $BBDD->prepare("
        UPDATE Usuarios
        SET clave_acceso = ?
        WHERE id_usuario = ? AND nickname = ?
    ");

    $query->execute([$hashNuevaPassword, $idUsuario, $nickname]);
}