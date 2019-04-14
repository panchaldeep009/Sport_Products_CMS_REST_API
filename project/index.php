<?php

    require_once('connect.php');
    require_once('includes/products.php');
    require_once('includes/categories.php');
    require_once('admin/functions.php');

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if(isset($_GET['catagories'])){
            $response = get_categories($pdo);
        } else {
            $response = get_all_products_name($pdo);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['product_id'])){
            $response = get_product($pdo, $_POST);
        } else if(isset($_POST['product_name'])){
            $response = create_product($pdo, $_POST);
        } else {
            $response = post_products_name($pdo, $_POST);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if(isset($_POST['product_id'])){
            $response = get_product($pdo, $_POST);
        } else if(isset($_POST['product_name'])){
            $response = create_product($pdo, $_POST);
        } else {
            $response = post_products_name($pdo, $_POST);
        }
    }
    header('Content-type: application/json');
    echo json_encode( $response );