<?php

function getGameData($BBDD, $gameId) {
    $stmt = $BBDD->prepare("
        SELECT id_juego, nombre_juego, descripcion, fecha_publicacion, desarrollador, precio, descuento
        FROM Juegos
        WHERE id_juego = ?
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->execute([$gameId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return null;
}

function getGameCategories($BBDD, $gameId) {
    $stmt = $BBDD->prepare("
        SELECT c.categoria AS nombre_categoria
        FROM Categorias c
        INNER JOIN Categorias_Juego cj ON c.id_categoria = cj.id_categoria
        WHERE cj.id_juego = ?
    ");

    if ($stmt) {
        $stmt->execute([$gameId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return [];
}

function getGameComments($BBDD, $gameName) {
    $stmt = $BBDD->prepare("
        SELECT nickname, comentario, valoracion, fechaPublicacion, id_idioma_comentario
        FROM Valoraciones
        WHERE nombre_juego = ?
        ORDER BY fechaPublicacion DESC
        LIMIT 20
    ");

    if ($stmt) {
        $stmt->execute([$gameName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return [];
}

function getPositiveRatingsCount($BBDD, $gameName) {
    $stmt = $BBDD->prepare("
        SELECT COUNT(*) AS positive
        FROM Valoraciones
        WHERE nombre_juego = ? AND valoracion = 'positiva'
    ");

    if ($stmt) {
        $stmt->execute([$gameName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['positive'] ?? 0);
    }

    return 0;
}

function getNegativeRatingsCount($BBDD, $gameName) {
    $stmt = $BBDD->prepare("
        SELECT COUNT(*) AS negative
        FROM Valoraciones
        WHERE nombre_juego = ? AND valoracion = 'negativa'
    ");

    if ($stmt) {
        $stmt->execute([$gameName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['negative'] ?? 0);
    }

    return 0;
}

function hasUserBoughtGame($BBDD, $nickname, $gameName) {
    $stmt = $BBDD->prepare("
        SELECT COUNT(*) AS bought
        FROM Biblioteca
        WHERE nickname = ? AND nombre_juego = ?
    ");

    if ($stmt) {
        $stmt->execute([$nickname, $gameName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ((int)($result['bought'] ?? 0)) > 0;
    }

    return false;
}

function getUserReview($BBDD, $nickname, $gameName) {
    $stmt = $BBDD->prepare("
        SELECT comentario, valoracion
        FROM Valoraciones
        WHERE nickname = ? AND nombre_juego = ?
        ORDER BY fechaPublicacion DESC
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->execute([$nickname, $gameName]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return null;
}

function getUserLanguages($BBDD, $nickname) {
    $stmt = $BBDD->prepare("
        SELECT id_idioma_principal, id_idioma_secundario
        FROM Usuarios
        WHERE nickname = ?
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->execute([$nickname]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: [
            'id_idioma_principal' => '',
            'id_idioma_secundario' => ''
        ];
    }

    return [
        'id_idioma_principal' => '',
        'id_idioma_secundario' => ''
    ];
}
?>