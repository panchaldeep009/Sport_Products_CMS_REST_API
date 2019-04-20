<?php

require_once('connect.php');
require_once('includes/products.php');
require_once('includes/categories.php');
require_once('admin/functions.php');
header('Access-Control-Allow-Origin: *');
// Admin Requests

// Content Requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['catagories'])) {
        $response = get_categories($pdo);
    } else if (isset($_GET['product_id'])) {
        $response = get_product($pdo, $_GET);
    } else {
        $response = get_all_products_name($pdo, $_GET);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (checkLogin()) {
        if (isset($_POST['product_name'])) {
            $response = create_product($pdo, $_POST);
        } else if (isset($_POST['category_name'])) {
            $response = create_category($pdo, $_POST);
        } else if (isset($_POST['category_id'], $_POST['product_id'])) {
            $response = create_product_category($pdo, $_POST);
        } else if (isset($_POST['product_id'], $_FILES['photo'])) {
            $response = create_product_media($pdo, $_POST, $_FILES);
        } else {
            $response = array('error' => 'Bad Request');
        }
    } else {
        $response = array('error' => 'User must be logged in');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    $putF = fopen('php://input', 'r');
    $putSTR = '';
    while ($data = fread($putF, 1024))
        $putSTR .= $data;
    fclose($putF);
    parse_str($putSTR, $_PUT);

    if (checkLogin()) {

        if (isset($_PUT['product_id'])) {
            $response = edit_product_name($pdo, $_PUT);
        } else if (isset($_PUT['information_id'])) {
            $response = edit_product_info($pdo, $_PUT);
        } else if (isset($_PUT['products_category_id'])) {
            $response = edit_product_category($pdo, $_PUT);
        } else if (isset($_PUT['media_id'])) {
            $response = edit_product_media($pdo, $_PUT, $_FILES);
        } else {
            $response = array('error' => 'Bad Request');
        }
    } else {
        $response = array('error' => 'User must be logged in');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $putF = fopen('php://input', 'r');
    $putSTR = '';
    while ($data = fread($putF, 1024))
        $putSTR .= $data;
    fclose($putF);
    parse_str($putSTR, $_DELETE);

    if (checkLogin()) {

        if (isset($_PUT['product_id'])) {
            $response = delete_product($pdo, $_DELETE);
        } else if (isset($_PUT['products_category_id'])) {
            $response = delete_product_category($pdo, $_DELETE);
        } else if (isset($_PUT['media_id'])) {
            $response = delete_product_media($pdo, $_DELETE);
        } else {
            $response = array('error' => 'Bad Request');
        }
    } else {
        $response = array('error' => 'User must be logged in');
    }
}
header('Content-type: application/json');
echo json_encode($response);
