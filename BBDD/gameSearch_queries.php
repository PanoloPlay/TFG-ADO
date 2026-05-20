<?php
declare(strict_types=1);

if (!function_exists('wishlist_e')) {
    function wishlist_e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

function shop_get_games(PDO $BBDD, string $creator = '', string $order = 'ninguno', array $genre = [], array $languages, float $minPrice = 0, float $maxPrice = 999, bool $discount, bool $recent, string $minDate, string $maxDate): array
{
    $allowedOrders = [
        'ninguno'        => '',
        'aleatorio'       => 'ORDER BY rand()',
        'nombre(↑)'    => 'ORDER BY J.nombre_juego ASC',
        'precio(↑)'    => 'ORDER BY J.precio DESC, J.nombre_juego ASC',
        'descuento(↑)' => 'ORDER BY J.descuento DESC, J.nombre_juego ASC',
        'fecha(↑)'     => 'ORDER BY J.fecha_publicacion DESC, J.nombre_juego ASC',
        'resenas(↑)'   => 'ORDER BY COALESCE(V.total_resenas, 0) DESC, J.nombre_juego ASC',
        'positivas'    => 'ORDER BY COALESCE(((V.positivas / V.total_resenas) * 100), 0) DESC, J.nombre_juego ASC',
        'nombre(↓)'   => 'ORDER BY J.nombre_juego DESC',
        'precio(↓)'   => 'ORDER BY J.precio ASC, J.nombre_juego ASC',
        'descuento(↓)'=> 'ORDER BY J.descuento ASC, J.nombre_juego ASC',
        'fecha(↓)'    => 'ORDER BY J.fecha_publicacion ASC, J.nombre_juego ASC',
        'resenas(↓)'  => 'ORDER BY COALESCE(V.total_resenas, 0) ASC, J.nombre_juego ASC',
        'negativas'    => 'ORDER BY COALESCE(((V.positivas / V.total_resenas) * 100), 0) ASC, J.nombre_juego ASC',
    ];

    $allowedGenre = '';
    $allowedLanguage = '';
    $anyForcedGenre = false;
    $anyForcedLanguage = false;

    if ($minPrice > $maxPrice) {
        $maxPrice = $minPrice;
    }

    if ($discount) {
        $onlyGetDiscounts = 'AND J.descuento > 0';
    }
    else {
        $onlyGetDiscounts = '';
    }

    if ($recent) {
        $lastMonth = (int) date('n') - 1;
        $lastYear = (int) date('Y');
        if ($lastMonth == 0) {
            $lastMonth = 12;
            $lastYear -= 1;
        }
        $daysInPreviousMonth = cal_days_in_month(CAL_GREGORIAN,$lastMonth,$lastYear);

        $daysToReduce = (int) date('d') + $daysInPreviousMonth - 1;

        $date = date("Y-m-d H:i:s", time() - ($daysToReduce * 24 * 3600));
        
        $minDate2 = "AND J.fecha_publicacion >= '" . $date . "'";
        $maxDate2 = '';
    }
    else {
        if ($minDate != null && $minDate != "") {
            $minDate2 = "AND J.fecha_publicacion >= '" . $minDate . " 00:00:00'";
        }
        else {
            $minDate2 = '';
        }

        if ($maxDate != null && $maxDate != "") {
            $maxDate2 = "AND J.fecha_publicacion <= '" . $maxDate . " 00:00:00'";;
        }
        else {
            $maxDate2 = '';
        }
    }

    foreach ($genre as $newAllowedGenre) {
        if ($anyForcedGenre) {
            $allowedGenre .= " OR C.categoria = '" . $newAllowedGenre . "'";
        }
        else {
            $allowedGenre .= "AND (C.categoria = '" . $newAllowedGenre . "'";
            $anyForcedGenre = true;
        }
    }

    if ($allowedGenre != '') {
        $allowedGenre .= ")";
    }

    foreach ($languages as $newAllowedLanguage) {
        if ($anyForcedLanguage) {
            $allowedLanguage .= " OR I.id_idioma  = '" . $newAllowedLanguage . "'";
        }
        else {
            $allowedLanguage .= "AND (I.id_idioma  = '" . $newAllowedLanguage . "'";
            $anyForcedLanguage = true;
        }
    }

    if ($allowedLanguage != '') {
        $allowedLanguage .= ")";
    }

    $orderBy = $allowedOrders[$order] ?? $allowedOrders['ninguno'];

    $creatorTerm = '%' . $creator . '%';

    $sqlSearchGames = "
        SELECT
            J.id_juego,
            J.nombre_juego,
            J.descripcion,
            J.fecha_publicacion,
            J.desarrollador,
            J.precio,
            J.descuento,
            COALESCE(V.total_resenas, 0) AS total_resenas,
            COALESCE(V.positivas, 0) AS positivas,
            COALESCE(V.negativas, 0) AS negativas,
            COALESCE(((V.positivas / V.total_resenas) * 100), 0) AS ratioPositivas
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
        WHERE J.desarrollador LIKE :creator
          AND J.nombre_juego in (
            SELECT 
            C.nombre_juego
            FROM Categoriasjuego AS C
            WHERE 
                C.nombre_juego = J.nombre_juego
                {$allowedGenre}
          )
          AND J.nombre_juego in (
            SELECT I.nombre_juego
            FROM Idiomasjuego AS I
            WHERE 
                I.nombre_juego = J.nombre_juego
                {$allowedLanguage}
          )
          AND (J.precio - (J.precio * (J.descuento / 100))) >= {$minPrice}
          AND (J.precio - (J.precio * (J.descuento / 100))) <= {$maxPrice}
          {$onlyGetDiscounts}
          {$minDate2}
          {$maxDate2}
        {$orderBy}
    ";
    $stmt = $BBDD->prepare($sqlSearchGames);
        
    $stmt->execute([
        ':creator' => $creatorTerm,
    ]);

    return ($games = $stmt->fetchAll(PDO::FETCH_ASSOC));
}

function get_genres(PDO $BBDD): array
{
    $sqlSearchCategories = "
        SELECT
            C.id_categoria,
            C.Categoria
        FROM Categorias AS C
    ";

    $stmt = $BBDD->prepare($sqlSearchCategories);
        
    $stmt->execute();

    return ($categories = $stmt->fetchAll(PDO::FETCH_ASSOC));
}

function get_languages(PDO $BBDD): array
{
    $sqlSearchCategories = "
        SELECT
            I.id_idioma,
            I.Idioma
        FROM Idiomas AS I
    ";

    $stmt = $BBDD->prepare($sqlSearchCategories);
        
    $stmt->execute();

    return ($categories = $stmt->fetchAll(PDO::FETCH_ASSOC));
}

?>