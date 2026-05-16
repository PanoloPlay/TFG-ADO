<?php
function getGameVideoUrl(int $idJuego, string $fileName): string
{
    $fileName = trim($fileName);

    if ($fileName === '') {
        return '../MEDIA/VIDEO/fallback/default.mp4';
    }

    $baseFs = dirname(__DIR__) . '/MEDIA/VIDEO/juegos/' . $idJuego . '/';
    $candidate = $baseFs . $fileName;

    if (is_file($candidate)) {
        return '../MEDIA/VIDEO/juegos/' . $idJuego . '/' . rawurlencode($fileName);
    }

    $fallbackFs = dirname(__DIR__) . '/MEDIA/VIDEO/fallback/';
    $fallbacks = ['default.mp4', 'default.webm', 'default.ogg'];

    foreach ($fallbacks as $fallback) {
        if (is_file($fallbackFs . $fallback)) {
            return '../MEDIA/VIDEO/fallback/' . $fallback;
        }
    }

    return '../MEDIA/VIDEO/fallback/default.mp4';
}