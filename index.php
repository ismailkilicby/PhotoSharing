<?php
require_once('./sys/init.php');

$root     = __DIR__;
define('ROOT', $root);
$app      = (!empty($_GET['app'])) ? $_GET['app'] : 'home';
$apph     = (!empty($_GET['apph'])) ? $_GET['apph'] : 'home';
$app_cont = "apps/$theme/$app/handlers/$apph.php";

if (file_exists($app_cont)) {
	require_once($app_cont);
}else{

}
if (!empty($_GET)) {
    foreach ($_GET as $key => $value) {
        if (!is_array($value)) {
            $value = preg_replace('/on[^<>=]+=[^<>]*/m', '', $value);
            $value = preg_replace('/\((.*?)\)/m', '', $value);
            $_GET[$key] = strip_tags($value);
        }
    }
}
if (!empty($_REQUEST)) {
    foreach ($_REQUEST as $key => $value) {
        if (!is_array($value)) {
            $value = preg_replace('/on[^<>=]+=[^<>]*/m', '', $value);
            $_REQUEST[$key] = strip_tags($value);
        }
    }
}
if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
        if (!is_array($value)) {
            $value = preg_replace('/on[^<>=]+=[^<>]*/m', '', $value);
            $_POST[$key] = strip_tags($value);
        }
    }
}
if( ISSET( $context['user'] ) ){
	if($context['user']["active"] == 0){
		$app      = 'notactive';
		$apph     = 'notactive';
		$app_cont = "apps/$theme/notactive/handlers/notactive.php";
		require_once($app_cont);
	}
	if($config['subscription_on_signup'] == 'on' && $config['pro_system'] == 'on' && $context['user']['is_pro'] == 0 ){
		$app_cont = "apps/$theme/go_pro/handlers/go_pro.php";
		require_once($app_cont);
	}else{
		if (($context['user']['startup_avatar'] == 0 || $context['user']['startup_info'] == 0 || $context['user']['startup_follow'] == 0) && $app != 'startup' && $context['user']['active']) {
			$app      = 'startup';
			$apph     = 'startup';
			header("Location: startup");
			exit;
		}
	}
}

if (empty($context['content'])) {
	header("Location: $site_url/404");
	exit;
}

$context['header'] = '';
if ($config['header']) {
	$context['header'] = $pixelphoto->PX_LoadPage('main/templates/header/header');
}
// $context['footer'] = '';
// if ($config['footer']) {
// 	$context['footer'] = $pixelphoto->PX_LoadPage('main/templates/footer/footer');
// }
$context['footer'] = $pixelphoto->PX_LoadPage('main/templates/footer/footer');

$context['page_title'] = strip_tags($context['page_title']);
if(isset($context['post_data']['description'])){
	$context['post_data']['description'] = strip_tags($context['post_data']['description']);
}
echo $pixelphoto->PX_LoadPage('main/templates/container',
	                          array('CONTENT'=>$context['content'],
	                          'THEME_URL' => $context['theme_url'],
	                          'HEADER' => $context['header'],
	                          'FOOTER' => $context['footer'],
	                          'TITLE' => $context['page_title']));
$db->disconnect();
unset($context);