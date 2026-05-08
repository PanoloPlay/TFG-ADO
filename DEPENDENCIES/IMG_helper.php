<?php
function getGameImageUrl(int $idJuego, string $variant): string
{
    //  Normalizar variante (para evitar problemas de mayusculas o espacios)
    $variant = strtolower(trim($variant));

    // Mapeo de variantes
    $variants = [
        'logo' => ['logo.png'],
        'background' => ['background.jpg', 'background.jpeg'],
        'wide-cover' => ['wide-cover.jpg', 'wide-cover.jpeg'],
        'banner' => ['banner.jpg', 'banner.jpeg'],
        'cover' => ['cover.jpg', 'cover.jpeg'],
        'icon' => ['icon.png', 'icon.jpg', 'icon.jpeg', 'icon.ico']
    ];
    
    // Ruta física de la imagen del juego
    $baseFs = dirname(__DIR__) . '/MEDIA/IMG/juegos/' . $idJuego . '/icons/';

    // Intentar imagen del juego
    foreach ($variants[$variant] as $file) {
        if (is_file($baseFs . $file)) {
            return '../MEDIA/IMG/juegos/' . $idJuego . '/icons/' . $file;
        }
    }

    // FALLBACK (si el juego no tiene imagen, usar una genérica segun el tipo)
    $fallbackFs = dirname(__DIR__) . '/MEDIA/IMG/juegos/fallback/';

    $fallbackFiles = [
        $variant . '.jpg',
        $variant . '.jpeg',
        $variant . '.png',
        $variant . '.ico'
    ];

    foreach ($fallbackFiles as $file) {
        if (is_file($fallbackFs . $file)) {
            return '../MEDIA/IMG/juegos/fallback/' . $file;
        }
    }

    // por si TODO falla 
    return '../MEDIA/IMG/juegos/fallback/default.jpg';
}