<?php

require_once("db.php");
    if(isset($_REQUEST['logout'])){
        if($_REQUEST['logout'] == 1){
            unset($_REQUEST['logout']);
            $q = new account();
            $q->logout();
        }
    }
    class account {
        public function logout(){
            if(session_status() == PHP_SESSION_NONE){
                session_start();
            }
            if(isset($_SESSION['id'])){
                unset($_SESSION['id']);
                unset($_SESSION['name']);
                unset($_SESSION['type']);
                unset($_SESSION['department']);

            }
        }

        public function login(){
            $username = $_POST['username'];
            $password = $_POST['password'];
            $query = new db();
            $q = new db();
            
            $results = $query->query('SELECT * FROM accounts WHERE username = ? AND password = ?', $username, $password)->fetchArray();


            if($results){

                // setting session for login
                if(session_status() == PHP_SESSION_NONE)
                    session_start();
                $img = $q->query("SELECT `image` FROM `profile` WHERE accountID = ?",$results['id'])->fetchArray();
                $_SESSION['image'] = $img['image'];
                $_SESSION['name'] = $results['f_name'] . " " . $results['l_name'];
                $_SESSION['id'] = $results['id'];
                $_SESSION['type'] = $results['account_type'];
                $_SESSION['department'] = $results['department'];
                return true;
            }

        }
        
        public function signup(){
            $f_err = $l_err = $email_err = $username_reg_err = $password_reg_err =  $password_err= "";
            $bol = false;
            function text_input($text) // purify input
                {
                    $text = trim($text);
                    $text = stripslashes($text);
                    $text = htmlspecialchars($text);
                    return $text;
                }
            function istext($text) // matching the pattern in regular expression
                {
                    if(preg_match("/^[a-zA-Z ]*$/",$text))
                        return true;
                    else
                        return false;
                }
                // field inputs stored in variables in php
                extract($_POST);
                $uppercase = preg_match('@[A-Z]@', $password);
                $lowercase = preg_match('@[a-z]@', $password);
                $number    = preg_match('@[0-9]@', $password);
                
                if(empty($f_name)){ // if the name is empty
                    
                    $f_err = "FirstName is mandatory";
                
                }else{ // if the name isn't empty purify the input and check for regex
                
                    $f_name = text_input($f_name);
                
                    if(istext($f_name) == false){
                
                        $f_err="Invalid FirstName";
                
                    }
                
                }
                if(empty($l_name)){
                    $l_err = "LastName is mandatory";
                }else{
                    $l_name = text_input($l_name);
                    if(istext($l_name) == false){
                        $l_err="Invalid LastName";
                    }
                }
                if(empty($email))
                {
                    $email_err="E-MAIL is mandatory";
                }else{
                    $email=text_input($email);
                    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
                    {
                        $email_err="Invalid E-MAIL";
                    }
                }
                if(empty($password))
                {
                    $password_err="Password is mandatory";
                }
                else
                {
                    $password = text_input($password);
                    // password must be bigger than 8 characters    
                    if(!$uppercase || !$lowercase || !$number || strlen($password_reg) < 8)
                    {
                        $password_reg_err="Invalid Password";
                    }
                }
                if(empty($f_err) && empty($l_err) && empty($email_err) && empty($username_err) && empty($password_err))
                {
                    $bol = true;
                    $q = new db();
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $q->query("INSERT INTO `accounts`(`f_name`, `l_name`, `email`, `username`, `password`, `department`, `account_type`) VALUES(?,?,?,?,?,?,?);"
                    ,$f_name, $l_name, $email, $username, $hashed_password, $de, $type);
                    if(session_status() == PHP_SESSION_NONE)
                        session_start();
                    
                    $result = $q->query("SELECT `id` FROM `accounts` WHERE `username` = ?", $username)->fetchArray();
                    if($result)
                        $_SESSION['id'] = $result['id'];
                    $q->query("INSERT INTO `profile`(`accountID`, `age`, `location`, `field`, `image`) VALUES(?,?,?,?,?);",
                    $result['id'], NULL, NULL, NULL, "uploads/random.png");
                    $_SESSION['image'] ="uploads/random.png";
                    $_SESSION['type'] =  $type;
                    $_SESSION['department'] = $de;
                    $_SESSION['name'] =  $f_name . " " . $l_name;
                }
                echo $f_err, $l_err, $email_err, $username_err, $password_err;
                return $bol;
            }
    }
