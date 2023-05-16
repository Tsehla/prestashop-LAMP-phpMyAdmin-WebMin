<?php

 

define('DEBUG', true);

define('_PS_DEBUG_SQL_', true);

// define('PS_SHOP_PATH', 'http://127.0.0.1/prestashop');

define('PS_WS_AUTH_KEY', '4ERQKSBATUWAMU1BN64M1LIY23W776JZ');

require_once ('./custom_prestashop_webservice_api_php_script/PSWebServiceLibrary.php');

// include_once ('core_config.php');
include_once ('./config/config.inc.php');

// my config for spec. func.


//posted variables
$data = json_decode(file_get_contents('php://input'), true); //get posted data in json en decode

$n_name = $data['new_category_name'];

$n_desc = $data['new_category_discription'];

$n_link_rewrite = $data['seo_category_link_rewrite'];

$n_meta_title = $data['seo_category_meta_title'];

$n_meta_description = $data['seo_ctaegory_meta_description'];

$n_meta_keywords = $data['seo_category_comma_seperated_keywords'];

$n_id_parent = $data['category_parent_id']; //parent category / [ 2 ] is home categerry / [ 1 ] is category under main/root category for prestashop //so if single store, never use category above [ 2 ]

$n_active = $data['is_product_active']; //set as active [ 1 ] or [ 0 ] disabled

$n_l_id = $data['category_l_id'];//dont know what this is

$n_is_root_category = 0;//if the category is not meant to be parent/main meaning a category unders [ root ]  like [ home ] category, set this to [ 0 ] else set to [ 1 ], this overrides [ $n_id_parent  ]

$category_schema_markup_website_server_link_url = $data['category_schema_markup_website_server_link_url'] .'/prestashop_category_xml_schema_blank';//'http://127.0.0.1:8080/prestashop_product_xml_schema_blank';// //get link :: data from extraction server;//prestashop had issuew giving category schema blank
$secure_key = $data['security_key'];


define('PS_SHOP_PATH', $data['prestashop_websitelink']);


// Check if the client use the correct secure_key, url to use: www.yourstore.com/yourbackoffice/importmyproduct.php?secure_key=ed3fa1ce558e1c2528cfbaa3f99403
if(PS_WS_AUTH_KEY != $secure_key) {

	// If the secure_key is not set our not equal the php page will stop running.
	// die('UNAUTHORIZED: We dont want you on this page!');
	die('Error:prestashop webservice key does not match one expected by category adding script : open category adding script to see the expected authorization key');

}
 

$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);

 

