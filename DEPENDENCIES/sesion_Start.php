<?php
    header('Content-Type: text/html; charset=UTF-8');
    
    if (session_status() === PHP_SESSION_NONE) {
        
        session_start();
    }
?>