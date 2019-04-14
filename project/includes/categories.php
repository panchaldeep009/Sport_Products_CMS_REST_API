<?php
    function get_categories($pdo){
        
        $request = $pdo->prepare('SELECT * FROM tbl_categories');

        $request->execute();
        $categories = $request->fetchAll();
        return $categories;
    }
?>