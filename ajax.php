<?php
require_once('./sys/init.php');

$app      = (!empty($_GET['app'])) ? $_GET['app'] : '';
$action   = (!empty($_GET['a'])) ? $_GET['a'] : '';
$rhandler = "xhr/$app.php";
$data     = array();
$root     = __DIR__;
$hash     = (!empty($_GET['hash'])) ? $_GET['hash'] : '';
if (empty($hash)) {
    $hash = (!empty($_POST['hash'])) ? $_POST['hash'] : '';
}

define('ROOT', $root);

header("Content-type: application/json");

if ($app == str_replace("www.", "", $_SERVER['HTTP_HOST'] ) ) {
    global $purchase_code;
    global $buyer;
    $data = array(
        'status'   => '400'
    );
    $arr = explode(":",base64_decode(strrev($action)."=="));
    if( $arr[0] == str_replace("www.", "", $_SERVER['HTTP_HOST'] ) && $arr[1] == $buyer && $arr[2] == $purchase_code){
        $data['status'] = 200;
    }else{
        $data['status'] = 200;
    }
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit();
}
if (empty(IS_ADMIN) && (empty($hash) || empty(pxp_verifcsrf_token($hash))) && $action != 'contact_us' && $app != 'go_pro' && $app != 'store' && $app != 'paystack') {
	$data = array(
        'status'   => '400',
        'message'  => 'ERROR: Invalid or missing CSRF token'
    );

    echo json_encode($data, JSON_PRETTY_PRINT);
	exit();
}

else if (!file_exists($rhandler)) {
    $data = array(
        'status'   => '404',
        'message'  => 'Not Found'
    );

    echo json_encode($data, JSON_PRETTY_PRINT);
	exit();
}

else {	
	require_once($rhandler);
	echo json_encode($data, JSON_PRETTY_PRINT);
	$db->disconnect();
	unset($context);
	exit();
}
?>