<?php

    // PARA ACTIVAR LA MESAJERÍA CAMBIA "$activeMail = false" A "$activeMail = true", para desactivarla cambiala de nuevo a "$activeMail = false"
    // ES POSIBLE QUE EN LA CARPETA "MAIL", EN EL FICHERO "mail.php", EN LA FUNCIÓN "mail_Data()", FALTEN LOS CREDENCIALES DE MAILTRAP, SI ES ASÍ, PONLOS SI DESEAS USAR LA MENSAJERÍA
    // $activateMail = false;

    $BBDD_Name = "tfg_ado_tienda_videojuegos";
    $BBDD_Host = "127.0.0.1";
    // cambiar al suvir a AWS, el usuatrio y contraseña de la base de datos
    $BBDD_User = "root";
    $BBDD_Password = "";
    $DNS = "mysql:dbname=$BBDD_Name;host=$BBDD_Host";
        
    try {
            
        $BBDD = new PDO($DNS, $BBDD_User, $BBDD_Password);
        $BBDD->exec("SET NAMES utf8mb4");
    } 
    catch (PDOException) {
            
        // header("Location: ../index.php?Error=Error_Al_Cargar");
        die("Error al conectar con la base de datos");
    }
?>