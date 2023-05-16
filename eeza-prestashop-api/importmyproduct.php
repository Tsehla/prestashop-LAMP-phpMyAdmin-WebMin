
<?php

/* test data send by post as JSON :: EXAMPLE SENT DATA { name: 'Manny', species: 'cat' }

$data = json_decode(file_get_contents('php://input'), true);

print_r($data);

echo 'this is result :: '.$data["name"];

*/

    $data = json_decode(file_get_contents('php://input'), true); //get posted data in json en decode

    $product_ean13_number = $data['product_ean13_number'];
    $mabuFactuId = $data['product_manufacture_id'];
    $product_referance_discription = $data["product_referance_discription"];
    $Product_name = $data["Product_name"];
    $total_products_in_stock = $data["total_products_in_stock"];
    $product_discription =  urldecode($data["product_discription"]);
    $product_summary_discription =  urldecode($data["product_summary_discription"]);

    // var_export($product_discription);

    $product_features_color = $data["product_features_color"];
    $product_features_dimension_en_weight = $data["product_features_dimension_en_weight"];
    $product_features_make_or_manufacture = $data["product_features_make_or_manufacture"];
    $product_features_model_or_product_sku = $data["product_features_model_or_product_sku"];
    $product_features_properties_header_text = $data["product_features_properties_header_text"];
    $product_features_properties_or_specifications = $data["product_features_properties_or_specifications"];

    $product_features_dimensions_height = $data["product_features_dimensions_height"];
    $product_price_with_decimal = $data["product_price_with_decimal"];
    $product_image_link_url = $data["product_image_link_url"];

    $product_default_parent_category = $data["product_default_parent_category"]; 
    // $product_default_parent_category = explode(',', $product_default_parent_category);//turn to php compartible array


    $product_other_categories_including_main_category_comma_separated = $data['product_other_categories_including_main_category_comma_separated']; //get javascript literal array turned to comma separated text
    $product_other_categories_including_main_category_comma_separated = explode(',', $product_other_categories_including_main_category_comma_separated);//turn to php compartible array
    


    // Check if _PS_ADMIN_DIR_ is defined
    if (!defined('_PS_ADMIN_DIR_')) {
        // if _PS_ADMIN_DIR_ is not defined, define.
        define('_PS_ADMIN_DIR_', getcwd());
    }
    // Setup connection with config.inc.php (required for database connection, ...)
    include(_PS_ADMIN_DIR_.'/config/config.inc.php');



    $secure_key = '4ERQKSBATUWAMU1BN64M1LIY23W776JZ';//authorization key //this file does not use prestashop webservices, it add products directly, this authorization key is used to prevent unauthorized entry, it will match security key posted with new products details en if that key matches on i specified here, then code will start else it will give error

    // Check if the client use the correct secure_key, url to use: www.yourstore.com/yourbackoffice/importmyproduct.php?secure_key=ed3fa1ce558e1c2528cfbaa3f99403
    if(!Tools::getValue('secure_key') || Tools::getValue('secure_key') != $secure_key) {
        // If the secure_key is not set our not equal the php page will stop running.
        // die('UNAUTHORIZED: We dont want you on this page!');
        die('Error:prestashop webservice key does not match one expected by product adding script : open product adding script to see the expected authorization key');
    }
    // echo 'Welcome, the secure_key you have used is correct. Now we can start adding  programmatically ... <br>';



    function addProduct($ean13, $mabuFactuId, $ref, $name, $qty, $text, $hortText, $features, $price, $imgUrl, $catDef, $catAll) {
        $product = new Product();              // Create new product in prestashop
        $product->ean13 = $ean13;
        $product->id_manufacturer = $mabuFactuId;
        $product->reference = $ref;
        $product->name = createMultiLangField(utf8_encode($name));
        // $product->description = htmlspecialchars($text); //will convert html special characters into their html entities i.e <br /> into &lt;br /&gt;
        // $product->description_short  = htmlspecialchars($hortText);  //will convert html special characters into their html entities i.e <br /> into &lt;br /&gt;
        $product->description = $text;
        $product->description_short  = $hortText;
        $product->id_category_default = $catDef;
        $product->redirect_type = '301';
        $product->price = number_format($price, 6, '.', '');
        $product->minimal_quantity = 1;
        $product->show_price = 1;
        $product->on_sale = 0;
        $product->online_only = 0;
        $product->meta_description = '';
        $product->link_rewrite = createMultiLangField(Tools::str2url($name)); // Contribution credits: mfdenis
        $product->add();                        // Submit new product
        StockAvailable::setQuantity($product->id, null, $qty); // id_product, id_product_attribute, quantity
        $product->addToCategories($catAll);     // After product is submitted insert all categories

        // Insert "feature name" and "feature value"
        if (is_array($features)) {
            foreach ($features as $feature) {
                $attributeName = $feature['name'];
                $attributeValue = $feature['value'];

                // 1. Check if 'feature name' exist already in database
                $FeatureNameId = Db::getInstance()->getValue('SELECT id_feature FROM ' . _DB_PREFIX_ . 'feature_lang WHERE name = "' . pSQL($attributeName) . '"');
                // If 'feature name' does not exist, insert new.
                if (empty($FeatureNameId)) {
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'feature` (`id_feature`,`position`) VALUES (0, 0)');
                    $FeatureNameId = Db::getInstance()->Insert_ID(); // Get id of "feature name" for insert in product
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'feature_shop` (`id_feature`,`id_shop`) VALUES (' . $FeatureNameId . ', 1)');
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'feature_lang` (`id_feature`,`id_lang`, `name`) VALUES (' . $FeatureNameId . ', ' . Context::getContext()->language->id . ', "' . pSQL($attributeName) . '")');
                }

                // 1. Check if 'feature value name' exist already in database
                $FeatureValueId = Db::getInstance()->getValue('SELECT id_feature_value FROM ' . _DB_PREFIX_ . 'feature_value WHERE id_feature_value IN (SELECT id_feature_value FROM `' . _DB_PREFIX_ . 'feature_value_lang` WHERE value = "' . pSQL($attributeValue) . '") AND id_feature = ' . $FeatureNameId);
                // If 'feature value name' does not exist, insert new.
                if (empty($FeatureValueId)) {
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'feature_value` (`id_feature_value`,`id_feature`,`custom`) VALUES (0, ' . $FeatureNameId . ', 0)');
                    $FeatureValueId = Db::getInstance()->Insert_ID();
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'feature_value_lang` (`id_feature_value`,`id_lang`,`value`) VALUES (' . $FeatureValueId . ', ' . Context::getContext()->language->id . ', "' . pSQL($attributeValue) . '")');
                }
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'feature_product` (`id_feature`, `id_product`, `id_feature_value`) VALUES (' . $FeatureNameId . ', ' . $product->id . ', ' . $FeatureValueId . ')');
            }
        }

        // add product image.  //Keep disabled, it somehow stopped working for unknown reason, image no longer added by this script
        // $shops = Shop::getShops(true, null, true);
        // $image = new Image();
        // $image->id_product = $product->id;
        // $image->position = Image::getHighestPosition($product->id) + 1;
        // $image->cover = true;
        // if (($image->validateFields(false, true)) === true && ($image->validateFieldsLang(false, true)) === true && $image->add()) {
        //     $image->associateTo($shops);
        //     if (!uploadImage($product->id, $image->id, $imgUrl)) {
        //         $image->delete();
        //     }
        // }
        // echo 'Product added successfully (ID: ' . $product->id . ')';
        echo 'Sucess new product added to store, newly added product given ID = ' . $product->id ;
    }

    function uploadImage($id_entity, $id_image = null, $imgUrl) {
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));
        $image_obj = new Image((int)$id_image);
        $path = $image_obj->getPathForCreation();
        $imgUrl = str_replace(' ', '%20', trim($imgUrl));
        // Evaluate the memory required to resize the image: if it's too big we can't resize it.
        if (!ImageManager::checkImageMemoryLimit($imgUrl)) {
            return false;
        }
        if (@copy($imgUrl, $tmpfile)) {
            ImageManager::resize($tmpfile, $path . '.jpg');
            $images_types = ImageType::getImagesTypes('products');
            foreach ($images_types as $image_type) {
                ImageManager::resize($tmpfile, $path . '-' . stripslashes($image_type['name']) . '.jpg', $image_type['width'], $image_type['height']);
                if (in_array($image_type['id_image_type'], $watermark_types)) {
                Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
                }
            }
        } else {
            unlink($tmpfile);
            return false;
        }
        unlink($tmpfile);
        return true;
    }




    function createMultiLangField($field) {
        $res = array();
        foreach (Language::getIDs(false) as $id_lang) {
            $res[$id_lang] = $field;
        }
        return $res;
    }


