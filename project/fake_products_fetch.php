<?php
    $main_categories = [
        "Men",
        "Women",
        "Boys", 
        "Girls"
    ];
    $sub_categories = [
        "SHOES",
        "JACKETS",
        "T-SHIRTS AND TOPS",
        "PANTS, SHORTS, TIGHTS",
        "HATS",
        "WATCH",
        "SUN GLASS",
        "SOCKS",
        "WINTER ACCESSORIES",
        "SWIM GEAR",
    ];

    $Special = [
        'BELTS',
        'BACKPACK',
        'SOCCER',
        'TENNIS',
        'BASKET BALL',
        'BASEBALL',
        'HOCKEY',
        'SOCCER',
        'GOLF',
    ];

    require_once('connect.php');

    foreach ($main_categories as $main_category) {
        foreach ($sub_categories as $sub_category) {
            $query = urlencode($main_category.' '.$sub_category);
            getProducts($query, [$main_category, $sub_category], $pdo);
        }
    }
    foreach ($Special as $Special_category) {
        $query = urlencode($main_category.' '.$sub_category);
        getProducts($query, [$Special_category], $pdo);
    }

    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    // getProducts(urlencode("MEN PANTS, SHORTS, TIGHTS"), ['MEN', 'PANTS, SHORTS, TIGHTS']);

    function getProducts($query, $this_categories, $pdo){

        $url = "https://www.sportchek.ca/services/sportchek/search-and-promote/products?q=$query=&page=1&count=25";
        $data = file_get_contents($url);
        $data = json_decode($data,true);

        // Find Category select if exists or insert
        $this_categories_ids = [];
        foreach ($this_categories as $this_category_name) {
            $check_category = $pdo->prepare("SELECT * FROM tbl_categories WHERE category_name = :category_name");
            $check_category->bindParam(':category_name', $this_category_name);
            $check_category->execute();
            $category_row = $check_category->fetch(PDO::FETCH_ASSOC);
            
            if(!$category_row){
                $insert_category = $pdo->prepare("INSERT INTO tbl_categories (`category_name`) VALUES (:category_name)");
                $insert_category->bindParam(':category_name', $this_category_name);
                $insert_category->execute();
                if($insert_category->rowCount() > 0){
                    array_push($this_categories_ids, $pdo->lastInsertId());
                }
            } else {
                array_push($this_categories_ids, $category_row['category_id']);
            }
        }

        foreach ($data['products'] as $product) {

            $create_product_query = 'INSERT INTO `tbl_products` (`product_name`) ';
            $create_product_query .= 'VALUES (:name);';

            $create_product = $pdo->prepare($create_product_query); 
            $create_product->bindParam(':name', $product['title'], PDO::PARAM_STR);
            $create_product->execute();

            if($create_product->rowCount() > 0){

                $product_id = $pdo->lastInsertId();

                $create_media_query = 'INSERT INTO `tbl_products_media` (`product_id`, `media_src`) VALUES (:product_id, :media_src);';

                $preps = '?bgColor=0,0,0,0&fmt=png-alpha&hei=800&resMode=sharp2&op_sharpen=1';
                
                foreach ($product['imageAndColor'] as $image) {
                    $image = "http:".$image['imageUrl'].$preps;
                    $create_media = $pdo->prepare($create_media_query); 
                    $create_media->bindParam(':product_id', intval($product_id));
                    $create_media->bindParam(':media_src', $image);
                    $create_media->execute();
                }

                $description = get_string_between($product['longDescription'], '<p>', '</p>');

                if($product['features'] !== null){
                    $features = implode(";", 
                        explode("</li><li>", 
                            get_string_between($product['features'], '<ul><li>', '</li></ul>')
                        ));
                } else {
                    $features = '';
                }

                $create_information_query = 'INSERT INTO `tbl_products_information` (`product_id`, `description`, `features`, `price`, `rating`) VALUES (:product_id, :description, :features, :price, :rating);';

                $create_information = $pdo->prepare($create_information_query);
                $create_information->bindParam(':product_id', intval($product_id));
                $create_information->bindParam(':description', $description);
                $create_information->bindParam(':features', $features);
                $create_information->bindParam(':rating', intval($product['rating']));
                $create_information->bindParam(':price', intval($product['price']));
                $create_information->execute();
            
                foreach ($this_categories_ids as  $this_category_id) {

                    $create_category_query = 'INSERT INTO `tbl_products_categories` (`category_id`, `product_id`) VALUES (:category_id, :product_id);';

                    $create_category = $pdo->prepare($create_category_query); 
                    $create_category->bindParam(':category_id', intval($this_category_id));
                    $create_category->bindParam(':product_id', intval($product_id));
                    $create_category->execute();
                }
            }
        }
    }
    ///https://www.sportchek.ca/services/sportchek/search-and-promote/products?q={term}&page=1&count=20
?>