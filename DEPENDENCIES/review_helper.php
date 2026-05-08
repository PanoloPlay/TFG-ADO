<?php

if (!function_exists('renderReviewBadge')) {
    function renderReviewBadge(array $juego): string
    {
        $total = (int)($juego['total_resenas'] ?? 0);
        $positivas = (int)($juego['positivas'] ?? 0);

        $porcentaje = $total > 0 ? ($positivas / $total) * 100 : 0;

        if ($total === 0) {
            $texto = 'Sin reseñas';
            $clase = 'review-mixed';

        } elseif ($porcentaje >= 95 && $total >= 500) {
            $texto = 'Extremadamente positivas';
            $clase = 'review-positive';

        } elseif ($porcentaje >= 80 && $total >= 50) {
            $texto = 'Muy positivas';
            $clase = 'review-positive';

        } elseif ($porcentaje >= 70) {
            $texto = 'Mayormente positivas';
            $clase = 'review-positive';

        } elseif ($porcentaje >= 40) {
            $texto = 'Variadas';
            $clase = 'review-mixed';

        } elseif ($porcentaje >= 20) {
            $texto = 'Mayormente negativas';
            $clase = 'review-negative';

        } elseif ($porcentaje >= 0 && $total < 50) {
            $texto = 'Negativas';
            $clase = 'review-negative';

        } elseif ($porcentaje < 20 && $total < 500) {
            $texto = 'Muy negativas';
            $clase = 'review-negative';

        } else {
            $texto = 'Extremadamente negativas';
            $clase = 'review-negative';
        }

        return '<span class="' . htmlspecialchars($clase, ENT_QUOTES, 'UTF-8') . '" title="' . round($porcentaje) . '% positivas">'
            . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') .
        '</span>';
    }
}