<?php

function getFriendsCode(PDO $BBDD, int $idUsuario, string $nickname): ?string
{
    $query = $BBDD->prepare("SELECT clave_amigos FROM Usuarios WHERE id_usuario = ? AND nickname = ? LIMIT 1");
    $query->execute([$idUsuario, $nickname]);
    return $query->fetchColumn() ?: null;
}

function getUserByFriendsCode(PDO $BBDD, int $friendsCode): ?array
{
    $query = $BBDD->prepare("SELECT id_usuario, nombre_usuario, nickname, descripcion FROM Usuarios WHERE clave_amigos = ? LIMIT 1");
    $query->execute([$friendsCode]);
    return $query->fetch(PDO::FETCH_ASSOC) ?: null;
}

function searchUsersByNickname(PDO $BBDD, string $searchText): array
{
    $searchTerm = '%' . $searchText . '%';
    $query = $BBDD->prepare("SELECT id_usuario, nombre_usuario, nickname, descripcion FROM Usuarios WHERE nickname LIKE ? ORDER BY nickname ASC LIMIT 10");
    $query->execute([$searchTerm]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getAcceptedFriends(PDO $BBDD, int $idUsuario, string $nickname): array
{
    $query = $BBDD->prepare("
        SELECT CASE WHEN id_usuario1 = ? THEN nickname2 ELSE nickname1 END AS amigo_nickname
        FROM Amigos
        WHERE ((id_usuario1 = ? AND nickname1 = ?) OR (id_usuario2 = ? AND nickname2 = ?)) AND estado = 'aceptada'
        ORDER BY amigo_nickname ASC
    ");
    $query->execute([$idUsuario, $idUsuario, $nickname, $idUsuario, $nickname]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function friendshipExists(PDO $BBDD, int $idUsuario1, int $idUsuario2): bool
{
    $query = $BBDD->prepare("
        SELECT 1
        FROM Amigos
        WHERE (id_usuario1 = ? AND id_usuario2 = ?) OR (id_usuario1 = ? AND id_usuario2 = ?)
        LIMIT 1
    ");
    $query->execute([$idUsuario1, $idUsuario2, $idUsuario2, $idUsuario1]);
    return (bool) $query->fetchColumn();
}

function getFriendUsers(PDO $BBDD, int $idUsuario, string $nickname): array
{
    $result = [];

    $query = $BBDD->prepare(
        "SELECT u.id_usuario, u.nombre_usuario, u.nickname, u.descripcion
         FROM Amigos a
         JOIN Usuarios u ON u.id_usuario = a.id_usuario2 AND u.nickname = a.nickname2
         WHERE a.id_usuario1 = ? AND a.nickname1 = ? AND a.estado = 'aceptada'
         ORDER BY u.nickname ASC"
    );
    $query->execute([$idUsuario, $nickname]);
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    $query = $BBDD->prepare(
        "SELECT u.id_usuario, u.nombre_usuario, u.nickname, u.descripcion
         FROM Amigos a
         JOIN Usuarios u ON u.id_usuario = a.id_usuario1 AND u.nickname = a.nickname1
         WHERE a.id_usuario2 = ? AND a.nickname2 = ? AND a.estado = 'aceptada'
         ORDER BY u.nickname ASC"
    );
    $query->execute([$idUsuario, $nickname]);
    $result = array_merge($result, $query->fetchAll(PDO::FETCH_ASSOC));

    usort($result, function ($a, $b) {
        return strcasecmp($a['nickname'], $b['nickname']);
    });

    return $result;
}

function getSentFriendRequests(PDO $BBDD, int $idUsuario, string $nickname): array
{
    $query = $BBDD->prepare(
        "SELECT a.id_amistad, u.id_usuario, u.nombre_usuario, u.nickname, u.descripcion
         FROM Amigos a
         JOIN Usuarios u ON u.id_usuario = a.id_usuario2 AND u.nickname = a.nickname2
         WHERE a.id_usuario1 = ? AND a.nickname1 = ? AND a.estado = 'pendiente'
         ORDER BY u.nickname ASC"
    );
    $query->execute([$idUsuario, $nickname]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getReceivedFriendRequests(PDO $BBDD, int $idUsuario, string $nickname): array
{
    $query = $BBDD->prepare(
        "SELECT a.id_amistad, u.id_usuario, u.nombre_usuario, u.nickname, u.descripcion
         FROM Amigos a
         JOIN Usuarios u ON u.id_usuario = a.id_usuario1 AND u.nickname = a.nickname1
         WHERE a.id_usuario2 = ? AND a.nickname2 = ? AND a.estado = 'pendiente'
         ORDER BY u.nickname ASC"
    );
    $query->execute([$idUsuario, $nickname]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function acceptedFriendshipExists(PDO $BBDD, int $idUsuario1, int $idUsuario2): bool
{
    $query = $BBDD->prepare(
        "SELECT 1 FROM Amigos
         WHERE ((id_usuario1 = ? AND id_usuario2 = ?) OR (id_usuario1 = ? AND id_usuario2 = ?))
           AND estado = 'aceptada'
         LIMIT 1"
    );
    $query->execute([$idUsuario1, $idUsuario2, $idUsuario2, $idUsuario1]);
    return (bool) $query->fetchColumn();
}

function acceptFriendRequest(PDO $BBDD, int $idUsuario, int $idAmistad): bool
{
    $query = $BBDD->prepare(
        "SELECT id_usuario1, id_usuario2
         FROM Amigos
         WHERE id_amistad = ? AND id_usuario2 = ? AND estado = 'pendiente'
         LIMIT 1"
    );
    $query->execute([$idAmistad, $idUsuario]);
    $amistad = $query->fetch(PDO::FETCH_ASSOC);

    if (!$amistad) {
        return false;
    }

    $idUsuario1 = (int) $amistad['id_usuario1'];
    $idUsuario2 = (int) $amistad['id_usuario2'];

    if (acceptedFriendshipExists($BBDD, $idUsuario1, $idUsuario2)) {
        $delete = $BBDD->prepare(
            "DELETE FROM Amigos
             WHERE id_amistad = ? AND id_usuario2 = ? AND estado = 'pendiente'"
        );
        $delete->execute([$idAmistad, $idUsuario]);
        return false;
    }

    $query = $BBDD->prepare(
        "UPDATE Amigos
         SET estado = 'aceptada'
         WHERE id_amistad = ? AND id_usuario2 = ? AND estado = 'pendiente'"
    );
    return $query->execute([$idAmistad, $idUsuario]);
}

function deleteFriendRequest(PDO $BBDD, int $idUsuario, int $idAmistad): bool
{
    $query = $BBDD->prepare(
        "DELETE FROM Amigos
         WHERE id_amistad = ?
           AND estado = 'pendiente'
           AND (
               (id_usuario2 = ?)
               OR (id_usuario1 = ?)
           )"
    );
    return $query->execute([$idAmistad, $idUsuario, $idUsuario]);
}

?>
