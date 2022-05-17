<?php 
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once('.'.DIRECTORY_SEPARATOR.'sys'.DIRECTORY_SEPARATOR.'init.php');

$response_data     = array();
$api_version       = isset($_GET['v']) && !empty($_GET['v']) ? $_GET['v'] : '';
$api_resource       = (isset($_GET['resource']) && !empty($_GET['resource'])) ? ucfirst ($_GET['resource']) : '';
$api_resource_id        = (isset($_GET['resource_id']) && !empty($_GET['resource_id'])) ? $_GET['resource_id'] : '';


if (empty($_POST['server_key']) || $_POST['server_key'] != $config['server_key']) {
    $response_data       = array(
        'code'     => '400',
        'status'   => 'Bad Request',
        'errors'         => array(
            'error_id'   => '19',
            'error_text' => 'Server Key Error'
        )
    );
    Generic::json($response_data);
}

$non_allowed = array('password','email_code','login_token','edit');

$allowed = array('Auth','User','Post','Story','Settings','Messages', 'Misc');
$classes = array('Misc' => 'MiscEndPoint', 'Auth' => 'Auth','User' => 'UserEndPoint','Post' => 'PostsEndPoint','Story' => 'StoryEndPoint','Settings' => 'SettingsEndPoint','Messages' => 'MessagesEndPoint');
$api               = "endpoint".DIRECTORY_SEPARATOR."$api_version".DIRECTORY_SEPARATOR.$classes[$api_resource].".php"; 

if (file_exists($api) && in_array($api_resource, $allowed)) {
	require_once $api;

	$response_data = new $classes[$api_resource]($api_resource_id);
}else{
	$response_data       = array(
        'code'     => '400',
        'status'   => 'Bad Request',
        'errors'         => array(
            'error_id'   => '1',
            'error_text' => 'Error: 404 API Version Not Found'
        )
    );
    Generic::json($response_data);
}

$db->disconnect();