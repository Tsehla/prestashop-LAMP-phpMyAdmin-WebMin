<?php



    define('DEBUG', true);

    define('_PS_DEBUG_SQL_', true);

    // define('PS_SHOP_PATH', 'http://127.0.0.1/prestashop');

    define('PS_WS_AUTH_KEY', '4ERQKSBATUWAMU1BN64M1LIY23W776JZ');

    require_once ('./custom_prestashop_webservice_api_php_script/PSWebServiceLibrary.php');

    // include_once ('core_config.php');
    include_once ('./config/config.inc.php');

    //posted variables
    $data = json_decode(file_get_contents('php://input'), true); //get posted data in json en decode

    $name = $data['new_manufacture_name'];
    $secure_key = $data['security_key'];
    $url = $data['prestashop_websitelink'];


    define('PS_SHOP_PATH', $data['prestashop_websitelink']);


    // Check if the client use the correct secure_key, url to use: www.yourstore.com/yourbackoffice/importmyproduct.php?secure_key=ed3fa1ce558e1c2528cfbaa3f99403
    if(PS_WS_AUTH_KEY != $secure_key) {

        // If the secure_key is not set our not equal the php page will stop running.
        // die('UNAUTHORIZED: We dont want you on this page!');
        die('Error:prestashop webservice key does not match one expected by category adding script : open category adding script to see the expected authorization key');

    }
    





    function GetManufacturerID($name) {
        
        $webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);

        // $xml = $webService->get(array('resource' => 'manufacturers?display=[id]&filter[name]='.$name .'&ws_key=4ERQKSBATUWAMU1BN64M1LIY23W776JZ'));
        // // $xml = $webService->get(array('resource' => 'api/manufacturers?display=[name,id]&ws_key=4ERQKSBATUWAMU1BN64M1LIY23W776JZ'));
        // echo $xml->children()->children()->manufacturer->id;

        try {
        //     $webService = new PrestaShopWebservice($url, PS_WS_AUTH_KEY, DEBUG);
        //     $opt = array(
        //         'resource' =>'manufacturers',
        //         'display'  => '[id]',
        //         'filter[name]'  => $name,
        //         'ws_key' => PS_WS_AUTH_KEY
        //     );      
        //     $xml = $webService->get($opt);      
        //     echo $xml;   
        //     return $xml->children()->children()->manufacturer->id;


            $xml = $webService->get(array('resource' => 'manufacturers?display=[id]&filter[name]='.$name .'&ws_key='.PS_WS_AUTH_KEY));
            // $xml = $webService->get(array('resource' => 'api/manufacturers?display=[name,id]&ws_key=4ERQKSBATUWAMU1BN64M1LIY23W776JZ'));
            echo $xml->children()->children()->manufacturer->id;


        }   catch (PrestaShopWebserviceException $e)    {       
                $trace = $e->getTrace();
        } 
        
        
    }

    GetManufacturerID($name);

?>
