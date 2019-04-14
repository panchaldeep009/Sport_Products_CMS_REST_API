<?php
    session_start();
    $db_dsn = array(
        'host'=>'localhost',
        'charset'=>'utf8',
    );

    $dsn ='mysql:'.http_build_query($db_dsn,'',';');
    $db_user = 'root';
    $db_name = 'db_sport_check';
    $db_pass = '';

    try{
        $pdo = new PDO($dsn,$db_user,$db_pass);
        $pdo->exec(" CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_users` (
            `user_id` INT(11) NOT NULL AUTO_INCREMENT ,
            `user_name` VARCHAR(50) NOT NULL ,
            `user_username` VARCHAR(50) NOT NULL ,
            `user_email` VARCHAR(65) NOT NULL ,
            `user_password` VARCHAR(255) NOT NULL ,
            `user_login_attempts` INT(3) NOT NULL DEFAULT 0,
            `user_last_login` INT(11) DEFAULT NULL,
            `user_created_at` INT(11) DEFAULT NULL,
            PRIMARY KEY (`user_id`)) ENGINE = MyISAM");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_products` (
            `product_id` INT(11) NOT NULL AUTO_INCREMENT ,
            `product_name` VARCHAR(255) NOT NULL ,
            PRIMARY KEY (`product_id`)) ENGINE = MyISAM");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_categories` (
            `category_id` INT(11) NOT NULL AUTO_INCREMENT ,
            `category_name` VARCHAR(255) NOT NULL ,
            PRIMARY KEY (`category_id`)) ENGINE = MyISAM");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_products_categories` (
            `products_category_id` INT(11) NOT NULL AUTO_INCREMENT ,
            `category_id` INT(11) NOT NULL,
            `product_id` INT(11) NOT NULL,
            PRIMARY KEY (`products_category_id`)) ENGINE = MyISAM");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_products_information` (
            `information_id` INT(11) NOT NULL AUTO_INCREMENT ,
            `product_id` INT(11) NOT NULL,
            `description` TEXT,
            `features` VARCHAR(5000),
            `price` INT(11) NOT NULL,
            `rating` INT(11),
            PRIMARY KEY (`information_id`)) ENGINE = MyISAM");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_products_media` (
            `media_id` INT(11) NOT NULL AUTO_INCREMENT ,
            `product_id` INT(11) NOT NULL,
            `media_src` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`media_id`)) ENGINE = MyISAM");
    } catch (PDOException $exception){
        echo 'connect error'.$exception->getMessage();
        exit();
    }
?>