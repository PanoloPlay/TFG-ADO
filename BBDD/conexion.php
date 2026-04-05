<?php

    $BBDD_Name = "tfg_ado_tienda_videojuegos";
    $BBDD_Host = "127.0.0.1";
    // cambiar al suvir a AWS, el usuatrio y contraseña de la base de datos
    $BBDD_User = "root";
    $BBDD_Password = "";
    $DNS = "mysql:dbname=$BBDD_Name;host=$BBDD_Host";
        
    try {
            
        $BBDD = new PDO($DNS, $BBDD_User, $BBDD_Password);
    } 
    catch (PDOException) {
            
        // header("Location: ../index.php?Error=Error_Al_Cargar");
        die("Error al conectar con la base de datos");
    }
?>