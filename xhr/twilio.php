<?php
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

if ($action == 'create_new_video_call' && IS_LOGGED && $config['video_chat'] == 1) {

        if ( empty($_GET['user_id2']) || empty($_GET['user_id1']) || $_GET['user_id1'] != $me['user_id'] ) {
            $data = array(
                'status' => 403,
                'message' => 'Forbidden'
            ); 
        }
        if ($config['agora_chat_video'] == 'on') {
            $room_script  = sha1(rand(1111111, 9999999999));
            $context['AgoraToken'] = null;
            if (!empty($config['agora_chat_app_certificate'])) {
                include_once 'assets/libraries/AgoraDynamicKey/src/RtcTokenBuilder.php';
                $appID = $config['agora_chat_app_id'];
                $appCertificate = $config['agora_chat_app_certificate'];
                $uid = 0;
                $uidStr = "0";
                $role = RtcTokenBuilder::RoleAttendee;
                $expireTimeInSeconds = 36000000;
                $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
                $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
                $context['AgoraToken'] = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $room_script, $uid, $role, $privilegeExpiredTs);
            }

            $call_type = 'video';
            $insertData = CreateNewAgoraCall(array(
                'from_id' => Generic::secure($_GET['user_id1']),
                'to_id' => Generic::secure($_GET['user_id2']),
                'room_name' => $room_script,
                'type' => $call_type,
                'status' => 'calling',
                'access_token' => $context['AgoraToken']
            ));
            $context['calling_user'] = $user->getUserDataById($_GET['user_id2']);
            if ($insertData > 0) {
                $context['call_type'] = $call_type;
                $data = array(
                    'status' => 200,
                    'access_token' => '',
                    'id' => $insertData,
                    'url' => $config['site_url'] . '/video_call/' . $room_script,
                    'html' => $pixelphoto->PX_LoadPage('home/templates/home/includes/calling'),
                    'text_no_answer' => lang('no_answer'),
                    'text_please_try_again_later' => lang('try_again_later')
                );
            }
        }
        else{
            //require_once($_LIBS . 'twilio'.$_DS.'vendor'.$_DS.'autoload.php');
            // $user_1       = userData(Generic::secure($_GET['user_id1']));
            // $user_2       = userData(Generic::secure($_GET['user_id2']));
            $room_script  = sha1(rand(1111111, 9999999));
            $accountSid   = $config['video_accountSid'];
            $apiKeySid    = $config['video_apiKeySid'];
            $apiKeySecret = $config['video_apiKeySecret'];
            $call_id      = substr(md5(microtime()), 0, 15);
            $call_id_2    = substr(md5(time()), 0, 15);
            $token        = new AccessToken($accountSid, $apiKeySid, $apiKeySecret, 3600, $call_id);
            $grant        = new VideoGrant();
            $grant->setRoom($room_script);
            $token->addGrant($grant);
            $token_ = $token->toJWT();
            $token2 = new AccessToken($accountSid, $apiKeySid, $apiKeySecret, 3600, $call_id_2);
            $grant2 = new VideoGrant();
            $grant2->setRoom($room_script);
            $token2->addGrant($grant2);
            $token_2    = $token2->toJWT();
            $vid_array = array(
                'access_token' => Generic::secure($token_),
                'from_id' => Generic::secure($_GET['user_id1']),
                'to_id' => Generic::secure($_GET['user_id2']),
                'access_token_2' => Generic::secure($token_2),
                'room_name' => $room_script
            );
            $insertData = CreateNewVideoCall($vid_array);
            if ($insertData > 0) {
                $context['call_type'] = 'video';
                $html = '';
                $user         = new User();
                $context['calling_user'] = $user->getUserDataById($_GET['user_id2']);
                $html = $pixelphoto->PX_LoadPage('home/templates/home/includes/calling');

                $data = array(
                    'status' => 200,
                    'access_token' => $token_,
                    'id' => $insertData,
                    'url' => $config['site_url'] . '/video_call/' . $insertData,
                    'html' => $html,
                    'text_no_answer' => lang('no_answer'),
                    'text_please_try_again_later' => lang('try_again_later')
                );
            }
        }
}
elseif ($action == 'create_new_audio_call' && IS_LOGGED && $config['audio_chat'] == 1) {
    if ( empty($_GET['user_id2']) || empty($_GET['user_id1']) || $_GET['user_id1'] != $me['user_id'] ) {
        $data = array(
            'status' => 403,
            'message' => 'Forbidden'
        ); 
    }
    if ($config['agora_chat_video'] == 'on') {
        $room_script  = sha1(rand(1111111, 9999999999));
        $context['AgoraToken'] = null;
        if (!empty($config['agora_chat_app_certificate'])) {
            include_once 'assets/libraries/AgoraDynamicKey/src/RtcTokenBuilder.php';
            $appID = $config['agora_chat_app_id'];
            $appCertificate = $config['agora_chat_app_certificate'];
            $uid = 0;
            $uidStr = "0";
            $role = RtcTokenBuilder::RoleAttendee;
            $expireTimeInSeconds = 36000000;
            $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
            $context['AgoraToken'] = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $room_script, $uid, $role, $privilegeExpiredTs);
        }
        $call_type = 'audio';

        $insertData = CreateNewAgoraCall(array(
            'from_id' => Generic::secure($_GET['user_id1']),
            'to_id' => Generic::secure($_GET['user_id2']),
            'room_name' => $room_script,
            'type' => $call_type,
            'status' => 'calling',
            'access_token' => $context['AgoraToken']
        ));
        if ($insertData > 0) {
            //$context['calling_user'] = $user_2;
            $context['calling_user'] = $user->getUserDataById($_GET['user_id2']);
            $context['call_type'] = $call_type;
            $data = array(
                'status' => 200,
                'access_token' => '',
                'id' => $insertData,
                'html' => $pixelphoto->PX_LoadPage('home/templates/home/includes/calling'),
                'text_no_answer' => lang('no_answer'),
                'text_please_try_again_later' => lang('try_again_later')
            );
        }
    }
    else{
        //require_once($_LIBS . 'twilio'.$_DS.'vendor'.$_DS.'autoload.php');
        // $user_1       = userData(Generic::secure($_GET['user_id1']));
        // $user_2       = userData(Generic::secure($_GET['user_id2']));
        $room_script  = sha1(rand(1111111, 9999999));
        $accountSid   = $config['video_accountSid'];
        $apiKeySid    = $config['video_apiKeySid'];
        $apiKeySecret = $config['video_apiKeySecret'];
        $call_id      = substr(md5(microtime()), 0, 15);
        $call_id_2    = substr(md5(time()), 0, 15);
        $token        = new AccessToken($accountSid, $apiKeySid, $apiKeySecret, 3600, $call_id);
        $grant        = new VideoGrant();
        $grant->setRoom($room_script);
        $token->addGrant($grant);
        $token_ = $token->toJWT();
        $token2 = new AccessToken($accountSid, $apiKeySid, $apiKeySecret, 3600, $call_id_2);
        $grant2 = new VideoGrant();
        $grant2->setRoom($room_script);
        $token2->addGrant($grant2);
        $token_2    = $token2->toJWT();
        $vid_array = array(
            'access_token' => Generic::secure($token_),
            'from_id' => Generic::secure($_GET['user_id1']),
            'to_id' => Generic::secure($_GET['user_id2']),
            'access_token_2' => Generic::secure($token_2),
            'room_name' => $room_script
        );
        $insertData = CreateNewAudioCall($vid_array);
        if ($insertData > 0) {
            $context['call_type'] = 'audio';
            $html = '';
            $user         = new User();
            $context['calling_user'] = $user->getUserDataById($_GET['user_id2']);
            $html = $pixelphoto->PX_LoadPage('home/templates/home/includes/calling');

            $data = array(
                'status' => 200,
                'access_token' => $token_,
                'id' => $insertData,
                'html' => $html,
                'text_no_answer' => lang('no_answer'),
                'text_please_try_again_later' => lang('try_again_later')
            );
        }
    }
}
else if( $action == 'check_for_answer'){
    if (!empty($_GET['id'])) {
        $selectData = CheckCallAnswer($_GET['id']);
        if ($selectData !== false) {
            $data = ['idxxxx' => $selectData];
            $data = array(
                'status' => 200,
                'url' => $selectData['url'],
                'text_answered' => lang('answered'),
                'text_please_wait' => lang('please_wait')
            );
        } else {
            $check_declined = CheckCallAnswerDeclined($_GET['id']);
            $data = ['id' => $check_declined];
            if ($check_declined) {
                $data = array(
                    'status' => 400,
                    'text_call_declined' => lang('call_declined'),
                    'text_call_declined_desc' => lang('recipient_has_declined')
                );
            }
        }
    }
}
else if( $action == 'check_for_audio_answer'){
    $data = array('status' => 500);
    if (!empty($_GET['id'])) {

        $selectData = CheckAudioCallAnswer($_GET['id']);
        if ($selectData !== false) {
            $data = array(
                'status' => 200,
                'text_answered' => lang('answered'),
                'text_please_wait' => lang('please_wait')
            );
            $id    = Generic::secure($_GET['id']);
            
            if ($config['agora_chat_video'] == 'on') {
                $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AGORA . " WHERE `id` = '{$id}'");
            }
            else{
                $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AUDIO_CALLES . " WHERE `id` = '{$id}'");
            }
            $sql   = mysqli_fetch_assoc($query);
            if (!empty($sql) && is_array($sql)) {
                $context['incall']                 = $sql;
                $user         = new User();
                if ($config['agora_chat_video'] == 'on') {
                    $context['incall']['in_call_user'] = $user->getUserDataById($sql['to_id']);
                }
                else{
                    $context['incall']['in_call_user'] = $user->getUserDataById($sql['to_id']);
                    if ($context['incall']['to_id'] == $me['user_id']) {
                        $context['incall']['user']         = 1;
                        $context['incall']['access_token'] = $context['incall']['access_token'];
                    } else if ($context['incall']['from_id'] == $me['user_id']) {
                        $context['incall']['user']         = 2;
                        $context['incall']['access_token'] = $context['incall']['access_token_2'];
                    }
                }
                    
                $context['incall']['room'] = $context['incall']['room_name'];
                $data['calls_html']   = $pixelphoto->PX_LoadPage('home/templates/home/includes/talking');
            }
        } else {

            $check_declined = CheckAudioCallAnswerDeclined($_GET['id']);
            if ($check_declined) {
                $data = array(
                    'status' => 400,
                    'text_call_declined' => lang('call_declined'),
                    'text_call_declined_desc' => lang('recipient_has_declined')
                );
            }
        }
    }
}
else if( $action == 'cancel_call'){
    $user_id = $me['user_id'];
    $query   = mysqli_query($sqlConnect, "DELETE FROM `videocalles` WHERE `from_id` = '$user_id'");
    $query   = mysqli_query($sqlConnect, "DELETE FROM " . T_AGORA . " WHERE `from_id` = '$user_id'");
    if ($query) {
        $data = array(
            'status' => 200
        );
    }
}
else if( $action == 'answer_call'){
    if (!empty($_GET['id']) && !empty($_GET['type'])) {
        $id = Generic::secure($_GET['id']);
        if ($_GET['type'] == 'audio') {
            $query = mysqli_query($sqlConnect, "UPDATE ".T_AUDIO_CALLES." SET `active` = 1 WHERE `id` = '$id'");
        } else {
            $query = mysqli_query($sqlConnect, "UPDATE `videocalles` SET `active` = 1 WHERE `id` = '$id'");
        }
        if ($config['agora_chat_video'] == 'on') {
            $query = mysqli_query($sqlConnect, "UPDATE " . T_AGORA . " SET `active` = 1 WHERE `id` = '$id'");
        }
        if ($query) {
            $data = array(
                'status' => 200
            );
            if ($_GET['type'] == 'audio') {
                if ($config['agora_chat_video'] == 'on') {
                    $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AGORA . " WHERE `id` = '{$id}'");
                }
                else{
                    $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AUDIO_CALLES . " WHERE `id` = '{$id}'");
                }
                
                $sql   = mysqli_fetch_assoc($query);
                if (!empty($sql) && is_array($sql)) {
                    $context['incall']                 = $sql;
                    $context['incall']['in_call_user'] = $user->getUserDataById($sql['from_id']);
                    if ($context['incall']['to_id'] == $me['user_id']) {
                        $context['incall']['user']         = 1;
                        $context['incall']['access_token'] = $context['incall']['access_token'];
                    } else if ($context['incall']['from_id'] == $me['user_id']) {
                        $context['incall']['user']         = 2;
                        $context['incall']['access_token'] = $context['incall']['access_token_2'];
                    }
                    $context['incall']['room'] = $context['incall']['room_name'];
                    $data['calls_html']   = $pixelphoto->PX_LoadPage('home/templates/home/includes/talking');
                }
            }
        }
    }
}
else if( $action == 'decline_call'){
    if (!empty($_GET['id']) && !empty($_GET['type'])) {
        $id = Generic::secure($_GET['id']);
        if ($config['agora_chat_video'] == 'on') {
            $query = mysqli_query($sqlConnect, "UPDATE " . T_AGORA . " SET `declined` = '1' WHERE `id` = '$id'");
        }
        else{
            if ($_GET['type'] == 'video') {
                $query = mysqli_query($sqlConnect, "UPDATE `videocalles` SET `declined` = '1' WHERE `id` = '$id'");
            } else {
                $query = mysqli_query($sqlConnect, "UPDATE ".T_AUDIO_CALLES." SET `declined` = '1' WHERE `id` = '$id'");
            }
        }
        if ($query) {
            $data = array(
                'status' => 200
            );
        }
    }
}