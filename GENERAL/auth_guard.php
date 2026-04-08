<?php

if (empty($_SESSION['id_usuario']) || empty($_SESSION['nickname'])) {
    header("Location: ../AUTH/login.php");
    exit;
}