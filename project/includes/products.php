<?php
    // Get Products
    function get_all_products_name($pdo, $REQUEST){

        /// Pagination

        if(!isset($REQUEST['product_per_page'])){
            $REQUEST['product_per_page'] = 15;
        }

        if(!isset($REQUEST['page'])){
            $REQUEST['page'] = 1;
        }

        $offset = ($REQUEST['product_per_page']*($REQUEST['page']-1));
        $limit_to = $REQUEST['product_per_page'];

        $limit = " LIMIT $offset,$limit_to";
        
        /// Condition

        $condition = " WHERE true ";

        if(isset($REQUEST['category_ids'])){
            foreach (explode(',', urldecode($REQUEST['category_ids'])) as $category_id) {
                $condition .= " AND product_id in (SELECT product_id FROM tbl_products_categories WHERE category_id = ".$category_id.") ";
            }
        }

        if(isset($REQUEST['search'])){
            $condition .= " AND `product_name` LIKE '%";
            $condition .= urldecode($REQUEST['search']);
            $condition .= "%' ";
        }

        // Products fetch
        
        $request_count = "SELECT count(*) FROM `tbl_products`".$condition; 
        $result_count = $pdo->prepare($request_count); 
        $result_count->execute(); 
        $number_of_products = $result_count->fetchColumn(); 

        $request = $pdo->prepare("SELECT * FROM tbl_products" .$condition.$limit);
        $request->execute();
        $products = [];

        while($product = $request->fetch(PDO::FETCH_ASSOC)){
            $information_request = $pdo->prepare('SELECT * FROM tbl_products_information WHERE product_id = '.$product['product_id']);
            $information_request->execute();
            if($information = $information_request->fetch(PDO::FETCH_ASSOC)){
                $product['price'] = $information['price'];
                $request_media = $pdo->prepare('SELECT * FROM tbl_products_media WHERE product_id = '.$product['product_id']);
                $request_media->execute();
                if($media = $request_media->fetch(PDO::FETCH_ASSOC)){
                    $product['image'] = $media['media_src'];
                    $products[] = $product;
                }
            }
        }
        return array(
            "condition" => $condition,
            "page" => $REQUEST['page'],
            "total_page" => ceil($number_of_products/$REQUEST['product_per_page']),
            "product_per_page" => $REQUEST['product_per_page'],
            "number_of_products" => $number_of_products,
            "products" => $products,
        );
    }

    function post_products_name($pdo, $REQUEST){
    
        if(isset($REQUEST['filter'])){
            $request = $pdo->prepare('SELECT * FROM tbl_products WHERE product_id in (SELECT product_id FROM tbl_products_categories WHERE category_id in ('.$REQUEST['filter'].'))');
        } else {
            $request = $pdo->prepare('SELECT * FROM tbl_products');
        }
        
        $request->execute();
        $products = $request->fetchAll();
    
        if(isset($REQUEST['search'])){
            $products = array_filter($products, function ($product) use ($REQUEST){
                if (strpos(strtolower($product['product_name']), strtolower($REQUEST['search'])) !== false) {
                    return true;
                } else {
                    return false;
                }
            });
        }
    
        return $products;
    }

    function get_product($pdo, $REQUEST){

        $request = $pdo->prepare('SELECT * FROM tbl_products WHERE product_id = '.$REQUEST['product_id']);
        $request->execute();
        $products = $request->fetchAll();
        $product = $products[0];

        $request = $pdo->prepare('SELECT * FROM tbl_categories WHERE category_id in (SELECT category_id FROM tbl_products_categories WHERE product_id = '.$product['product_id'].')');
        $request->execute();
        $catagories = $request->fetchAll();
        $product['catagories'] = [];
        foreach( $catagories as $category ) {
            $product['catagories'][] = $category;
        }
    
        $request = $pdo->prepare('SELECT * FROM tbl_products_information WHERE product_id = '.$product['product_id']);
        $request->execute();
        $information = $request->fetchAll();
        $product['info'] = [];
        if(sizeof($information) > 0){
            $information[0]['features'] = explode(';', $information[0]['features']);
            $product['info'] = $information;
        }
    
        $request = $pdo->prepare('SELECT * FROM tbl_products_media WHERE product_id = '.$product['product_id']);
        $request->execute();
        $medias = $request->fetchAll();
        $product['media'] = [];
        foreach ($medias as $media) {
            $product['media'][] = $media['media_src'];
        }
    
        return $product;
    }

    function create_product($pdo, $REQUEST){

        require_once('admin/functions.php');
        
        if(checkLogin()){
            $create_product_query = 'INSERT INTO `tbl_products` (`product_name`) ';
            $create_product_query .= 'VALUES (:name);';

            $create_product = $pdo->prepare($create_product_query); 
            $create_product->bindParam(':name', $REQUEST['product_name'], PDO::PARAM_STR);
            $create_product->execute();

            if($create_product->rowCount() > 0){
                $product_id = $pdo->lastInsertId();

                if(isset($_FILES['photo'])){
                    if(getimagesize($_FILES["photo"]["tmp_name"]) !== false){
                        $thisImageSource = 'images/image_'.$product_id.'_'.$_FILES["photo"]["name"];

                        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $thisImageSource)) {
                            $create_media_query = 'INSERT INTO `tbl_products_media` (`product_id`, `media_src`) VALUES (:product_id, :media_src);';

                            $create_media = $pdo->prepare($create_media_query); 
                            $create_media->bindParam(':product_id', intval($product_id));
                            $create_media->bindParam(':media_src', $thisImageSource);
                            $create_media->execute();
        
                            if(!$create_media->rowCount() > 0){
                                return array('error' => "Unable to Upload Image");
                            }
                        } else {
                            return array('error' => "Unable to Upload Image");
                        }
                    } else {
                        return array('error' => "Uploaded File is not image");
                    }
                }

                if(!empty($REQUEST['categories'])){
                    foreach (explode(',', $REQUEST['categories']) as $category_id) {

                        $check_category = $pdo->prepare("SELECT * FROM tbl_categories WHERE category_id = :category_id");
                        $check_category->bindParam(':category_id', intval($category_id));
                        $check_category->execute();

                        if(!$check_category->rowCount() > 0){
                            $pdo->exec("DELETE FROM tbl_products WHERE product_id = ".$product_id);
                            $pdo->exec("DELETE FROM tbl_products_categories WHERE product_id = ".$product_id);
                            return array('error' => "Category Not Found");
                        }

                        $create_category_query = 'INSERT INTO `tbl_products_categories` (`category_id`, `product_id`) VALUES (:category_id, :product_id);';

                        $create_category = $pdo->prepare($create_category_query); 
                        $create_category->bindParam(':category_id', intval($category_id));
                        $create_category->bindParam(':product_id', intval($product_id));
                        $create_category->execute();
    
                        if(!$create_category->rowCount() > 0){
                            $pdo->exec("DELETE FROM tbl_products WHERE product_id = ".$product_id);
                            $pdo->exec("DELETE FROM tbl_products_categories WHERE product_id = ".$product_id);
                            return array('error' => "Unable to insert Product");
                        }
                    }
                }

                    $create_information_query = 'INSERT INTO `tbl_products_information` (`product_id`, `description`, `features`, `price`, `rating`) VALUES (:product_id, :description, :features, :price, :rating);';

                    $create_information = $pdo->prepare($create_information_query);
                    $create_information->bindParam(':product_id', intval($product_id));
                    $create_information->bindParam(':description', $REQUEST['description']);
                    $create_information->bindParam(':features', $REQUEST['features']);
                    $create_information->bindParam(':rating', intval($REQUEST['rating']));
                    $create_information->bindParam(':price', intval($REQUEST['price']));
                    $create_information->execute();

                    if(!$create_information->rowCount() > 0){
                        $pdo->exec("DELETE FROM tbl_products WHERE product_id = ".$product_id);
                        $pdo->exec("DELETE FROM tbl_products_categories WHERE product_id = ".$product_id);
                        $pdo->exec("DELETE FROM tbl_products_information WHERE product_id = ".$product_id);
                        return array('error' => "Unable to insert info of Product");
                    }
                    
                return array('success' => "Product Added successfully");

            } else {
                return array('error' => "Unable to create new product");
            }
            return array('success' => "Product Created");
        } else {
            return array('error' => "No admin logged in");
        }
    }
?>