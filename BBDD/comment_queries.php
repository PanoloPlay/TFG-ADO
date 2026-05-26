<?php
declare(strict_types=1);

function getCommentGameData(PDO $BBDD, int $gameId): ?array
{
    $stmt = $BBDD->prepare("SELECT id_juego, nombre_juego, descripcion, fecha_publicacion, desarrollador, precio, descuento FROM Juegos WHERE id_juego = ? LIMIT 1");
    if ($stmt === false) {
        return null;
    }

    $stmt->execute([$gameId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

function comment_get_reviews(PDO $BBDD, int $gameId): array
{
    $stmt = $BBDD->prepare(
        "SELECT
            U.nombre_usuario,
            V.nickname,
            V.comentario,
            V.valoracion,
            V.fechaPublicacion,
            V.id_idioma_comentario
        FROM Valoraciones V
        INNER JOIN Usuarios U ON V.nickname = U.nickname
        INNER JOIN Juegos J ON V.nombre_juego = J.nombre_juego
        WHERE J.id_juego = ?
        ORDER BY V.fechaPublicacion DESC"
    );

    if ($stmt === false) {
        return [];
    }

    $stmt->execute([$gameId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function comment_get_positive_count(PDO $BBDD, int $gameId): int
{
    $stmt = $BBDD->prepare(
        "SELECT COUNT(*) AS positive_count
         FROM Valoraciones V
         INNER JOIN Juegos J ON V.nombre_juego = J.nombre_juego
         WHERE J.id_juego = ? AND V.valoracion = 'positiva'"
    );

    if ($stmt === false) {
        return 0;
    }

    $stmt->execute([$gameId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)($result['positive_count'] ?? 0);
}

function comment_get_negative_count(PDO $BBDD, int $gameId): int
{
    $stmt = $BBDD->prepare(
        "SELECT COUNT(*) AS negative_count
         FROM Valoraciones V
         INNER JOIN Juegos J ON V.nombre_juego = J.nombre_juego
         WHERE J.id_juego = ? AND V.valoracion = 'negativa'"
    );

    if ($stmt === false) {
        return 0;
    }

    $stmt->execute([$gameId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)($result['negative_count'] ?? 0);
}

function getUserLanguages(PDO $BBDD, string $nickname): array
{
    $stmt = $BBDD->prepare(
        "SELECT id_idioma_principal, id_idioma_secundario
         FROM Usuarios
         WHERE nickname = ?
         LIMIT 1"
    );

    if ($stmt === false) {
        return [
            'id_idioma_principal' => '',
            'id_idioma_secundario' => ''
        ];
    }

    $stmt->execute([$nickname]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: [
        'id_idioma_principal' => '',
        'id_idioma_secundario' => ''
    ];
}
