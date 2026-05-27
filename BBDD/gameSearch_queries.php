<?php
declare(strict_types=1);

if (!function_exists('wishlist_e')) {
    function wishlist_e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

function shop_get_games(PDO $BBDD, string $search, string $order, array $genre, array $languages, float $minPrice, float $maxPrice, bool $discount, bool $recent, string $minDate, string $maxDate): array
{
    $allowedOrders = [
        'ninguno'      => '',
        'aleatorio'    => 'ORDER BY RAND()',
        'nombre(↑)'    => 'ORDER BY J.nombre_juego ASC',
        'precio(↑)'    => 'ORDER BY COALESCE(J.precio, 0) DESC, J.nombre_juego ASC',
        'descuento(↑)' => 'ORDER BY COALESCE(J.descuento, 0) DESC, J.nombre_juego ASC',
        'fecha(↑)'     => 'ORDER BY J.fecha_publicacion DESC, J.nombre_juego ASC',
        'resenas(↑)'   => 'ORDER BY COALESCE(V.total_resenas, 0) DESC, J.nombre_juego ASC',
        'positivas'    => 'ORDER BY COALESCE(((V.positivas / NULLIF(V.total_resenas, 0)) * 100), 0) DESC, J.nombre_juego ASC',
        'nombre(↓)'    => 'ORDER BY J.nombre_juego DESC',
        'precio(↓)'    => 'ORDER BY COALESCE(J.precio, 0) ASC, J.nombre_juego ASC',
        'descuento(↓)' => 'ORDER BY COALESCE(J.descuento, 0) ASC, J.nombre_juego ASC',
        'fecha(↓)'     => 'ORDER BY J.fecha_publicacion ASC, J.nombre_juego ASC',
        'resenas(↓)'   => 'ORDER BY COALESCE(V.total_resenas, 0) ASC, J.nombre_juego ASC',
        'negativas'    => 'ORDER BY COALESCE(((V.positivas / NULLIF(V.total_resenas, 0)) * 100), 0) ASC, J.nombre_juego ASC',
    ];

    if ($minPrice > $maxPrice) {
        $maxPrice = $minPrice;
    }

    $onlyGetDiscounts = $discount ? 'AND COALESCE(J.descuento, 0) > 0' : '';

    if ($recent) {
        $lastMonth = (int) date('n') - 1;
        $lastYear = (int) date('Y');

        if ($lastMonth == 0) {
            $lastMonth = 12;
            $lastYear -= 1;
        }

        $daysInPreviousMonth = cal_days_in_month(CAL_GREGORIAN, $lastMonth, $lastYear);
        $daysToReduce = (int) date('d') + $daysInPreviousMonth - 1;

        $date = date("Y-m-d H:i:s", time() - ($daysToReduce * 24 * 3600));

        $minDate2 = "AND J.fecha_publicacion >= '" . $date . "'";
        $maxDate2 = '';
    } else {
        if ($minDate !== null && $minDate !== "") {
            $minDate2 = "AND J.fecha_publicacion >= '" . $minDate . " 00:00:00'";
        } else {
            $minDate2 = '';
        }

        if ($maxDate !== null && $maxDate !== "") {
            $maxDate2 = "AND J.fecha_publicacion <= '" . $maxDate . " 23:59:59'";
        } else {
            $maxDate2 = '';
        }
    }

    $genreConditions = '';
    $languageConditions = '';
    $queryParams = [
        ':search' => '%' . $search . '%',
        ':minPrice' => $minPrice,
        ':maxPrice' => $maxPrice,
    ];

    if (!empty($genre)) {
        $genrePlaceholders = [];
        foreach ($genre as $index => $newAllowedGenre) {
            $placeholder = ':genre' . $index;
            $genrePlaceholders[] = $placeholder;
            $queryParams[$placeholder] = $newAllowedGenre;
        }
        $genreConditions = 'AND C.categoria IN (' . implode(', ', $genrePlaceholders) . ')';
    }

    if (!empty($languages)) {
        $languagePlaceholders = [];
        foreach ($languages as $index => $newAllowedLanguage) {
            $placeholder = ':language' . $index;
            $languagePlaceholders[] = $placeholder;
            $queryParams[$placeholder] = $newAllowedLanguage;
        }
        $languageConditions = 'AND I.id_idioma IN (' . implode(', ', $languagePlaceholders) . ')';
    }

    $orderBy = $allowedOrders[$order] ?? $allowedOrders['ninguno'];

    $sqlSearchGames = "
        SELECT DISTINCT
            J.id_juego,
            J.nombre_juego,
            J.descripcion,
            J.fecha_publicacion,
            J.desarrollador,
            COALESCE(J.precio, 0) AS precio,
            COALESCE(J.descuento, 0) AS descuento,
            COALESCE(V.total_resenas, 0) AS total_resenas,
            COALESCE(V.positivas, 0) AS positivas,
            COALESCE(V.negativas, 0) AS negativas,
            COALESCE(((V.positivas / NULLIF(V.total_resenas, 0)) * 100), 0) AS ratioPositivas
        FROM Juegos AS J
        LEFT JOIN (
            SELECT
                nombre_juego,
                COUNT(*) AS total_resenas,
                SUM(CASE WHEN valoracion = 'positiva' THEN 1 ELSE 0 END) AS positivas,
                SUM(CASE WHEN valoracion = 'negativa' THEN 1 ELSE 0 END) AS negativas
            FROM Valoraciones
            GROUP BY nombre_juego
        ) V ON V.nombre_juego = J.nombre_juego
        LEFT JOIN CategoriasJuego AS C ON C.nombre_juego = J.nombre_juego
        LEFT JOIN IdiomasJuego AS I ON I.nombre_juego = J.nombre_juego
        WHERE (J.nombre_juego LIKE :search OR J.desarrollador LIKE :search)
          {$genreConditions}
          {$languageConditions}
          AND (COALESCE(J.precio, 0) - (COALESCE(J.precio, 0) * (COALESCE(J.descuento, 0) / 100))) >= :minPrice
          AND (COALESCE(J.precio, 0) - (COALESCE(J.precio, 0) * (COALESCE(J.descuento, 0) / 100))) <= :maxPrice
          {$onlyGetDiscounts}
          {$minDate2}
          {$maxDate2}
        {$orderBy}
    ";

    $stmt = $BBDD->prepare($sqlSearchGames);
    $stmt->execute($queryParams);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_genres(PDO $BBDD): array
{
    $sqlSearchCategories = "
        SELECT
            C.id_categoria,
            C.categoria AS Categoria
        FROM Categorias AS C
    ";

    $stmt = $BBDD->prepare($sqlSearchCategories);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_languages(PDO $BBDD): array
{
    $sqlSearchCategories = "
        SELECT
            I.id_idioma,
            I.idioma AS Idioma
        FROM Idiomas AS I
    ";

    $stmt = $BBDD->prepare($sqlSearchCategories);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>