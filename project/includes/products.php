<?php

    // Get Products functions

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
        
        $request = $pdo->prepare('SELECT * FROM tbl_products_categories WHERE product_id = '.$product['product_id']);
        $request->execute();
        $catagories = $request->fetchAll();
        $product['catagories_id'] = [];
        foreach( $catagories as $category ) {
            $product['catagories_id'][] = $category;
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
            $product['media'][] = $media;
        }
    
        return $product;
    }

    /// Create Products functions

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

    function create_category($pdo, $REQUEST){

        $create_category_query = 'INSERT INTO `tbl_categories` (`category_name`) VALUES (:category_name);';

        $create_category = $pdo->prepare($create_category_query); 
        $create_category->bindParam(':category_name', $REQUEST['category_name']);
        $create_category->execute();

        if($create_category->rowCount() > 0){
            return array('success' => "Category inserted");
        } else {
            return array('error' => "Unable to create category");
        }
    }

    function create_product_category($pdo, $REQUEST){
        $create_category_query = 'INSERT INTO `tbl_products_categories` (`category_id`, `product_id`) VALUES (:category_id, :product_id);';

        $create_category = $pdo->prepare($create_category_query); 
        $create_category->bindParam(':category_id', intval($REQUEST['category_id']));
        $create_category->bindParam(':product_id', intval($REQUEST['product_id']));
        $create_category->execute();
        if($create_category->rowCount() > 0){
            return array('success' => "Product category inserted");
        } else {
            return array('error' => "Unable to insert product category");
        }
    }

    function create_product_media($pdo, $REQUEST, $FILES){
        $product_id = $REQUEST['product_id'];

        if(isset($FILES['photo'])){
            if(getimagesize($FILES["photo"]["tmp_name"]) !== false){
                $thisImageSource = 'images/image_'.$product_id.'_'.$FILES["photo"]["name"];

                if (move_uploaded_file($FILES["photo"]["tmp_name"], $thisImageSource)) {
                    $create_media_query = 'INSERT INTO `tbl_products_media` (`product_id`, `media_src`) VALUES (:product_id, :media_src);';

                    $create_media = $pdo->prepare($create_media_query); 
                    $create_media->bindParam(':product_id', intval($product_id));
                    $create_media->bindParam(':media_src', $thisImageSource);
                    $create_media->execute();
    
                    if(!$create_media->rowCount() > 0){
                        return array('error' => "Unable to Upload Image");
                    } else {
                        return array('success' => "Image upload successfully");
                    }
                } else {
                    return array('error' => "Unable to Upload Image");
                }
            } else {
                return array('error' => "Uploaded File is not image");
            }
        }
    }

    /// Edit Products functions

    function edit_product_name($pdo, $REQUEST){
        $edit_product_query = "UPDATE `tbl_products` SET `product_name` = :product_name WHERE `product_id` = :product_id;";

        $edit_product = $pdo->prepare($edit_product_query); 
        $edit_product->bindParam(':product_name', $REQUEST['product_name']);
        $edit_product->bindParam(':product_id', $REQUEST['product_id']);
        $edit_product->execute();

        if($edit_product->rowCount() > 0){
            return array('success' => "Product edited successfully");
        } else {
            return array('error' => "Unable to edit product");
        }
    }

    function edit_product_info($pdo, $REQUEST){
        $edit_product_query = "UPDATE `tbl_products_information` SET `description` = :description, `features` = :features, price = :price WHERE `information_id` = :information_id;";

        $edit_product = $pdo->prepare($edit_product_query); 
        $edit_product->bindParam(':information_id', $REQUEST['information_id']);
        $edit_product->bindParam(':description', $REQUEST['description']);
        $edit_product->bindParam(':features', $REQUEST['features']);
        $edit_product->bindParam(':price', $REQUEST['price']);
        $edit_product->execute();

        if($edit_product->rowCount() > 0){
            return array('success' => "Product information edited successfully");
        } else {
            return array('error' => "Unable to edit product information");
        }
    }

    function edit_product_category($pdo, $REQUEST){
        $edit_product_category_query = "UPDATE `tbl_products_categories` SET `category_id` = :category_id WHERE `category_id` = :category_id AND `product_id` = :product_id;";

        $edit_product_category = $pdo->prepare($edit_product_category_query); 
        $edit_product_category->bindParam(':category_id', $REQUEST['category_id']);
        $edit_product_category->bindParam(':product_id', $REQUEST['product_id']);
        $edit_product_category->execute();

        if($edit_product_category->rowCount() > 0){
            return array('success' => "Product category edited successfully");
        } else {
            return array('error' => "Unable to edit product category");
        }
    }

    function edit_product_media($pdo, $REQUEST, $FILES){
        $select_product_media_query = "SELECT * FROM `tbl_products_media` WHERE `media_id` = :media_id";
        $select_product_media = $pdo->prepare($select_product_media_query); 
        $select_product_media->bindParam(':media_id', $REQUEST['media_id']);
        $select_product_media->execute();

        $this_media = $select_product_media->fetch(PDO::FETCH_ASSOC);

        if(isset($FILES['photo'])){
            if(getimagesize($FILES["photo"]["tmp_name"]) !== false){

                $newImageSource = 'images/image_'.$REQUEST['product_id'].'_'.$FILES["photo"]["name"];

                if (move_uploaded_file($FILES["photo"]["tmp_name"], $newImageSource)) {

                    $edit_product_media_query = "UPDATE `tbl_products_media` SET `media_src` = :media_src WHERE `media_id` = :media_id";

                    $edit_product_media = $pdo->prepare($edit_product_media_query); 
                    $edit_product_media->bindParam(':media_id', $REQUEST['media_id']);
                    $edit_product_media->bindParam(':media_src', $newImageSource);
                    $edit_product_media->execute();

                    if($edit_product_media->rowCount() > 0){
                        return array('success' => "Product category edited successfully");
                        unlink($this_media['media_src']);
                    } else {
                        return array('error' => "Unable to edit product category");
                        unlink($newImageSource);
                    }
                } else {
                    return array('error' => "Unable to Upload Image");
                }
            } else {
                return array('error' => "Uploaded File is not image");
            }
        }
    }

    /// Delete Products functions

    function delete_product($pdo, $REQUEST){

        $delete_product_query = "DELETE FROM `tbl_products` WHERE `product_id` = :product_id;";
        $delete_product = $pdo->prepare($delete_product_query);
        $delete_product->bindParam(':product_id', $REQUEST['product_id']);
        $delete_product->execute();

        $delete_product_info_query = "DELETE FROM `tbl_products_information` WHERE `product_id` = :product_id;";
        $delete_product_info = $pdo->prepare($delete_product_info_query);
        $delete_product_info->bindParam(':product_id', $REQUEST['product_id']);
        $delete_product_info->execute();

        $delete_product_category_query = "DELETE FROM `tbl_products_categories` WHERE `product_id` = :product_id;";
        $delete_product_category = $pdo->prepare($delete_product_category_query);
        $delete_product_category->bindParam(':product_id', $REQUEST['product_id']);
        $delete_product_category->execute();

        $delete_product_media_query = "DELETE FROM `tbl_products_media` WHERE `product_id` = :product_id;";
        $delete_product_media = $pdo->prepare($delete_product_media_query);
        $delete_product_media->bindParam(':product_id', $REQUEST['product_id']);
        $delete_product_media->execute();


        if($delete_product->rowCount() > 0){
            return array('success' => "Product deleted successfully");
        } else {
            return array('error' => "Unable to deleted product");
        }
    }

    function delete_product_category($pdo, $REQUEST){

        $delete_product_category_query = "DELETE FROM `tbl_products_categories` WHERE `products_category_id` = :products_category_id;";
        $delete_product_category = $pdo->prepare($delete_product_category_query);
        $delete_product_category->bindParam(':products_category_id', $REQUEST['products_category_id']);
        $delete_product_category->execute();

        if($delete_product_category->rowCount() > 0){
            return array('success' => "Product category deleted successfully");
        } else {
            return array('error' => "Unable to deleted product category");
        }
    }

    function delete_product_media($pdo, $REQUEST){

        $product_media = "SELECT count(*) FROM `tbl_products_media` WHERE `product_id` = ".$REQUEST['product_media'];
        $product_media = $pdo->prepare($product_media); 
        $product_media->execute(); 
        $number_of_media = $product_media->fetchColumn(); 
        
        if($number_of_media > 1){
    
            $delete_product_media_query = "DELETE FROM `tbl_products_media` WHERE `media_id` = :media_id;";
            $delete_product_media = $pdo->prepare($delete_product_media_query);
            $delete_product_media->bindParam(':media_id', $REQUEST['media_id']);
            $delete_product_media->execute();
    
            if($delete_product_media->rowCount() > 0){
                return array('success' => "Product media deleted successfully");
            } else {
                return array('error' => "Unable to deleted product media");
            }

        } else {
            return array('error' => "This is only image, upload another to delete this photo");
        }
    }
?>