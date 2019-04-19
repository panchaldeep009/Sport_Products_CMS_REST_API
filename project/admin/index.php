<?php

    require_once('../connect.php');
    require_once('functions.php');

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if(checkLogin()){
            $response = $_SESSION['user'];
        } else {
            $response = array('error' => "No login user found");
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['login'])){
            if(checkLogin()){
                $response = array('success' => 'A user already logged in');
            } else {
                if(isset($_POST['username'], $_POST['password'])){
                    $response = login($_POST['username'], $_POST['password'],$pdo);
                } else {
                    $response = array('error' => 'Bad Request');
                }
            }
        } else if(isset($_POST['create_user'])) {
            if(checkLogin() && $_SESSION['user']['user_username'] === 'Admin'){
                if(isset($_POST['username'], $_POST['password'], $_POST['name'], $_POST['email'])){
                    $response = create_user($_POST['username'], $_POST['password'], $_POST['name'], $_POST['email'], $pdo);
                } else {
                    $response = array('error' => 'Bad Request');
                }
            } else {
                $response = array('error' => 'Admin must be logged in');
            }
        } else if(isset($_POST['log_out'])){
            $response = logout();
        } else {
            $response = array('error' => "Invalid Request");
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $putF = fopen('php://input', 'r');
        $putSTR = '';
        while($data = fread($putF, 1024))
            $putSTR.= $data;
        fclose($putF);
        parse_str($putSTR, $_PUT);

        if(checkLogin()){
            if(isset($_PUT['username'], $_PUT['password'], $_PUT['name'], $_PUT['email'])){
                $response = edit_user($_PUT['username'], $_PUT['email'], $_PUT['name'], $_PUT['password'], $pdo);
            } else {
                $response = array('error' => 'Bad Request');
            }
        } else {
            $response = array('error' => 'User must be logged in');
        }
    }

    header('Content-type: application/json');
    echo json_encode( $response );