<?php 
// +------------------------------------------------------------------------+
// | @author Deen Doughouz (DoughouzForest)
// | @author_url 1: http://www.wowonder.com
// | @author_url 2: http://codecanyon.net/user/doughouzforest
// | @author_email: wowondersocial@gmail.com   
// +------------------------------------------------------------------------+
// | WoWonder - The Ultimate Social Networking Platform
// | Copyright (c) 2017 WoWonder. All rights reserved.
// +------------------------------------------------------------------------+
require_once('./sys/init.php');
$root     = __DIR__;
define('ROOT', $root);
$app      = (!empty($_GET['app'])) ? $_GET['app'] : 'home';
$apph     = (!empty($_GET['apph'])) ? $_GET['apph'] : 'home';
$app_cont = "apps/$theme/$app/handlers/$apph.php";
// echo $app_cont;
// $context['apphhh'] = $apph;
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
if (file_exists($app_cont)) {
    require_once($app_cont);
}
else{
    require_once("apps/$theme/404/handlers/404.php");
}
if( ISSET( $context['user'] ) ){
    if($context['user']["active"] == 0){
        $app      = 'notactive';
        $apph     = 'notactive';
        $app_cont = "apps/$theme/notactive/handlers/notactive.php";
        require_once($app_cont);
    }
    if($config['subscription_on_signup'] == 'on' && $context['user']['is_pro'] == 0 ){
		$app_cont = "apps/$theme/go_pro/handlers/go_pro.php";
		require_once($app_cont);
	}else{
        if (($context['user']['startup_avatar'] == 0 || $context['user']['startup_info'] == 0 || $context['user']['startup_follow'] == 0) && $app != 'startup' && $context['user']['active']) {
            $app      = 'startup';
            $apph     = 'startup';
            $app_cont = "apps/$theme/$app/handlers/$apph.php";
            require_once($app_cont);
        }
    }
}

if (empty($context['content'])) {
    require_once("apps/$theme/404/handlers/404.php");
}

// if ($context['app_name'] == 'startup') {
//     $data['url'] = 'startup';
// }
// elseif ($context['app_name'] == '404') {
//     $data['url'] = '404';
// }
// elseif ( $context['app_name'] == 'posts') {
//     $data['url'] = 'post/'.$context['post_data']['post_id'];
// }
// elseif ( $context['app_name'] == 'notactive') {
//     $data['url'] = '';
// }
// elseif (($app == 'home' && $apph == 'home')) {
//     $data['url'] = '';
// }
// elseif ($app == 'explore' && $apph == 'explore') {
//     $data['url'] = 'explore';
// }
// elseif ($app == 'explore' && $apph == 'people') {
//     $data['url'] = 'explore/people';
// }
// elseif ($app == 'terms' && $context['pname'] == 'terms_of_use') {
//     $data['url'] = 'terms-of-use';
// }
// elseif ($app == 'terms' && $context['pname'] == 'privacy_and_policy') {
//     $data['url'] = 'privacy-and-policy';
// }
// elseif ($app == 'terms' && $context['pname'] == 'about_us') {
//     $data['url'] = 'about-us';
// }
// elseif ($app == 'terms' && $context['pname'] == 'contact_us') {
//     $data['url'] = 'contact_us';
// }
// elseif ($app == 'profile' && $apph == 'profile' && empty($_GET['page'])) {
//     $data['url'] = '@'.$context['user_data']['username'];
// }
// elseif ($app == 'profile' && $apph == 'profile' && !empty($_GET['page']) && $_GET['page'] == 'posts') {
//     $data['url'] = '@'.$context['user_data']['username'].'/posts';
// }
// elseif ($app == 'profile' && $apph == 'profile' && !empty($_GET['page']) && $_GET['page'] == 'followers') {
//     $data['url'] = '@'.$context['user_data']['username'].'/followers';
// }
// elseif ($app == 'profile' && $apph == 'profile' && !empty($_GET['page']) && $_GET['page'] == 'following') {
//     $data['url'] = '@'.$context['user_data']['username'].'/following';
// }
// elseif ($app == 'profile' && $apph == 'profile' && !empty($_GET['page']) && $_GET['page'] == 'favourites') {
//     $data['url'] = '@'.$context['user_data']['username'].'/favourites';
// }
// elseif ($app == 'settings' && $apph == 'settings' && !empty($_GET['page']) && $_GET['page'] == 'general') {
//     $data['url'] = 'settings/general/'.$context['me']['username'];
// }
// elseif ($app == 'settings' && $apph == 'settings' && !empty($_GET['page']) && $_GET['page'] == 'delete') {
//     $data['url'] = 'settings/delete/'.$context['me']['username'];
// }
// elseif ($app == 'settings' && $apph == 'settings' && !empty($_GET['page']) && $_GET['page'] == 'password') {
//     $data['url'] = 'settings/password/'.$context['me']['username'];
// }
// elseif ($app == 'settings' && $apph == 'settings' && !empty($_GET['page']) && $_GET['page'] == 'profile') {
//     $data['url'] = 'settings/profile/'.$context['me']['username'];
// }
// elseif ($app == 'settings' && $apph == 'settings' && !empty($_GET['page']) && $_GET['page'] == 'privacy') {
//     $data['url'] = 'settings/privacy/'.$context['me']['username'];
// }
// elseif ($app == 'settings' && $apph == 'settings' && !empty($_GET['page']) && $_GET['page'] == 'notifications') {
//     $data['url'] = 'settings/notifications/'.$context['me']['username'];
// }
// elseif ($app == 'settings' && $apph == 'settings' && !empty($_GET['page']) && $_GET['page'] == 'verification') {
//     $data['url'] = 'settings/verification/'.$context['me']['username'];
// }
// elseif ($app == 'settings' && $apph == 'settings' && !empty($_GET['page']) && $_GET['page'] == 'manage_sessions') {
//     $data['url'] = 'settings/manage_sessions/'.$context['me']['username'];
// }
// elseif ($app == 'settings' && $apph == 'settings' && !empty($_GET['page']) && $_GET['page'] == 'blocked') {
//     $data['url'] = 'settings/blocked/'.$context['me']['username'];
// }
// elseif ($app == 'messages' && $apph == 'messages') {
//     $data['url'] = 'messages';
// }

$data['url'] = $context['page_link'];
$context['header'] = '';
if ($config['header']) {
    $context['header'] = $pixelphoto->PX_LoadPage('main/templates/header/header');
}
$context['footer'] = '';
if ($config['footer']) {
    $context['footer'] = $pixelphoto->PX_LoadPage('main/templates/footer/footer');
}
if (!empty($context['settings_page'])) {
    $data['settings_page'] = $context['settings_page'];
}

$data['header'] = $config['header'];
$data['footer'] = $config['footer'];
$data['page_title'] = $context['page_title'];
$data['app_name'] = $context['app_name'];

?>
<input type="hidden" id="json-data" value='<?php echo htmlspecialchars(json_encode($data));?>'>
<?php
echo $context['content'];
$db->disconnect();
unset($context);
exit();