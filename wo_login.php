<?php
require_once('./sys/init.php');
global $db;
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $app_id        = $config['wowonder_app_ID'];
    $app_secret    = $config['wowonder_app_key'];
    $wowonder_url  = $config['wowonder_domain_uri'];
    $code          = $_GET['code'];
    $url           = $wowonder_url . "/authorize?app_id={$app_id}&app_secret={$app_secret}&code={$code}";
    $get           = file_get_contents($url);
    $wo_json_reply = json_decode($get, true);
    $access_token  = '';
    if (is_array($wo_json_reply) && isset($wo_json_reply['access_token'])) {
        $access_token    = $wo_json_reply['access_token'];
        $type            = "get_user_data";
        $url             = $wowonder_url . "/api_request?access_token={$access_token}&type={$type}";
        $user_data_json  = file_get_contents($url);
        $user_data_array = json_decode($user_data_json, true);
        if (is_array($user_data_array) && !empty($user_data_array) && isset($user_data_array['user_data'])) {
            $user_data  = $user_data_array['user_data'];
            $user_email = $user_data['email'];
            if (User::userEmailExists($user_email)) {
                $db->where('email', $user_email);
                $login               = $db->getOne(T_USERS);
                $session_id          = sha1(rand(11111, 99999)) . time() . md5(microtime());
                $insert_data         = array(
                    'user_id' => $login->user_id,
                    'session_id' => $session_id,
                    'time' => time()
                );
                $insert              = $db->insert(T_SESSIONS, $insert_data);
                $_SESSION['user_id'] = $session_id;
                setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
                header("Location: " . $config['site_url']);
                exit();
    		}else{
    		    $str          = md5(microtime());
                $id           = substr($str, 0, 9);
                $user_uniq_id = (User::userNameExists($id) === false) ? $id : 'u_' . $id;
                $first_name   = (isset($user_data['first_name'])) ? User::secure($user_data['first_name'], 0) : '';
                $last_name    = (isset($user_data['last_name'])) ? User::secure($user_data['last_name'], 0) : '';
                $gender       = (isset($user_data['gender'])) ? User::secure($user_data['gender'], 0) : 'male';
                $username     = (User::secure($user_data['username']));
                $provider     = ($wowonder_url . "/{$username}");
                $re_data      = array(
                    'username' => User::secure($user_uniq_id, 0),
                    'email' => User::secure($user_email, 0),
                    'password' => password_hash($user_email, PASSWORD_DEFAULT),
                    'ip_address' => '0.0.0.0',
                    'email_code' => User::secure(md5($user_uniq_id), 0),
                    'fname' => $first_name,
                    'lname' => $last_name,
                    'last_seen' => time(),
                    'gender' => User::secure($gender),
                    'active' => '1',
                    'language' => 'english',
                    'registered' => date('Y') . '/' . intval(date('m'))
                );

                if (!empty($user_data['avatar'])) {
                    $media = new Media();
                    $re_data['avatar'] = User::secure($media->ImportImage($user_data['avatar'], 1));
                }
                
                $user_id     = $db->insert(T_USERS, $re_data);
                $signup      = false;

                if (!empty($user_id)) {
                	$signup      = true;
                	$session_id  = sha1(rand(11111, 99999)) . time() . md5(microtime());
        	        $insert_data = array(
        	            'user_id' => $user_id,
        	            'session_id' => $session_id,
        	            'time' => time()
        	        );
        			$insert              = $db->insert(T_SESSIONS, $insert_data);
        	        $_SESSION['user_id'] = $session_id;
        	        setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
        	        header("Location: " . $config['site_url']);
                    exit();
                }
    		}
        }
    } else {
        echo 'Error found, please try again later.' . "<a href='" . $config['site_url'] . "'>Return back</a>";
    }
} else {
    echo "<a href='" . $config['site_url'] . "'>Return back</a>";
}
?>