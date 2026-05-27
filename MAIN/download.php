<?php 

require_once "../BBDD/conexion.php";

session_start();

function download_file(string $file_path, string $file_name) {
    $file_size = filesize($file_path);
    $mime_type = mime_content_type($file_path);

    if(!isset($_SERVER['HTTP_RANGE'])) {
            // no range, serve the whole file

        header('Accept-Ranges: bytes');
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . $file_size);
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        $file = fopen($file_path, 'rb');
        while (!feof($file)) {
            echo fread($file, 8192);
            flush();
        }
        fclose($file);
        return;
    }

    // range request, serve only the requested range

    list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    $range = explode(',', $range_orig, 2)[0];
    list($range_start, $range_end) = explode('-', $range, 2);
    $range_start = intval($range_start);
    $range_end = intval($range_end);

    // check if range is valid

    if($size_unit != "bytes" || $range_start >= $range_end || $range_end > $file_size - 1 || $range_start < 0 || $range_end < 0) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header('Content-Range: bytes */' . $file_size);
        return;
    }

        // serve requested range

    header('HTTP/1.1 206 Partial Content');
    header('Accept-Ranges: bytes');
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . ($range_end - $range_start + 1));
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Range: bytes ' . $range_start . '-' . $range_end . '/' . $file_size);
    $file = fopen($file_path, 'rb');
    fseek($file, $range_start);
    while (!feof($file) && $range_start < $range_end) {
        echo fread($file, 8192);
        flush();
        $range_start += 8192;
    }
    fclose($file);
}

if (isset($_GET["file"]) && isset($_GET["id"])) {
    $game = $_GET["file"];
    $id = $_GET["id"];

    if (empty($_SESSION["nickname"])) {
        $game = "secretFiles.rar";
        $path = "temp/secretFiles..rar";
    }
    else {
        $stmt = $BBDD->prepare("SELECT j.`id_juego`, j.`nombre_juego`, j.`descripcion`, j.`fecha_publicacion`, j.`desarrollador`, j.`precio`, j.`descuento`, COUNT(v.`id_valoracion`) AS valoraciones, COUNT(CASE WHEN v.`valoracion` = 'positiva' THEN 1 END) AS valoraciones_positivas, AVG(CASE WHEN v.`valoracion` = 'positiva' THEN 1 ELSE 0 END) AS valoracion_media
                            FROM `Biblioteca` AS b 
                            JOIN `Juegos` AS j ON b.`id_juego` = j.`id_juego` 
                            LEFT JOIN `Valoraciones` AS v ON b.`nombre_juego` = v.`nombre_juego`
                            WHERE b.`nickname` = :nick 
                            AND j.`id_juego` = :idJuego
                            GROUP BY j.`nombre_juego`");
        $stmt->bindParam(":nick", $_SESSION["nickname"]);
        $stmt->bindParam(":idJuego", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $path = $id . "/" . $game;
        } else {
            $game = "secretFiles.rar";
            $path = "temp/secretFiles.rar";
        }
    }
}
else {
    $game = "secretFiles.rar";
    $id = "";
    $path = "temp/secretFiles.rar";
}

download_file("../APPS/GAMES/" . $path, $game); 
?>