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

$manu_name = $data['new_manufacture_name'];
$secure_key = $data['security_key'];
$manufacture_blank_xml_link = $data['eeza_server_link'] . '/prestashop_manufacture_xml_schema_blank'; //http://127.0.0.1:8080/prestashop_manufacture_xml_schema_blank



define('PS_SHOP_PATH', $data['prestashop_websitelink']);


// Check if the client use the correct secure_key, url to use: www.yourstore.com/yourbackoffice/importmyproduct.php?secure_key=ed3fa1ce558e1c2528cfbaa3f99403
if(PS_WS_AUTH_KEY != $secure_key) {

	// If the secure_key is not set our not equal the php page will stop running.
	// die('UNAUTHORIZED: We dont want you on this page!');
	die('Error:prestashop webservice key does not match one expected by category adding script : open category adding script to see the expected authorization key');

}
 
    // $webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);

function AddManufacturer($manu_name,$manufacture_blank_xml_link) { 

    // global $webService;

    $webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);

    // $xml = $webService_->get(array('resource' => 'manufacturers?schema=synopsis&ws_key='.PS_WS_AUTH_KEY));
       
    // $xml = $webService -> get(array('url' => 'http://127.0.0.1:8080/prestashop_manufacture_xml_schema_blank')); //get link :: data from extraction server;//prestashop had issuew giving category schema blank
    $xml = $webService -> get(array('url' => $manufacture_blank_xml_link)); //get link :: data from extraction server;//prestashop had issuew giving category schema blank


    // printf($xml);
    $resources = $xml->children()->children();
    $resources->name = $manu_name;  
    $resources->active = 1; 
    unset($resources -> link_rewrite);

    // $webService->add(
    //     array(
    //     'resource' => 'manufacturers',
    //     'active' => array(),
    //     'postXml' => $xml->asXML()
    //     )
    // );



    try {

        $opt = array('resource' => 'manufacturers', 'ws_key' => PS_WS_AUTH_KEY); //edited to support method http://my-website/api/resources?ws_key=my-webservice-key //similar edit made in file [ './custom_prestashop_webservice_api_php_script/PSWebServiceLibrary.php' ] to support this feature, only made for adding part of the file, so may need to duplicate if you to use other function in file such as [ edit, delete ] etc

        $opt['postXml'] = $xml -> asXML();
        
        $xml = $webService -> add($opt);

    } catch (PrestaShopWebserviceException $ex) {

        // czarodziej_log("PS/SYNCHRONIZACJA KATEGORII: " . $e -> getMessage(), 1);
        
        // my log function
    
    }




}




AddManufacturer($manu_name, $manufacture_blank_xml_link);





?>