function PS_new_category($n_id_parent, $n_active, $n_l_id, $n_name, $n_desc, $n_link_rewrite, $n_meta_title, $n_meta_description, $n_meta_keywords, $category_schema_markup_website_server_link_url) {

 

global $webService;

 

// $xml = $webService -> get(array('url' => PS_SHOP_PATH . '/api/categories?schema=blank'));
$xml = $webService -> get(array('url' => $category_schema_markup_website_server_link_url )); //get link :: data from extraction server;//prestashop had issuew giving category schema blank


$resources = $xml -> children() -> children();


unset($resources -> id);

unset($resources -> position);

unset($resources -> id_shop_default);

unset($resources -> date_add);

unset($resources -> date_upd);

$resources -> active = $n_active;

$resources -> id_parent = $n_id_parent;

$resources -> id_parent['xlink:href'] = PS_SHOP_PATH . '/api/categories/' . $n_id_parent;

$node = dom_import_simplexml($resources -> name -> language[0][0]);

$no = $node -> ownerDocument;

$node -> appendChild($no -> createCDATASection($n_name));

$resources -> name -> language[0][0] = $n_name;

$resources -> name -> language[0][0]['id'] = $n_l_id;

$resources -> name -> language[0][0]['xlink:href'] = PS_SHOP_PATH . '/api/languages/' . $n_l_id;

$node = dom_import_simplexml($resources -> description -> language[0][0]);

$no = $node -> ownerDocument;

$node -> appendChild($no -> createCDATASection($n_desc));

$resources -> description -> language[0][0] = $n_desc;

$resources -> description -> language[0][0]['id'] = $n_l_id;

$resources -> description -> language[0][0]['xlink:href'] = PS_SHOP_PATH . '/api/languages/' . $n_l_id;

$node = dom_import_simplexml($resources -> link_rewrite -> language[0][0]);

$no = $node -> ownerDocument;

$node -> appendChild($no -> createCDATASection($n_link_rewrite));

$resources -> link_rewrite -> language[0][0] = $n_link_rewrite;

$resources -> link_rewrite -> language[0][0]['id'] = $n_l_id;

$resources -> link_rewrite -> language[0][0]['xlink:href'] = PS_SHOP_PATH . '/api/languages/' . $n_l_id;

$node = dom_import_simplexml($resources -> meta_title -> language[0][0]);

$no = $node -> ownerDocument;

$node -> appendChild($no -> createCDATASection($n_meta_title));

$resources -> meta_title -> language[0][0] = $n_meta_title;

$resources -> meta_title -> language[0][0]['id'] = $n_l_id;

$resources -> meta_title -> language[0][0]['xlink:href'] = PS_SHOP_PATH . '/api/languages/' . $n_l_id;

$node = dom_import_simplexml($resources -> meta_description -> language[0][0]);

$no = $node -> ownerDocument;

$node -> appendChild($no -> createCDATASection($n_meta_description));

$resources -> meta_description -> language[0][0] = $n_meta_description;

$resources -> meta_description -> language[0][0]['id'] = $n_l_id;

$resources -> meta_description -> language[0][0]['xlink:href'] = PS_SHOP_PATH . '/api/languages/' . $n_l_id;

$node = dom_import_simplexml($resources -> meta_keywords -> language[0][0]);

$no = $node -> ownerDocument;

$node -> appendChild($no -> createCDATASection($n_meta_keywords));

$resources -> meta_keywords -> language[0][0] = $n_meta_keywords;

$resources -> meta_keywords -> language[0][0]['id'] = $n_l_id;

$resources -> meta_keywords -> language[0][0]['xlink:href'] = PS_SHOP_PATH . '/api/languages/' . $n_l_id;

try {

$opt = array('resource' => 'categories', 'ws_key' => PS_WS_AUTH_KEY); //edited to support method http://my-website/api/resources?ws_key=my-webservice-key //similar edit made in file [ './custom_prestashop_webservice_api_php_script/PSWebServiceLibrary.php' ] to support this feature, only made for adding part of the file, so may need to duplicate if you to use other function in file such as [ edit, delete ] etc

$opt['postXml'] = $xml -> asXML();

$xml = $webService -> add($opt);

} catch (PrestaShopWebserviceException $ex) {

// czarodziej_log("PS/SYNCHRONIZACJA KATEGORII: " . $e -> getMessage(), 1);

// my log function

}

}

 

// simple use

 

// $n_name = 'New category name';

// $n_desc = 'New desc ...';

// $n_link_rewrite = 'someone_rewrite';

// $n_meta_title = 'meta-title';

// $n_meta_description = 'meta desc';

// $n_meta_keywords = 'some,one,keywords';

// $n_id_parent = '2'; //parent category / [ 2 ] is home categerry / [ 1 ] is category under main/root category for prestashop //so if single store, never use category above [ 2 ]

// $n_active = '1'; //set as active [ 1 ] or [ 0 ] disabled

// $n_l_id = '1';

// $n_is_root_category = 0;//if the category is not meant to be parent/main meaning a category unders [ root ]  like [ home ] category, set this to [ 0 ] else set to [ 1 ], this overrides [ $n_id_parent  ]

// $category_schema_markup_website_server_link_url = 'http://127.0.0.1:8080/prestashop_product_xml_schema_blank';// //get link :: data from extraction server;//prestashop had issuew giving category schema blank

// run

PS_new_category($n_id_parent, $n_active, $n_l_id, $n_name, $n_desc, $n_link_rewrite, $n_meta_title, $n_meta_description, $n_meta_keywords, $category_schema_markup_website_server_link_url);

 

?>