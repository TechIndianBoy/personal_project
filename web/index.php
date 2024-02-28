<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', true);
//error_reporting(E_ALL ^ E_NOTICE);
defined('BILL_UPLOAD_URL')  or define('BILL_UPLOAD_URL',    'bills/');
defined('STORE_IMAGE_URL')  or define('STORE_IMAGE_URL',    'stores/');
defined('IMAGE_UPLOAD_URL')     or define('IMAGE_UPLOAD_URL',     'posmimages/');
defined('PYTHON_API_URL')         or define('PYTHON_API_URL', 'http://localhost:5000');
defined('PROFILE_UPLOAD_URL')     or define('PROFILE_UPLOAD_URL',     'userprofiles/');
defined('PROFILE_DOCUMENT_UPLOAD_URL')     or define('PROFILE_DOCUMENT_UPLOAD_URL',     'userprofile_documents/');
defined('IMAGE_TASK_UPLOAD_URL')     or define('IMAGE_TASK_UPLOAD_URL',     'image_task/');
defined('MYSTERY_IMAGE_UPLOAD_URL')     or define('MYSTERY_IMAGE_UPLOAD_URL',     'mystery_image_task/');
defined('OPEN_STOCK_IMAGE')     or define('OPEN_STOCK_IMAGE',     'open_stock_image/');
defined('CLOSE_STOCK_IMAGE')     or define('CLOSE_STOCK_IMAGE',     'close_stock_image/');
defined('MENU_REQUEST_IMAGE')     or define('MENU_REQUEST_IMAGE',     'menu_request_images/');
defined('MENU_REQUEST_LOGO')     or define('MENU_REQUEST_LOGO',     'menu_request_logos/');
defined('ECOMM_PRODUCT_IMG_UPLOAD')     or define('ECOMM_PRODUCT_IMG_UPLOAD',     'ecommerce/products/'); 
defined('ECOMM_OFFER_IMG_UPLOAD')     or define('ECOMM_OFFER_IMG_UPLOAD',     'ecommerce/offer_images/');
//Firebase notifications

// defined('FCM_API_KEY')          or define('FCM_API_KEY',             'AAAAWNF_wos:APA91bHj536hOC98e43EzAEd4oQEC9MbX5zQp8yFhNd9-TOjT4Z9_jMO1_QLqYVBQOYXlk-siXOG8nkteT_2qnUxxlqHEqZR4Jy9G4jF2mhFBf53Opjzn2qj4EsJmfjhw904frQq6fpr');
defined('FCM_API_KEY')          or define('FCM_API_KEY',         'AAAA-oB_9pw:APA91bEdGBLCi9ldFpYQHKuj_OK9_sextwvClkCDQg8Kean2nqWmhkFbLGjP0B-BXQuGJ7KsrQRHXne8FFCmlfMctbE60eUOEp6INlLWDjoREpVLmP89FeguLcHWF_0stzyxQYmFoQrR');
defined('FCM_MESSAGE_URL')      or define('FCM_MESSAGE_URL',         'https://fcm.googleapis.com/fcm/send');
defined('MESSAGE_SUBJECT')      or define('MESSAGE_SUBJECT',         'King Service');

defined('OUTLET_CODE') or define('OUTLET_CODE', 'AND Usr.dummy_rocode::integer IN (100956, 101928, 101978, 102129, 102427, 102493, 102526, 102925, 103315, 104279, 104312, 105272, 105297, 105595, 105599, 105705, 105707, 105805, 106342, 106348, 106498, 106538, 107371, 107391, 107421, 108211, 108309, 111875, 112124, 114253, 114255, 114286, 114294, 114311, 236412, 271021, 272592, 310705, 314616, 601085, 610546, 618107, 618252, 624151, 664989, 667020, 682155, 682388, 685628, 706688)');
defined('TSE_USER_ID') or define ('TSE_USER_ID', 'AND Usr.id IN (174, 176, 179, 367, 368, 371, 428, 431, 479, 711, 787)');

require(__DIR__ . '/../vendor/autoload.php');
// require(__DIR__ . '/../vendor/phpqrcode/phpqrcode.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');


$config = require(__DIR__ . '/../config/web.php');
$host = $_SERVER['HTTP_HOST'];
if(isset($host) && $host == 'appapi.cargill.bigcityexperience.co.in'){
// Already Allow from any origin
}
else {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
    // should do a check here to match $_SERVER['HTTP_ORIGIN'] to a
    // whitelist of safe domains
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // cache for 1 day
    }
// Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
}

(new yii\web\Application($config))->run();
