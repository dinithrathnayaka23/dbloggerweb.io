<?php
    if(session_start() === PHP_SESSION_NONE){}
    if(!isset($_SESSION['user_id'])){
        header("Location: login.php");
        exit;
    }


?>