//     addProduct(
//         '1234567891234',                         // Product EAN13
//         'Tutorial by Crezzur',                         // Product reference
//         'Crezzur test product',                               // Product name
//         5,                                       // Product quantity
//         'Code by Crezzur (https://crezzur.com)', // Product description
//         array(                                  // Product features (array)
//             array("name" => "Color", "value" => "Red"),
//             array("name" => "Height", "value" => "200cm"),
//        ),
//         '999.95',                                // Product price
//         'https://i1.sndcdn.com/avatars-000001420029-vwz0xj-t500x500.jpg',       // Product image
//         1,                                       // Product default category
//         array(1, 5)                              // All categorys for product (array)
//    );

//     addProduct(
//         $product_ean13_number,                                                                                         // Product EAN13   // 1234567891234
//         $product_referance_discription,                                              // Product reference //'Tutorial by Crezzur' 
//         $Product_name,                                                                      // Product name //'Crezzur test product'
//         $total_products_in_stock,                                                         // Product quantity //  5
//         $product_discription,                                                                 // Product description //'Code by Crezzur (https://crezzur.com)'
//         $product_summary_discription,                                                         // Product short description //'Code by Crezzur (https://crezzur.com)'
//         array(                                                                                      // Product features (array) // can add other stuff like width/etc
//             array("name" => "Color", "value" => $product_features_color),                       // array("name" => "Color", "value" => "Red" )
//             array("name" => "Height", "value" => $product_features_dimensions_height),          //array("name" => "weight", "value" => "200cm")
//             // array("name" => "weight", "value" => $data["product_features_total_weight"]),               //array("name" => "weight", "value" => "200kg")
//        ),
//         $product_price_with_decimal,                                                                                 // Product price //'999.95'
//         $product_image_link_url,                                                               // Product image//'https://i1.sndcdn.com/avatars-000001420029-vwz0xj-t500x500.jpg'
//         $product_default_parent_category ,                                                    // Product default category //  1
//         $product_other_categories_including_main_category_comma_separated                 //array(1, 3)       // All categorys for product (array)//========== find way to handle this //all other categories product should appears on
//    );


  
    $product_data_sheet2 =    array(  // can add other stuff like width/etc //html breaks dont work with prestashop so use [ \r\n ] for bullets use html symbol representation code i.e [ &#9702 ]
        // array("name" => "Make", "value" => $product_features_make_or_manufacture), 
        // array("name" => "Model/SKU", "value" => $product_features_model_or_product_sku), 
        // array("name" => "Dimensions", "value" => $product_features_dimension_en_weight), 
        // array("name" => "Colour", "value" => $product_features_color), 
        // array("name" => "Properties", "value" => ""),                                                                                                                         // Product features (array) /
        // array("name" => "Color", "value" => $product_features_color),                       // array("name" => "Color", "value" => "Red" )
        // array("name" => "Height", "value" => $product_features_dimensions_height),          //array("name" => "weight", "value" => "200cm")
        // array("name" => "weight", "value" => $data["product_features_total_weight"]),               //array("name" => "weight", "value" => "200kg")
    );

   
    $str_to_edit =  $product_features_properties_or_specifications;
    $to_replace_with = ";;";
    $to_replace = "\n";
    $final_str = str_replace($to_replace, $to_replace_with, $str_to_edit);
    $str = $final_str;
    $delimiter = ';;';
    $words = explode($delimiter, $str);
    $arr_lenth = count($words);


   //add first property
    
    

    //loop en create other properties
    // foreach ($words as $word) {
    for($a=0; $a <= $arr_lenth -1 ; $a++){

        // echo $word;
        // $value_pairs = array("name" => $a + 5, "value" => $words[$a]);
        $value_pairs = array("name" => $words[$a], "value" => "");
        // echo "<br>";
        // array_push($product_data_sheet2,$value_pairs); //keep disabled for now, its misbehaving badly in some products

        // if($a==$arr_lenth -1){
        //     array_unshift($product_data_sheet2,
        //         array("name" => "Make", "value" => $product_features_make_or_manufacture), 
        //         array("name" => "Model/SKU", "value" => $product_features_model_or_product_sku), 
        //         array("name" => "Dimensions", "value" => $product_features_dimension_en_weight), 
        //         array("name" => "Colour", "value" => $product_features_color), 
        //         array("name" => "Properties", "value" => $properties_text)       
        //     );
        // }
    }


    $product_data_sheet = $product_data_sheet2;


    // if($a==$arr_lenth -1){
        //     array_unshift($product_data_sheet2,
        //         array("name" => "Make", "value" => $product_features_make_or_manufacture), 
        //         array("name" => "Model/SKU", "value" => $product_features_model_or_product_sku), 
        //         array("name" => "Dimensions", "value" => $product_features_dimension_en_weight), 
        //         array("name" => "Colour", "value" => $product_features_color), 
        //         array("name" => "Properties", "value" => $properties_text)       
        //     ); 

        // error :::  array("name" => "Features", "value" => $product_features_properties_header_text),   ::: is not added in right position, should be above features; this seem to work [  array("name" => "0", "value" => "Features") ]
        // array_unshift($product_data_sheet,
        //     array("name" => 1, "value" => "Make : " . $product_features_make_or_manufacture), 
        //     array("name" => 2, "value" => "Model/SKU : " .$product_features_model_or_product_sku), 
        //     array("name" => 3, "value" => "Dimensions : " .$product_features_dimension_en_weight), 
        //     array("name" => 4, "value" => "Colour : " . $product_features_color), 
        //     array("name" => 5, "value" => "Specification")
        // );
        // array_unshift($product_data_sheet,
        //     // array("name" => "Brand : \n\n" . $product_features_make_or_manufacture, "value" => ""), 
        //     // array("name" => "Model/SKU : \n\n" .$product_features_model_or_product_sku, "value" => ""), 
        //     array("name" => "Dimensions : \n\n" .$product_features_dimension_en_weight, "value" => ""), 
        //     array("name" => "Colour : \n\n" . $product_features_color, "value" => "") 
        //     // array("name" => "Specification", "value" => "")
        // );
        array_unshift($product_data_sheet,
            // array("name" => "Brand : \n\n" . $product_features_make_or_manufacture, "value" => ""), 
            // array("name" => "Model/SKU : \n\n" .$product_features_model_or_product_sku, "value" => ""), 
            array("name" => "Dimensions", "value" => $product_features_dimension_en_weight), //return them the right way until i can solve [         // array_push($product_data_sheet2,$value_pairs); //keep disabled for now, its misbehaving badly in some products] issue if i do
            array("name" => "Colour", "value" => $product_features_color) 
            // array("name" => "Specification", "value" => "")
        );

    // }

    // print_r($product_data_sheet);
        // echo $product_summary_discription;

    //STRING CLEANING OF URL ENCODING ISSUE FIXED :: it was caused on main app/software when each product details was updated part ....
    //clean strings, issue doing this in nodejs, seem not effective or applied, so decided to do here than spends hours en days figuring out why nodejs issue   //NOT WORKING=---
    // $to_replace_with_on_string = ["&quot;","&quot;","&apos;","&quot;","&apos;"]; //array position one will be replaced with array position 1, then move to 2, then 3, then etc
    // $to_replace_what_on_string = ["%28","%29","%27","\"","'"];
    // $cleaned_product_summary_discription = str_replace($to_replace_what_on_string,$to_replace_with_on_string, $product_summary_discription);
    // $cleaned_product_discription = str_replace($to_replace_what_on_string,$to_replace_with_on_string, $product_discription);



    //try another methods,  //using url decode, this is unexected, sice data is posted to this php script
    //   echo utf8_decode(urldecode(" Whether you%27re watching something by yourself or with a partner or a group of friends, this TV offers a pleasurable viewing experience."));
    // $cleaned_product_summary_discription = utf8_decode(urldecode($product_summary_discription));
    // $cleaned_product_discription = utf8_decode(urldecode($product_discription));

 

    // addProduct(
    //     $product_ean13_number,                                                                                         // Product EAN13   // 1234567891234
    //     $product_referance_discription,                                              // Product reference //'Tutorial by Crezzur' 
    //     $Product_name,                                                                      // Product name //'Crezzur test product'
    //     $total_products_in_stock,                                                         // Product quantity //  5
    //     $cleaned_product_discription,                                                                 // Product description //'Code by Crezzur (https://crezzur.com)'
    //     $cleaned_product_summary_discription,                                                         // Product short description //'Code by Crezzur (https://crezzur.com)'
    //     $product_data_sheet,
    //     $product_price_with_decimal,                                                                                 // Product price //'999.95'
    //     $product_image_link_url,                                                               // Product image//'https://i1.sndcdn.com/avatars-000001420029-vwz0xj-t500x500.jpg'
    //     $product_default_parent_category ,                                                    // Product default category //  1
    //     $product_other_categories_including_main_category_comma_separated                 //array(1, 3)       // All categorys for product (array)//========== find way to handle this //all other categories product should appears on
    // );
    // addProduct(
    //     $product_ean13_number,                                                                                         // Product EAN13   // 1234567891234
    //     $product_referance_discription,                                              // Product reference //'Tutorial by Crezzur' 
    //     $Product_name,                                                                      // Product name //'Crezzur test product'
    //     $total_products_in_stock,                                                         // Product quantity //  5
    //     utf8_decode(urldecode($product_discription)),                                                                 // Product description //'Code by Crezzur (https://crezzur.com)'
    //     utf8_decode(urldecode($product_summary_discription)),                                                         // Product short description //'Code by Crezzur (https://crezzur.com)'
    //     $product_data_sheet,
    //     $product_price_with_decimal,                                                                                 // Product price //'999.95'
    //     $product_image_link_url,                                                               // Product image//'https://i1.sndcdn.com/avatars-000001420029-vwz0xj-t500x500.jpg'
    //     $product_default_parent_category ,                                                    // Product default category //  1
    //     $product_other_categories_including_main_category_comma_separated                 //array(1, 3)       // All categorys for product (array)//========== find way to handle this //all other categories product should appears on
    // );


    // addProduct(
    //     $product_ean13_number,                                                                                         // Product EAN13   // 1234567891234
    //     $product_referance_discription,                                              // Product reference //'Tutorial by Crezzur' 
    //     $Product_name,                                                                      // Product name //'Crezzur test product'
    //     $total_products_in_stock,                                                         // Product quantity //  5
    //     urldecode($product_discription),                                                                 // Product description //'Code by Crezzur (https://crezzur.com)'
    //     urldecode($product_summary_discription),                                                         // Product short description //'Code by Crezzur (https://crezzur.com)'
    //     $product_data_sheet,
    //     $product_price_with_decimal,                                                                                 // Product price //'999.95'
    //     $product_image_link_url,                                                               // Product image//'https://i1.sndcdn.com/avatars-000001420029-vwz0xj-t500x500.jpg'
    //     $product_default_parent_category ,                                                    // Product default category //  1
    //     $product_other_categories_including_main_category_comma_separated                 //array(1, 3)       // All categorys for product (array)//========== find way to handle this //all other categories product should appears on
    // );
    
    // addProduct(
    //     $product_ean13_number,                                                                                         // Product EAN13   // 1234567891234
    //     $product_referance_discription,                                              // Product reference //'Tutorial by Crezzur' 
    //     $Product_name,                                                                      // Product name //'Crezzur test product'
    //     $total_products_in_stock,                                                         // Product quantity //  5
    //     htmlentities($product_discription),                                                     //CONVERT TO HTML ENTITIES            // Product description //'Code by Crezzur (https://crezzur.com)'
    //     htmlentities($product_summary_discription),                                               //CONVERT TO HTML ENTITIES          // Product short description //'Code by Crezzur (https://crezzur.com)'
    //     $product_data_sheet,
    //     $product_price_with_decimal,                                                                                 // Product price //'999.95'
    //     $product_image_link_url,                                                               // Product image//'https://i1.sndcdn.com/avatars-000001420029-vwz0xj-t500x500.jpg'
    //     $product_default_parent_category ,                                                    // Product default category //  1
    //     $product_other_categories_including_main_category_comma_separated                 //array(1, 3)       // All categorys for product (array)//========== find way to handle this //all other categories product should appears on
    // );


    // $string = '<p><strong>A <i>test</i> string with a <a href="#">Test link</a></strong></p>';
    // $encoded_string = htmlentities($string);
    
    // $encoded_string = preg_replace('/&lt;(\/?(strong|b|i|em|br))&gt;/', '<$1>', $encoded_string);
    
    // echo($encoded_string); 


        //this works but breaks html styling send with code,
    // addProduct(
    //     $product_ean13_number,                                                                                         // Product EAN13   // 1234567891234
    //     $product_referance_discription,                                              // Product reference //'Tutorial by Crezzur' 
    //     $Product_name,                                                                      // Product name //'Crezzur test product'
    //     $total_products_in_stock,                                                         // Product quantity //  5
    //     utf8_decode(urldecode(htmlentities($product_discription))),                                                     //CONVERT TO HTML ENTITIES            // Product description //'Code by Crezzur (https://crezzur.com)'
    //     utf8_decode(urldecode(htmlentities($product_summary_discription))),                                               //CONVERT TO HTML ENTITIES          // Product short description //'Code by Crezzur (https://crezzur.com)'
    //     $product_data_sheet,
    //     $product_price_with_decimal,                                                                                 // Product price //'999.95'
    //     $product_image_link_url,                                                               // Product image//'https://i1.sndcdn.com/avatars-000001420029-vwz0xj-t500x500.jpg'
    //     $product_default_parent_category ,                                                    // Product default category //  1
    //     $product_other_categories_including_main_category_comma_separated                 //array(1, 3)       // All categorys for product (array)//========== find way to handle this //all other categories product should appears on
    // );


    addProduct(
        $product_ean13_number,                                                                                         // Product EAN13   // 1234567891234
        $mabuFactuId, //product manufacture id
        $product_referance_discription,                                              // Product reference //'Tutorial by Crezzur' 
        $Product_name,                                                                      // Product name //'Crezzur test product'
        $total_products_in_stock,                                                         // Product quantity //  5
        $product_discription,                                                                 // Product description //'Code by Crezzur (https://crezzur.com)'
        $product_summary_discription,                                                         // Product short description //'Code by Crezzur (https://crezzur.com)'
        $product_data_sheet,
        $product_price_with_decimal,                                                                                 // Product price //'999.95'
        $product_image_link_url,                                                               // Product image//'https://i1.sndcdn.com/avatars-000001420029-vwz0xj-t500x500.jpg'
        $product_default_parent_category,                                                    // Product default category //  1
        $product_other_categories_including_main_category_comma_separated                 //array(1, 3)       // All categorys for product (array)//========== find way to handle this //all other categories product should appears on
    );
    
?>


