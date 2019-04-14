<?php

    require_once('../connect.php');
    require_once('functions.php');

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if(checkLogin()){
            $response = $_SESSION['user'];
        } else {
            $response = array('error' => "No user logged in");
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['login'])){
            $response = login($_POST['username'], $_POST['password'], $pdo);
        } else if(isset($_POST['sign_up'])){
            $response = create_user($_POST['username'], $_POST['password'], $_POST['name'], $_POST['email'], $pdo);
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

        $response = edit_user($_PUT['username'], $_PUT['email'], $_PUT['name'], $_PUT['password'], $pdo);
    }
    header('Content-type: application/json');
    echo json_encode( $response );