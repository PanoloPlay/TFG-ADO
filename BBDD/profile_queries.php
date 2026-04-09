<?php
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

    $query = $BBDD->prepare("
        SELECT
            id_usuario,
            nombre_usuario,
            nickname,
            correo,
            fecha_registro,
            descripcion,
            id_idioma_principal,
            id_idioma_secundario
        FROM Usuarios
        WHERE id_usuario = ? AND nickname = ?
        LIMIT 1
    ");
    $query->execute([$idUsuario, $nickname]);
    $data['usuario'] = $query->fetch(PDO::FETCH_ASSOC);

    if (!$data['usuario']) {
        return $data;
    }

    $query = $BBDD->prepare("
        SELECT COUNT(*)
        FROM Biblioteca
        WHERE id_usuario = ? AND nickname = ?
    ");
    $query->execute([$idUsuario, $nickname]);
    $data['totalBiblioteca'] = (int)$query->fetchColumn();

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
    $data['totalAmigos'] = (int)$query->fetchColumn();

    $query = $BBDD->prepare("
        SELECT COUNT(*)
        FROM LogrosUsuario
        WHERE id_usuario = ? AND nickname = ?
    ");
    $query->execute([$idUsuario, $nickname]);
    $data['totalLogros'] = (int)$query->fetchColumn();

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
    $data['biblioteca'] = $query->fetchAll(PDO::FETCH_ASSOC);

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
    $data['amigos'] = $query->fetchAll(PDO::FETCH_ASSOC);

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
    $data['logros'] = $query->fetchAll(PDO::FETCH_ASSOC);

    return $data;
}