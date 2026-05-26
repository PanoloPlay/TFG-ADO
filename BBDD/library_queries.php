<?php
declare(strict_types=1);

function library_get_items(PDO $BBDD, int $idUsuario, string $nickname, string $orden = 'nombre'): array
{
    $ordenesPermitidos = [
        'ninguno'      => '',
        'aleatorio'    => 'ORDER BY RAND()',
        'nombre(↑)'    => 'ORDER BY J.nombre_juego ASC',
        'nombre(↓)'    => 'ORDER BY J.nombre_juego DESC',
        'precio(↑)'    => 'ORDER BY COALESCE(J.precio, 0) DESC, J.nombre_juego ASC',
        'precio(↓)'    => 'ORDER BY COALESCE(J.precio, 0) ASC, J.nombre_juego ASC',
        'descuento(↑)' => 'ORDER BY COALESCE(J.descuento, 0) DESC, J.nombre_juego ASC',
        'descuento(↓)' => 'ORDER BY COALESCE(J.descuento, 0) ASC, J.nombre_juego ASC',
        'fecha(↑)'     => 'ORDER BY J.fecha_publicacion DESC, J.nombre_juego ASC',
        'fecha(↓)'     => 'ORDER BY J.fecha_publicacion ASC, J.nombre_juego ASC',
        'resenas(↑)'   => 'ORDER BY COALESCE(V.total_resenas, 0) DESC, J.nombre_juego ASC',
        'resenas(↓)'   => 'ORDER BY COALESCE(V.total_resenas, 0) ASC, J.nombre_juego ASC',
        'positivas'    => 'ORDER BY COALESCE(((V.positivas / V.total_resenas) * 100), 0) DESC, J.nombre_juego ASC',
        'negativas'    => 'ORDER BY COALESCE(((V.positivas / V.total_resenas) * 100), 0) ASC, J.nombre_juego ASC',
    ];

    $orderBy = $ordenesPermitidos[$orden] ?? $ordenesPermitidos['nombre(↑)'];

    $sql = "
        SELECT
            B.id_Biblioteca,
            B.id_juego,
            B.nombre_juego,
            J.descripcion,
            J.fecha_publicacion,
            J.desarrollador,
            J.precio,
            J.descuento,
            COALESCE(V.total_resenas, 0) AS total_resenas,
            COALESCE(V.positivas, 0) AS positivas,
            COALESCE(V.negativas, 0) AS negativas
        FROM Biblioteca B
        INNER JOIN Juegos J
            ON J.id_juego = B.id_juego
           AND J.nombre_juego = B.nombre_juego
        LEFT JOIN (
            SELECT
                nombre_juego,
                COUNT(*) AS total_resenas,
                SUM(CASE WHEN valoracion = 'positiva' THEN 1 ELSE 0 END) AS positivas,
                SUM(CASE WHEN valoracion = 'negativa' THEN 1 ELSE 0 END) AS negativas
            FROM Valoraciones
            GROUP BY nombre_juego
        ) V ON V.nombre_juego = B.nombre_juego
        WHERE B.id_usuario = :id_usuario
          AND B.nickname = :nickname
        {$orderBy}
    ";

    $stmt = $BBDD->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $idUsuario,
        ':nickname' => $nickname,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
