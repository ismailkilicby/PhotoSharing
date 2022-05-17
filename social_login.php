<?php
require_once('sys/init.php');
$types    = array(
    'Google',
    'Facebook',
    'Twitter',
    'Vkontakte',
    'LinkedIn',
    'Instagram',
    'QQ',
    'WeChat',
    'Discord',
    'Mailru'
);
$provider = "";
if (isset($_GET['provider']) && in_array($_GET['provider'], $types)) {
    $provider = $user::secure($_GET['provider']);
}
require_once('./sys/import3p/social-login/config.php');
require_once('./sys/import3p/social-login/autoload.php');
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;
if (isset($_GET['provider']) && in_array($_GET['provider'], $types)) {
    try {
        $hybridauth   = new Hybridauth($login_with_conf);
        $authProvider = $hybridauth->authenticate($provider);
        $tokens       = $authProvider->getAccessToken();
        $user_profile = $authProvider->getUserProfile();
        if ($user_profile && isset($user_profile->identifier)) {
            $name = $user_profile->firstName;
            if ($provider == 'Google') {
                $notfound_email     = 'go_';
                $notfound_email_com = '@google.com';
            } else if ($provider == 'Facebook') {
                $notfound_email     = 'fa_';
                $notfound_email_com = '@facebook.com';
            } else if ($provider == 'Twitter') {
                $notfound_email     = 'tw_';
                $notfound_email_com = '@twitter.com';
            } else if ($provider == 'LinkedIn') {
                $notfound_email     = 'li_';
                $notfound_email_com = '@linkedIn.com';
            } else if ($provider == 'Vkontakte') {
                $notfound_email     = 'vk_';
                $notfound_email_com = '@vk.com';
            } else if ($provider == 'Instagram') {
                $notfound_email     = 'in_';
                $notfound_email_com = '@instagram.com';
                $name = $user_profile->displayName;
            } else if ($provider == 'QQ') {
                $notfound_email     = 'qq_';
                $notfound_email_com = '@qq.com';
                $name = $user_profile->displayName;
            } else if ($provider == 'WeChat') {
                $notfound_email     = 'wechat_';
                $notfound_email_com = '@wechat.com';
                $name = $user_profile->displayName;
            } else if ($provider == 'Discord') {
                $notfound_email     = 'discord_';
                $notfound_email_com = '@discord.com';
                $name = $user_profile->displayName;
            } else if ($provider == 'Mailru') {
                $notfound_email     = 'mailru_';
                $notfound_email_com = '@mailru.com';
                $name = $user_profile->displayName;
            }
            $user_name  = $notfound_email . $user_profile->identifier;
            $user_email = $user_name . $notfound_email_com;
            if (!empty($user_profile->email)) {
                $user_email = $user_profile->email;
                if(empty($user_profile->emailVerified) && $provider == 'Discord') {
                    exit("Your E-mail is not verfied on Discord.");
                }
            }

            if ($user->userEmailExists($user_email) === true) {
                $db->where('email', $user_email);
                $login               = $db->getOne(T_USERS, 'user_id');
                $session_id          = sha1(rand(11111, 99999)) . time() . md5(microtime());
                $insert_data         = array(
                    'user_id' => $login->user_id,
                    'session_id' => $session_id,
                    'time' => time(),
                    'platform_details' => '',
                );
                $insert              = $db->insert(T_SESSIONS, $insert_data);
                $_SESSION['user_id'] = $session_id;
                setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
                header("Location: $site_url");
                exit();
            } else {
                $str          = md5(microtime());
                $id           = substr($str, 0, 9);
                $password     = substr(md5(time()), 0, 9);
                $user_uniq_id = (empty($user->userNameExists($id))) ? $id : 'u_' . $id;
                $social_url   = substr($user_profile->profileURL, strrpos($user_profile->profileURL, '/') + 1);
                $media        = new Media();
                $re_data      = array(
                    'username' => $user::secure($user_uniq_id, 0),
                    'email' => $user::secure($user_email, 0),
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'email_code' => $user::secure(sha1($user_uniq_id), 0),
                    'fname' => $user::secure($name),
                    'lname' => $user::secure($user_profile->lastName),
                    'avatar' => $user::secure($media->ImportImage($user_profile->photoURL, 1)),
                    'src' => $user::secure($provider),
                    'active' => '1'
                );
                if ($provider == 'Google') {
                    $re_data['about']  = $user::secure($user_profile->description);
                    $re_data['google'] = $user::secure($social_url);
                }
                if ($provider == 'Facebook') {
                    $fa_social_url       = @explode('/', $user_profile->profileURL);
                    $re_data['facebook'] = $user::secure($fa_social_url[4]);
                    $re_data['gender']   = 'male';
                    if (!empty($user_profile->gender)) {
                        if ($user_profile->gender == 'male') {
                            $re_data['gender'] = 'male';
                        } else if ($user_profile->gender == 'female') {
                            $re_data['gender'] = 'female';
                        }
                    }
                }
                // if ($provider == 'Twitter') {
                //     $re_data['twitter'] = $user::secure($social_url);
                // }
                // if ($provider == 'LinkedIn') {
                //     $re_data['about']    = $user::secure($user_profile->description);
                //     $re_data['linkedIn'] = $user::secure($social_url);
                // }
                // if ($provider == 'Vkontakte') {
                //     $re_data['about'] = $user::secure($user_profile->description);
                //     $re_data['vk']    = $user::secure($social_url);
                // }
                // if ($provider == 'Instagram') {
                //     $re_data['instagram']   = $user::secure($user_profile->username);
                // }
                // if ($provider == 'QQ') {
                //     $re_data['qq']   = $user::secure($social_url);
                // }
                // if ($provider == 'WeChat') {
                //     $re_data['wechat']   = $user::secure($social_url);
                // }
                // if ($provider == 'Discord') {
                //     $re_data['discord']   = $user::secure($social_url);
                // }
                // if ($provider == 'Mailru') {
                //     $re_data['mailru']   = $user::secure($social_url);
                // }
                $insert_id = $db->insert(T_USERS, $re_data);
                if ($insert_id) {
                    $session_id          = sha1(rand(11111, 99999)) . time() . md5(microtime());
                    $insert_data         = array(
                        'user_id' => $insert_id,
                        'session_id' => $session_id,
                        'time' => time(),
                        'platform_details' => '',
                    );
                    $insert              = $db->insert(T_SESSIONS, $insert_data);
                    $_SESSION['user_id'] = $session_id;
                    setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
                    header("Location: $site_url");
                    exit();
                }
            }
        }
    }
    catch (Exception $e) {
        var_dump($e);
        switch ($e->getCode()) {
            case 0:
                echo "Unspecified error.";
                break;
            case 1:
                echo "Hybridauth configuration error.";
                break;
            case 2:
                echo "Provider not properly configured.";
                break;
            case 3:
                echo "Unknown or disabled provider.";
                break;
            case 4:
                echo "Missing provider application credentials.";
                break;
            case 5:
                echo "Authentication failed The user has canceled the authentication or the provider refused the connection.";
                break;
            case 6:
                echo "User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.";
                break;
            case 7:
                echo "User not connected to the provider.";
                break;
            case 8:
                echo "Provider does not support this feature.";
                break;
        }
        echo " an error found while processing your request!";
        echo " <b><a href='{$site_url}/welcome'>Try again<a></b>";
    }
} else {
    header("Location: $site_url/welcome");
    exit();
}
