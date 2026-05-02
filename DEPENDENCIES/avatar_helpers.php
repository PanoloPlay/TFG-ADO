<?php

if (!function_exists('getProfileAvatarData')) {
    function getProfileAvatarData(string $nickname, string $webBase = '../IMG/usuarios/'): array
    {
        $nickname = trim($nickname);

        $data = [
            'initial' => '',
            'avatarPath' => null,
            'avatarClass' => 'avatar-1',
        ];

        if ($nickname === '') {
            return $data;
        }

        $safeNickname = basename($nickname);
        $data['initial'] = strtoupper(mb_substr($safeNickname, 0, 1, 'UTF-8'));

        $avatarSeed = crc32($safeNickname);
        $avatarClass = 'avatar-' . (($avatarSeed % 6) + 1);

        $baseFs = __DIR__ . '/../IMG/usuarios/';
        $webBase = rtrim($webBase, '/') . '/';

        $candidatos = array_unique([
            $safeNickname,
            strtolower($safeNickname),
            strtoupper($safeNickname),
        ]);

        foreach ($candidatos as $nombre) {
            foreach (['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'] as $ext) {
                $rutaFs = $baseFs . $nombre . '.' . $ext;

                if (is_file($rutaFs)) {
                    clearstatcache(true, $rutaFs);
                    $version = @filemtime($rutaFs) ?: time();

                    $data['avatarPath'] = $webBase . rawurlencode($nombre) . '.' . $ext . '?v=' . $version;
                    $data['avatarClass'] = 'avatar-0';
                    return $data;
                }
            }
        }

        $data['avatarClass'] = $avatarClass;
        return $data;
    }
}
?>