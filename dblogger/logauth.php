<?php

session_start();
require_once "db.php";

if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=trim($_POST['email']);
    $password=$_POST['password'];

    //validating fields
    if(empty($email) || empty($password)){
        die("Please enter both email and password");
    }

    //Getting the user from Database
    $stmt=$conn->prepare("SELECT id,full_name,email,password_hash,is_active FROM users WHERE email= ?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result=$stmt->get_result();

    if ($result->num_rows===1) {
        $user=$result->fetch_assoc();

        //check if account is active
        if ($user['is_active']==0) {
            die("Please verify your email before logging in");
        }

        //verify the password
        if(password_verify($password,$user['password_hash'])){
            //here if password correct session is started
            $_SESSION['user_id']=$user['id'];
            $_SESSION['full_name']=$user['full_name'];
            $_SESSION['email']=$user['email'];

            //For security regenerate session ID
            session_regenerate_id(true);

            //Redirecting to Dashboard
            header("Location: dashboard.php");
            exit;
        }
        else{
            die("Invalid password");
        }
    }

    else{
        die("No account found with that email");
    }
}




?>