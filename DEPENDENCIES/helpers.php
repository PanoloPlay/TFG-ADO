<?php
    function e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    function go($url)
    {
        header("Location: $url");
        exit;
    }
?>