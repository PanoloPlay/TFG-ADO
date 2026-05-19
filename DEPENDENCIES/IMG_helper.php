<?php
function getGameImageUrl(int $idJuego, string $variant): string
{
    $variant = strtolower(trim($variant));

    $variants = [
        'logo' => ['logo.png'],
        'background' => ['background.jpg', 'background.jpeg'],
        'wide-cover' => ['wide-cover.jpg', 'wide-cover.jpeg'],
        'banner' => ['banner.jpg', 'banner.jpeg'],
        'cover' => ['cover.jpg', 'cover.jpeg'],
        'capsule' => ['capsule.jpg', 'capsule.jpeg'],
        'icon' => ['icon.png', 'icon.jpg', 'icon.jpeg', 'icon.ico']
    ];

    $baseFs = dirname(__DIR__) . '/MEDIA/IMG/juegos/' . $idJuego . '/icons/';

    foreach ($variants[$variant] ?? [] as $file) {
        if (is_file($baseFs . $file)) {
            return '../MEDIA/IMG/juegos/' . $idJuego . '/icons/' . $file;
        }
    }

    $fallbackFs = dirname(__DIR__) . '/MEDIA/IMG/juegos/fallback/';

    $fallbackFiles = [
        $variant . '.jpg',
        $variant . '.jpeg',
        $variant . '.png',
        $variant . '.ico',
        $variant . '.webp'
    ];

    foreach ($fallbackFiles as $file) {
        if (is_file($fallbackFs . $file)) {
            return '../MEDIA/IMG/juegos/fallback/' . $file;
        }
    }

    return '../MEDIA/IMG/juegos/fallback/default.jpg';
}

function getAchievementImageUrl(string $tipo): string
{
    $tipo = strtolower(trim($tipo));
    $baseFs = dirname(__DIR__) . '/MEDIA/IMG/juegos/fallback/achivements/';

    $files = [
        $tipo . '.jpg',
        $tipo . '.jpeg',
        $tipo . '.png',
        $tipo . '.webp'
    ];

    foreach ($files as $file) {
        if (is_file($baseFs . $file)) {
            return '../MEDIA/IMG/juegos/fallback/achivements/' . $file;
        }
    }

    return '../MEDIA/IMG/juegos/fallback/achivements/default.jpg';
}