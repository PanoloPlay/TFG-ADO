<?php
declare(strict_types=1);

if (!function_exists('wishlist_e')) {
    function wishlist_e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

function wishlist_get_items(PDO $BBDD, int $idUsuario, string $nickname, string $orden = 'usuario'): array
{
    $ordenesPermitidos = [
        'usuario'      => 'LD.numero_orden ASC, J.nombre_juego ASC',
        'aleatorio'    => 'RAND()',
        'nombre(↑)'    => 'J.nombre_juego ASC',
        'nombre(↓)'    => 'J.nombre_juego DESC',
        'precio(↑)'    => 'COALESCE(J.precio, 0) DESC, J.nombre_juego ASC',
        'precio(↓)'    => 'COALESCE(J.precio, 0) ASC, J.nombre_juego ASC',
        'descuento(↑)' => 'COALESCE(J.descuento, 0) DESC, J.nombre_juego ASC',
        'descuento(↓)' => 'COALESCE(J.descuento, 0) ASC, J.nombre_juego ASC',
        'fecha(↑)'     => 'J.fecha_publicacion DESC, J.nombre_juego ASC',
        'fecha(↓)'     => 'J.fecha_publicacion ASC, J.nombre_juego ASC',
        'resenas(↑)'   => 'COALESCE(V.total_resenas, 0) DESC, J.nombre_juego ASC',
        'resenas(↓)'   => 'COALESCE(V.total_resenas, 0) ASC, J.nombre_juego ASC',
        'positivas'    => 'COALESCE(((V.positivas / V.total_resenas) * 100), 0) DESC, J.nombre_juego ASC',
        'negativas'    => 'COALESCE(((V.positivas / V.total_resenas) * 100), 0) ASC, J.nombre_juego ASC',
    ];

    $orderBy = $ordenesPermitidos[$orden] ?? $ordenesPermitidos['usuario'];

    $sql = "
        SELECT
            LD.id_Carrito,
            LD.numero_orden,
            LD.id_juego,
            LD.nombre_juego,
            J.descripcion,
            J.fecha_publicacion,
            J.desarrollador,
            J.precio,
            J.descuento,
            COALESCE(V.total_resenas, 0) AS total_resenas,
            COALESCE(V.positivas, 0) AS positivas,
            COALESCE(V.negativas, 0) AS negativas
        FROM Carrito LD
        INNER JOIN Juegos J
            ON J.id_juego = LD.id_juego
           AND J.nombre_juego = LD.nombre_juego
        LEFT JOIN (
            SELECT
                nombre_juego,
                COUNT(*) AS total_resenas,
                SUM(CASE WHEN valoracion = 'positiva' THEN 1 ELSE 0 END) AS positivas,
                SUM(CASE WHEN valoracion = 'negativa' THEN 1 ELSE 0 END) AS negativas
            FROM Valoraciones
            GROUP BY nombre_juego
        ) V ON V.nombre_juego = LD.nombre_juego
        WHERE LD.id_usuario = :id_usuario
          AND LD.nickname = :nickname
        ORDER BY {$orderBy}
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $idUsuario,
        ':nickname' => $nickname,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function wishlist_reorder(PDO $BBDD, int $idUsuario, string $nickname, array $wishlistIds): void
{
    $wishlistIds = array_values(array_filter(array_map('intval', $wishlistIds), static fn ($v) => $v > 0));

    $BBDD->beginTransaction();

    try {
        $sqlVerify = "
            SELECT id_Carrito
            FROM Carrito
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND id_Carrito = :id_Carrito
            LIMIT 1
        ";
        $stmtVerify = $BBDD->prepare($sqlVerify);

        $sqlUpdate = "
            UPDATE Carrito
            SET numero_orden = :numero_orden
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND id_Carrito = :id_Carrito
        ";
        $stmtUpdate = $BBDD->prepare($sqlUpdate);

        $orden = 1;
        foreach ($wishlistIds as $idWishlist) {
            $stmtVerify->execute([
                ':id_usuario' => $idUsuario,
                ':nickname' => $nickname,
                ':id_Carrito' => $idWishlist,
            ]);

            if (!$stmtVerify->fetchColumn()) {
                throw new RuntimeException('Uno de los elementos no pertenece a tu lista de deseos.');
            }

            $stmtUpdate->execute([
                ':numero_orden' => $orden,
                ':id_usuario' => $idUsuario,
                ':nickname' => $nickname,
                ':id_Carrito' => $idWishlist,
            ]);
            $orden++;
        }

        $BBDD->commit();
    } catch (Throwable $e) {
        if ($BBDD->inTransaction()) {
            $BBDD->rollBack();
        }
        throw $e;
    }
}

function cart_delete_item(PDO $BBDD, int $idUsuario, string $nickname, int $idWishlist): void
{
    $BBDD->beginTransaction();

    try {
        $sqlCurrent = "
            SELECT numero_orden
            FROM Carrito
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND id_Carrito = :id_Carrito
            FOR UPDATE
        ";
        $stmtCurrent = $BBDD->prepare($sqlCurrent);
        $stmtCurrent->execute([
            ':id_usuario' => $idUsuario,
            ':nickname' => $nickname,
            ':id_Carrito' => $idWishlist,
        ]);
        $row = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new RuntimeException('El juego no existe en tu lista de deseos.');
        }

        $ordenActual = (int) $row['numero_orden'];

        $sqlDelete = "
            DELETE FROM Carrito
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND id_Carrito = :id_Carrito
        ";
        $stmtDelete = $BBDD->prepare($sqlDelete);
        $stmtDelete->execute([
            ':id_usuario' => $idUsuario,
            ':nickname' => $nickname,
            ':id_Carrito' => $idWishlist,
        ]);

        $sqlReindex = "
            UPDATE Carrito
            SET numero_orden = numero_orden - 1
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND numero_orden > :numero_orden
        ";
        $stmtReindex = $BBDD->prepare($sqlReindex);
        $stmtReindex->execute([
            ':id_usuario' => $idUsuario,
            ':nickname' => $nickname,
            ':numero_orden' => $ordenActual,
        ]);

        $BBDD->commit();
    } catch (Throwable $e) {
        if ($BBDD->inTransaction()) {
            $BBDD->rollBack();
        }
        throw $e;
    }
}

function wishlist_move_item(PDO $BBDD, int $idUsuario, string $nickname, int $idWishlist, string $direction): void
{
    if (!in_array($direction, ['up', 'down'], true)) {
        throw new InvalidArgumentException('Dirección de movimiento no válida.');
    }

    $BBDD->beginTransaction();

    try {
        $sqlCurrent = "
            SELECT numero_orden
            FROM Carrito
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND id_Carrito = :id_Carrito
            FOR UPDATE
        ";
        $stmtCurrent = $BBDD->prepare($sqlCurrent);
        $stmtCurrent->execute([
            ':id_usuario' => $idUsuario,
            ':nickname' => $nickname,
            ':id_Carrito' => $idWishlist,
        ]);
        $current = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

        if (!$current) {
            throw new RuntimeException('El juego no existe en tu lista de deseos.');
        }

        $ordenActual = (int) $current['numero_orden'];
        $comparador = $direction === 'up' ? '<' : '>';
        $ordenVecino = $direction === 'up' ? 'DESC' : 'ASC';

        $sqlNeighbor = "
            SELECT id_Carrito, numero_orden
            FROM Carrito
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND numero_orden {$comparador} :numero_orden
            ORDER BY numero_orden {$ordenVecino}
            LIMIT 1
            FOR UPDATE
        ";
        $stmtNeighbor = $BBDD->prepare($sqlNeighbor);
        $stmtNeighbor->execute([
            ':id_usuario' => $idUsuario,
            ':nickname' => $nickname,
            ':numero_orden' => $ordenActual,
        ]);
        $neighbor = $stmtNeighbor->fetch(PDO::FETCH_ASSOC);

        if (!$neighbor) {
            $BBDD->commit();
            return;
        }

        $idWishlistNeighbor = (int) $neighbor['id_Carrito'];
        $ordenNeighbor = (int) $neighbor['numero_orden'];

        // Temporal para evitar colisiones con una restricción única futura.
        $sqlTmp = "
            UPDATE Carrito
            SET numero_orden = -1
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND id_Carrito = :id_Carrito
        ";
        $stmtTmp = $BBDD->prepare($sqlTmp);
        $stmtTmp->execute([
            ':id_usuario' => $idUsuario,
            ':nickname' => $nickname,
            ':id_Carrito' => $idWishlist,
        ]);

        $sqlSetNeighbor = "
            UPDATE Carrito
            SET numero_orden = :numero_orden
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND id_Carrito = :id_Carrito
        ";
        $stmtSetNeighbor = $BBDD->prepare($sqlSetNeighbor);
        $stmtSetNeighbor->execute([
            ':numero_orden' => $ordenActual,
            ':id_usuario' => $idUsuario,
            ':nickname' => $nickname,
            ':id_Carrito' => $idWishlistNeighbor,
        ]);

        $sqlSetCurrent = "
            UPDATE Carrito
            SET numero_orden = :numero_orden
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND id_Carrito = :id_Carrito
        ";
        $stmtSetCurrent = $BBDD->prepare($sqlSetCurrent);
        $stmtSetCurrent->execute([
            ':numero_orden' => $ordenNeighbor,
            ':id_usuario' => $idUsuario,
            ':nickname' => $nickname,
            ':id_Carrito' => $idWishlist,
        ]);

        $BBDD->commit();
    } catch (Throwable $e) {
        if ($BBDD->inTransaction()) {
            $BBDD->rollBack();
        }
        throw $e;
    }
}

function cartBuyOne(PDO $BBDD, int $idUsuario, string $nickname, string $nombre_juego, int $idWishlist): void
{
    $BBDD->beginTransaction();

    try {

        $sqlCurrentCart = "
            SELECT id_usuario, nickname, id_juego, nombre_juego, numero_orden
            FROM Carrito
            WHERE id_usuario = :id_usuario
              AND nombre_juego = :nombre_juego
              AND nickname = :nickname
              AND id_Carrito = :id_Carrito
            FOR UPDATE
        ";
        $stmtCurrentCart = $BBDD->prepare($sqlCurrentCart);
        $stmtCurrentCart->execute([
            ':id_usuario' => $idUsuario,
            ':nombre_juego' => $nombre_juego,
            ':nickname' => $nickname,
            ':id_Carrito' => $idWishlist,
        ]);
        $rowCart = $stmtCurrentCart->fetch(PDO::FETCH_ASSOC);

        if (!$rowCart) {
            throw new RuntimeException('El juego no existe en tu lista de deseos.');
        }

        $ordenActualCart = (int) $rowCart['numero_orden'];

        $sqlDeleteCart = "
            DELETE FROM Carrito
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND nombre_juego = :nombre_juego
              AND id_Carrito = :id_Carrito
        ";
        $stmtDeleteCart = $BBDD->prepare($sqlDeleteCart);
        $stmtDeleteCart->execute([
            ':id_usuario' => $rowCart['id_usuario'],
            ':nickname' => $rowCart['nickname'],
            ':nombre_juego' => $rowCart['nombre_juego'],
            ':id_Carrito' => $idWishlist,
        ]);

        $sqlReindexCart = "
            UPDATE Carrito
            SET numero_orden = numero_orden - 1
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND nombre_juego = :nombre_juego
              AND numero_orden > :numero_orden
        ";
        $stmtReindexCart = $BBDD->prepare($sqlReindexCart);
        $stmtReindexCart->execute([
            ':id_usuario' => $rowCart['id_usuario'],
            ':nickname' => $rowCart['nickname'],
            ':nombre_juego' => $rowCart['nombre_juego'],
            ':numero_orden' => $ordenActualCart,
        ]);

        $sqlCurrentWishlist = "
            SELECT id_Wishlist, id_usuario, nickname, id_juego, nombre_juego, numero_orden
            FROM ListaDeseos
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
              AND id_juego = :id_juego
              AND nombre_juego = :nombre_juego
            FOR UPDATE
        ";
        $stmtCurrentWishlist = $BBDD->prepare($sqlCurrentWishlist);
        $stmtCurrentWishlist->execute([
            ':id_usuario' => $rowCart['id_usuario'],
            ':nickname' => $rowCart['nickname'],
            ':id_juego' => $rowCart['id_juego'],
            ':nombre_juego' => $rowCart['nombre_juego']
        ]);
        $rowWishlist = $stmtCurrentWishlist->fetch(PDO::FETCH_ASSOC);
        
        if ($rowWishlist) {
            $ordenActualWishlist = (int) $rowWishlist['numero_orden'];

            $sqlDeleteWishlist = "
                DELETE FROM ListaDeseos
                WHERE id_usuario = :id_usuario
                AND nickname = :nickname
                AND nombre_juego = :nombre_juego
                AND id_Wishlist = :id_Wishlist
            ";
            $stmtDeleteWishlist = $BBDD->prepare($sqlDeleteWishlist);
            $stmtDeleteWishlist->execute([
                ':id_usuario' => $rowWishlist['id_usuario'],
                ':nickname' => $rowWishlist['nickname'],
                ':nombre_juego' => $rowWishlist['nombre_juego'],
                ':id_Wishlist' => $rowWishlist['id_Wishlist'],
            ]);

            echo "Iniciando compaaaaaaaaaaara de un juego: $nombre_juego (ID Wishlist: $idWishlist)"; // Debugging line


            $sqlReindexWishlist = "
                UPDATE ListaDeseos
                SET numero_orden = numero_orden - 1
                WHERE id_usuario = :id_usuario
                AND nickname = :nickname
                AND numero_orden > :numero_orden
            ";
            $stmtReindexWishlist = $BBDD->prepare($sqlReindexWishlist);
            $stmtReindexWishlist->execute([
                ':id_usuario' => $rowWishlist['id_usuario'],
                ':nickname' => $rowWishlist['nickname'],
                ':numero_orden' => $ordenActualWishlist,
            ]);
        }

        $sqlInsertLibrary = "
            INSERT INTO Biblioteca (id_usuario, nickname, id_juego, nombre_juego)
            VALUES (:id_usuario, :nickname, :id_juego, :nombre_juego)
        ";
        $stmtInsertLibrary = $BBDD->prepare($sqlInsertLibrary);
        $stmtInsertLibrary->execute([
            ':id_usuario' => $rowCart['id_usuario'],
            ':nickname' => $rowCart['nickname'],
            ':id_juego' => $rowCart['id_juego'],
            ':nombre_juego' => $rowCart['nombre_juego']
        ]);

        $BBDD->commit();
    } catch (Throwable $e) {
        echo "Error: " . $e->getMessage(); // Debugging line
        if ($BBDD->inTransaction()) {
            $BBDD->rollBack();
        }
        throw $e;
    }
}

function cartBuyAll(PDO $BBDD, int $idUsuario, string $nickname): void
{
    $BBDD->beginTransaction();

    try {
        $sqlCartItems = "
            SELECT id_usuario, nickname, id_juego, nombre_juego
            FROM Carrito
            WHERE id_usuario = :id_usuario
              AND nickname = :nickname
            FOR UPDATE
        ";
        $stmtCartItems = $BBDD->prepare($sqlCartItems);
        $stmtCartItems->execute([
            ':id_usuario' => $idUsuario,
            ':nickname' => $nickname,
        ]);

        $cartItems = $stmtCartItems->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cartItems as $item) {

            $sqlDeleteCart = "
                DELETE FROM Carrito
                WHERE id_usuario = :id_usuario
                  AND nickname = :nickname
                  AND id_juego = :id_juego
                  AND nombre_juego = :nombre_juego
            ";
            $stmtDeleteCart = $BBDD->prepare($sqlDeleteCart);
            $stmtDeleteCart->execute([
                ':id_usuario' => $item['id_usuario'],
                ':nickname' => $item['nickname'],
                ':id_juego' => $item['id_juego'],
                ':nombre_juego' => $item['nombre_juego']
            ]);

            $sqlDeleteWishlist = "
                DELETE FROM ListaDeseos
                WHERE id_usuario = :id_usuario
                  AND nickname = :nickname
                  AND id_juego = :id_juego
                  AND nombre_juego = :nombre_juego
            ";
            $stmtDeleteWishlist = $BBDD->prepare($sqlDeleteWishlist);
            $stmtDeleteWishlist->execute([
                ':id_usuario' => $item['id_usuario'],
                ':nickname' => $item['nickname'],
                ':id_juego' => $item['id_juego'],
                ':nombre_juego' => $item['nombre_juego']
            ]);

            $sqlInsertLibrary = "
                INSERT INTO Biblioteca (id_usuario, nickname, id_juego, nombre_juego)
                VALUES (:id_usuario, :nickname, :id_juego, :nombre_juego)
            ";
            $stmtInsertLibrary = $BBDD->prepare($sqlInsertLibrary);
            $stmtInsertLibrary->execute([
                ':id_usuario' => $item['id_usuario'],
                ':nickname' => $item['nickname'],
                ':id_juego' => $item['id_juego'],
                ':nombre_juego' => $item['nombre_juego']
            ]);
        }

        $BBDD->commit();
    } catch (Throwable $e) {
        if ($BBDD->inTransaction()) {
            $BBDD->rollBack();
        }
        throw $e;
    }
}
