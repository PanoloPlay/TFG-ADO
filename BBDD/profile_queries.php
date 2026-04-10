<?php

function getProfileUserByNickname(PDO $BBDD, string $nickname): ?array
{
    $query = $BBDD->prepare("
        SELECT
            id_usuario,
            nombre_usuario,
            nickname,
            correo,
            fecha_registro,
            descripcion,
            id_idioma_principal,
            id_idioma_secundario,
            visibilidad
        FROM Usuarios
        WHERE nickname = ?
        LIMIT 1
    ");
    $query->execute([$nickname]);

    $usuario = $query->fetch(PDO::FETCH_ASSOC);
    return $usuario ?: null;
}

function getProfileUserByIdAndNickname(PDO $BBDD, int $idUsuario, string $nickname): ?array
{
    $query = $BBDD->prepare("
        SELECT
            id_usuario,
            nombre_usuario,
            nickname,
            correo,
            fecha_registro,
            descripcion,
            id_idioma_principal,
            id_idioma_secundario,
            visibilidad
        FROM Usuarios
        WHERE id_usuario = ? AND nickname = ?
        LIMIT 1
    ");
    $query->execute([$idUsuario, $nickname]);

    $usuario = $query->fetch(PDO::FETCH_ASSOC);
    return $usuario ?: null;
}

function sonAmigos(PDO $BBDD, int $id1, int $id2): bool
{
    $query = $BBDD->prepare("
        SELECT 1
        FROM Amigos
        WHERE (
            (id_usuario1 = ? AND id_usuario2 = ?)
            OR
            (id_usuario1 = ? AND id_usuario2 = ?)
        )
        AND estado = 'aceptada'
        LIMIT 1
    ");
    $query->execute([$id1, $id2, $id2, $id1]);

    return (bool)$query->fetchColumn();
}

function sonAmigosDeAmigos(PDO $BBDD, int $id1, int $id2): bool
{
    $query = $BBDD->prepare("
        SELECT 1
        FROM Amigos a1
        INNER JOIN Amigos a2
            ON a1.id_usuario2 = a2.id_usuario1
        WHERE a1.id_usuario1 = ?
          AND a2.id_usuario2 = ?
          AND a1.estado = 'aceptada'
          AND a2.estado = 'aceptada'
        LIMIT 1
    ");
    $query->execute([$id1, $id2]);

    return (bool)$query->fetchColumn();
}

function getTotalBiblioteca(PDO $BBDD, int $idUsuario, string $nickname): int
{
    $query = $BBDD->prepare("
        SELECT COUNT(*)
        FROM Biblioteca
        WHERE id_usuario = ? AND nickname = ?
    ");
    $query->execute([$idUsuario, $nickname]);

    return (int)$query->fetchColumn();
}

function getTotalAmigos(PDO $BBDD, int $idUsuario, string $nickname): int
{
    $query = $BBDD->prepare("
        SELECT COUNT(*)
        FROM Amigos
        WHERE (
            (id_usuario1 = ? AND nickname1 = ?)
            OR
            (id_usuario2 = ? AND nickname2 = ?)
        )
        AND estado = 'aceptada'
    ");
    $query->execute([$idUsuario, $nickname, $idUsuario, $nickname]);

    return (int)$query->fetchColumn();
}

function getTotalLogros(PDO $BBDD, int $idUsuario, string $nickname): int
{
    $query = $BBDD->prepare("
        SELECT COUNT(*)
        FROM LogrosUsuario
        WHERE id_usuario = ? AND nickname = ?
    ");
    $query->execute([$idUsuario, $nickname]);

    return (int)$query->fetchColumn();
}

function getBiblioteca(PDO $BBDD, int $idUsuario, string $nickname): array
{
    $query = $BBDD->prepare("
        SELECT
            J.id_juego,
            J.nombre_juego,
            J.desarrollador,
            J.fecha_publicacion,
            J.precio,
            J.descuento
        FROM Biblioteca B
        INNER JOIN Juegos J
            ON B.id_juego = J.id_juego
           AND B.nombre_juego = J.nombre_juego
        WHERE B.id_usuario = ? AND B.nickname = ?
        ORDER BY J.fecha_publicacion DESC
        LIMIT 6
    ");
    $query->execute([$idUsuario, $nickname]);

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getAmigos(PDO $BBDD, int $idUsuario, string $nickname): array
{
    $query = $BBDD->prepare("
        SELECT
            CASE
                WHEN id_usuario1 = ? AND nickname1 = ? THEN nickname2
                ELSE nickname1
            END AS amigo
        FROM Amigos
        WHERE (
            (id_usuario1 = ? AND nickname1 = ?)
            OR
            (id_usuario2 = ? AND nickname2 = ?)
        )
        AND estado = 'aceptada'
        ORDER BY amigo ASC
        LIMIT 8
    ");
    $query->execute([$idUsuario, $nickname, $idUsuario, $nickname, $idUsuario, $nickname]);

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getLogros(PDO $BBDD, int $idUsuario, string $nickname): array
{
    $query = $BBDD->prepare("
        SELECT
            LU.fecha_obtencion,
            L.nombre_logro,
            J.nombre_juego
        FROM LogrosUsuario LU
        INNER JOIN Logros L
            ON LU.Logros_id_logro = L.id_logro
           AND LU.id_juego = L.id_juego
           AND LU.Logros_nombre_juego = L.nombre_juego
           AND LU.nombre_logro = L.nombre_logro
        INNER JOIN Juegos J
            ON L.id_juego = J.id_juego
           AND L.nombre_juego = J.nombre_juego
        WHERE LU.id_usuario = ? AND LU.nickname = ?
        ORDER BY LU.fecha_obtencion DESC
        LIMIT 6
    ");
    $query->execute([$idUsuario, $nickname]);

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getProfilePageData(PDO $BBDD, int $idUsuario, string $nickname): array
{
    $data = [
        'usuario' => null,
        'totalBiblioteca' => 0,
        'totalAmigos' => 0,
        'totalLogros' => 0,
        'biblioteca' => [],
        'amigos' => [],
        'logros' => []
    ];

    $data['usuario'] = getProfileUserByIdAndNickname($BBDD, $idUsuario, $nickname);

    if (!$data['usuario']) {
        return $data;
    }

    $data['totalBiblioteca'] = getTotalBiblioteca($BBDD, $idUsuario, $nickname);
    $data['totalAmigos'] = getTotalAmigos($BBDD, $idUsuario, $nickname);
    $data['totalLogros'] = getTotalLogros($BBDD, $idUsuario, $nickname);

    $data['biblioteca'] = getBiblioteca($BBDD, $idUsuario, $nickname);
    $data['amigos'] = getAmigos($BBDD, $idUsuario, $nickname);
    $data['logros'] = getLogros($BBDD, $idUsuario, $nickname);

    return $data;
}