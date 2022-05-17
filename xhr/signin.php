<?php 

if ($action == 'signin') {

    $error  = false;

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = lang('enter_ur_n_and_p');
    } 

    if(empty($error)){
        
        $login = User::loginUser();
        if ($login === true) {
            $data['status'] = 200;
        }

        else{
            $data['status']  = 400;
            $data['message'] = lang('invalid_un_or_passwd');
        }
    }

    else{
        $data['status'] = 400;
        $data['message'] = $error;
    }
}

if ($action == 'reset') {
    $error = '';
    if ($config['recaptcha'] == 'on' && !empty($config['recaptcha_secret_key'])) {
        if (empty($_POST['g-recaptcha-response'])) {
            $error = lang('please_fill_fields');
        }
        else{
            $recaptcha_data = array(
            'secret' => $config['recaptcha_secret_key'],
            'response' => $_POST['g-recaptcha-response']
            );

            $verify = curl_init();
            curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($verify, CURLOPT_POST, true);
            curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($recaptcha_data));
            curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($verify);
            $response = json_decode($response);
            if (!$response->success) {
                $error = lang('reCaptcha_error');
            }
        }
    }
    if (empty($_POST['email'])) {
        $error = lang('please_fill_fields');
    } 
    if(!User::userEmailExists($_POST['email'])){
        $error = lang('email_not_exists');
    }

    if (empty($error)) {

        $user = new User;
        $user_data = $user->setUserByEmail($_POST['email'])->getUser();

        $email_code = sha1(time() + rand(111,999));
        $update_data = array('email_code' => $email_code);
        $db->where('user_id', $user_data->user_id);
        $update = $db->update(T_USERS, $update_data);

        $password_text = "Hello {{NAME}},
<br><br>".lang('v2_reset_password_msg')."
<br>
<a href=\"{{RESET_LINK}}\">".lang('v2_reset_password')."</a>
<br><br>
{{SITE_NAME}} Team.";

        $password_text = str_replace(
            array("{{NAME}}","{{SITE_NAME}}", "{{RESET_LINK}}"),
            array($user_data->name, $config['site_name'], $site_url . '/reset-password/' . $email_code),
            $password_text 
        );

        $send_email_data = array(
            'from_email' => $config['site_email'],
            'from_name' => $config['site_name'],
            'to_email' => $_POST['email'],
            'to_name' => $user_data->name,
            'subject' => lang('v2_reset_password'),
            'charSet' => 'UTF-8',
            'message_body' => $password_text,
            'is_html' => true
        );
        $send_message = Generic::sendMail($send_email_data);
        if ($send_message) {
            $data['status'] = 200;
            $data['message'] = lang('sent_email');
        } else {
            $data['status'] = 400;
            $data['message'] = lang('unknown_error');
        }
    } else {
        $data['status'] = 400;
        $data['message'] = $error;
    }
}

if ($action == 'reset-new') {

    $error  = false;
    $post   = array();
    $post[] = (empty($_POST['password']) || empty($_POST['confirm_passwd']));
    $post[] = (empty($_POST['code']));

    if (in_array(true, $post)) {
        $error = lang('please_fill_fields');
    } else {
        if($_POST['password'] != $_POST['confirm_passwd']){
            $error = lang('password_not_match');
        } else if (strlen($_POST['confirm_passwd']) < 4) {
            $error = lang('password_is_short');
        }
    }
    if (empty($error)) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user_id = User::validateCode($_POST['code']);
        if (!$user_id) {
            exit;
        }

        $update_data = array(
            'password' => $password,
            'email_code' => sha1(microtime())
        );

        $update = $db->where('user_id', $user_id)->update(T_USERS, $update_data);
        if ($update) {
            $platform_details = $user->getUserBrowser();
            $session_id  = sha1(rand(11111, 99999)) . time() . md5(microtime());
            $insert_data = array(
                'user_id' => $user_id,
                'session_id' => $session_id,
                'time' => time(),
                'platform_details'  => json_encode($platform_details),
                'platform' => $platform_details['platform']
            );
            $insert              = $db->insert(T_SESSIONS, $insert_data);
            $_SESSION['user_id'] = $session_id;
            setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
            $data['status'] = 200;
            $data['link'] = $site_url;
        }
    } else {
        $data['status'] = 400;
        $data['message'] = $error;
    }
}