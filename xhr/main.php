<?php 

if ($action == 'follow' && IS_LOGGED) {
	if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
		$follower_id  = $me['user_id'];
		$following_id = Generic::secure($_GET['user_id']);
		$notif        = new Notifications();
		$user->setUserById($follower_id);
		$status       = $user->follow($following_id);
		$data['status'] = 400;
		if ($status === 1) {
			$data['status'] = 200;
			$data['code'] = 1;

			#Notify post owner
			$notif_conf = $notif->notifSettings($following_id,'on_follow');
			if ($notif_conf) {
				$re_data = array(
					'notifier_id' => $me['user_id'],
					'recipient_id' => $following_id,
					'type' => 'followed_u',
					'url' => un2url($me['username']),
					'time' => time()
				);
				
				$notif->notify($re_data);
			}	
		}

		else if($status === -1){
			$data['status'] = 200;
			$data['code'] = 0;
		}

		goto exit_xhr;
	}
}

else if($action == 'get_notif' && IS_LOGGED){
	$notif = new Notifications();
	$data  = array();

	$notif->setUserById($me['user_id']);
	$notif->type    = 'all';
	$notif->limit   = 1000;
	$queryset       = $notif->getNotifications();

	if (!empty($queryset) && is_array($queryset)) {
		$new_notif      = o2array($queryset);
		$context['notifications'] = $new_notif;
		$data['html']    = $pixelphoto->PX_LoadPage('main/templates/header/notifications');
		$data['status'] = 200;
	}

	else{
		$data['status']  = 304;
		$data['message'] = lang('u_dont_have_notif');
	}
}

else if($action == 'get_requests' && IS_LOGGED){
	

	$db->where('following_id',$me['user_id']);
	$db->where('type',2);
	$db->orderBy('id','DESC');
	$requests = $db->get(T_CONNECTIV,10);
	$db->where('following_id',$me['user_id'])->where('active',0)->update(T_CONNECTIV,array('active' => 1));
	$user = new User();
	$html = '';

	foreach ($requests as $key => $request) {
		$context['request'] = $request;
		$context['user_data'] = $user->getUserDataById($request->follower_id);
		$html .= $pixelphoto->PX_LoadPage('main/templates/header/requests');
		$data['status'] = 200;
		$data['html'] = $html;

	}
	if (empty($html)) {
		$data['status']  = 304;
		$data['message'] = lang('u_dont_have_requests');
	}
}
else if($action == 'accept_requests' && IS_LOGGED){
	$data['status'] = 400;
	if (!empty($_POST['user_id']) && is_numeric($_POST['user_id']) && $_POST['user_id'] > 0) {
		$db->where('following_id',$me['user_id']);
		$db->where('follower_id',Generic::secure($_POST['user_id']));
		$db->where('type',2);
		$request = $db->getOne(T_CONNECTIV);
		$user = new User();
		$follower = $user->getUserDataById($request->follower_id);
		if (!empty($request) && !empty($follower)) {
			$db->where('id',$request->id)->update(T_CONNECTIV,array('type' => 1,'active' => 1));
			$notif        = new Notifications();
			$re_data = array(
				'notifier_id' => $me['user_id'],
				'recipient_id' => $follower->user_id,
				'type' => 'accept_request',
				'url' => un2url($me['username']),
				'time' => time()
			);
				
			$notif->notify($re_data);
			$data['status'] = 200;
			$data['message'] = $follower->name . ' '. lang('is_following_you');
		}
		else{
			$data['message'] = lang('please_check_details');
		}
	}
	else{
		$data['message'] = lang('please_check_details');
	}
}
else if($action == 'delete_requests' && IS_LOGGED){
	$data['status'] = 400;
	if (!empty($_POST['user_id']) && is_numeric($_POST['user_id']) && $_POST['user_id'] > 0) {
		 $db->where('following_id',$me['user_id']);
		 $db->where('follower_id',Generic::secure($_POST['user_id']));
		 $db->where('type',2);
		 $request = $db->delete(T_CONNECTIV);
		$data['status'] = 200;
	}
	else{
		$data['message'] = lang('please_check_details');
	}
}

elseif ($action == 'update-data' && IS_LOGGED) {
	if ($config['private_videos'] == 'on' || $config['private_photos'] == 'on') {
		$month = 60 * 60 * 24 * 30;
		$expired_subscribed = $db->where('time',(time() - $month),'<')->get(T_SUBSCRIBERS);
		if (!empty($expired_subscribed)) {
			foreach ($expired_subscribed as $key => $value) {
				$user_data = $db->where('user_id',$value->user_id)->getOne(T_USERS,array('subscribe_price','username'));
				if (!empty($user_data) && $user_data->subscribe_price > 0) {
					$subscribe_data = $db->where('user_id',$value->subscriber_id)->getOne(T_USERS,array('wallet','username'));

					if (!empty($subscribe_data) && $subscribe_data->wallet > 0 && $subscribe_data->wallet >= $user_data->subscribe_price) {
						$amount = $user_data->subscribe_price;
	                    $admin_com = 0;
	                    if ($config['monthly_subscribers_commission'] > 0) {
	                        $admin_com = ($config['monthly_subscribers_commission'] * $amount) / 100;
	                        $amount = $amount - $admin_com;
	                    }
	                    $wallet = $subscribe_data->wallet - $user_data->subscribe_price;
						$db->insert(T_SUBSCRIBERS,array('user_id' => $value->user_id,
	                                                    'subscriber_id' => $value->subscriber_id,
	                                                    'time' => time()));
						$user = new User();
						$user->updateStatic($value->subscriber_id,array('wallet' => $wallet));
						$db->where('user_id',$value->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
						$notif   = new Notifications();

	                    $re_data = array(
	                        'notifier_id' => $value->subscriber_id,
	                        'recipient_id' => $value->user_id,
	                        'type' => 'renewed_his_subscription',
	                        'url' => $config['site_url'] . "/".$subscribe_data->username,
	                        'time' => time()
	                    );
	                    $notif->notify($re_data);

	                    $re_data = array(
	                        'notifier_id' => $value->user_id,
	                        'recipient_id' => $value->subscriber_id,
	                        'type' => 'subscription_has_been_renewed',
	                        'url' => $config['site_url'] . "/".$user_data->username,
	                        'time' => time()
	                    );
	                    $notif->notify($re_data);
					}
					else{
						$notif   = new Notifications();
						$re_data = array(
	                        'notifier_id' => $value->user_id,
	                        'recipient_id' => $value->subscriber_id,
	                        'type' => 'your_subscription_has_been_expired',
	                        'url' => $config['site_url'] . "/".$user_data->username,
	                        'time' => time()
	                    );
	                    $notif->notify($re_data);
					}
					$db->where('id',$value->id)->delete(T_SUBSCRIBERS);
				}
			}
		}
	}
	$data  = array();
	$notif = new Notifications();

	$data['calls']    = 0;
	$data['is_call']  = 0;
	$check_calles     = CheckFroInCalls();
	$context['call_type'] = 'video';
	if ($check_calles !== false && is_array($check_calles)) {
		$context['incall'] 	  = $check_calles;
		$user         = new User();
		$context['incall']['in_call_user'] = $user->getUserDataById($check_calles['from_id']);
		$data['calls']                = 200;
		$data['is_call']              = 1;
		
		$html = $pixelphoto->PX_LoadPage('home/templates/home/includes/in_call');
		$data['calls_html']           = $html;
	}
	else{
		$data['calls']   = 0;
	    $data['is_call'] = 0;
	    $check_calles          = CheckFroInCalls('audio');
	    if ($check_calles !== false && is_array($check_calles)) {
	    	$context['call_type'] = 'audio';
	        $context['incall']                 = $check_calles;
	        $context['incall']['in_call_user'] = $user->getUserDataById($check_calles['from_id']);
	        $data['calls']          = 200;
	        $data['is_call']        = 1;
	        $data['calls_html']     = $pixelphoto->PX_LoadPage('home/templates/home/includes/in_call');
	    }
	}

	$notif->setUserById($me['user_id']);
	$notif->type    = 'new';
	$new_notif      = $notif->getNotifications();
	$data['notif']  = (is_numeric($new_notif)) ? $new_notif : 0;

	$db->where('following_id',$me['user_id']);
	$db->where('type',2);
	$db->where('active',0);
	$data['requests'] = $db->getValue(T_CONNECTIV,"COUNT(*)");

	if (!empty($_GET['new_messages'])) {
		$messages     = new Messages();
		$messages->setUserById($me['user_id']);
		$new_messages = $messages->countNewMessages();
		$data['new_messages'] = $new_messages;
	}
}

elseif ($action == 'explore-people' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$user->limit = 100;
		$offset      = $_GET['offset'];
		$users       = $user->explorePeople($offset);
		$data        = array('status' => 404);

		if (!empty($users)) {
			$users = o2array($users);
			$html  = "";

			foreach ($users as $udata) {
				$html    .= $pixelphoto->PX_LoadPage('explore/templates/explore/includes/row');
			}

			$data = array(
				'status' => 200,
				'html' => $html
			);
		}
	}
}

elseif ($action == 'report-profile' && IS_LOGGED && !empty($_POST['id'])){
	if (is_numeric($_POST['id']) && !empty($_POST['t'])) {
		$user_id = $_POST['id'];
		$type    = $_POST['t'];
		$data    = array('status' => 304);
		if (in_array($type, range(1, 8)) || $type == -1) {
			$code = $user->reportUser($user_id,$type);
			$code = ($code == -1) ? 0 : 1;
			$data = array(
				'status' => 200,
				'code' => $code,
			);

			if ($code == 0) {
				$data['message'] = lang('report_canceled');
			}

			else if($code == 1){
				$data['message'] = lang('report_sent');
			}
		}
	}
}

elseif ($action == 'block-user' && IS_LOGGED && !empty($_POST['id'])){
	if (is_numeric($_POST['id'])) {
		$user_id = $_POST['id'];
		$data    = array('status' => 304);
		$notif   = new Notifications();
		$code    = $user->blockUser($user_id);
		$code    = ($code == -1) ? 0 : 1;

		if (in_array($code, array(0,1))) {
			$data    = array(
				'status' => 200,
				'code' => $code,
			);

			if ($code == 0) {
				$data['message'] = lang('user_unblocked');
			}

			else if($code == 1){
				$data['message']    = lang('user_blocked');
				$notif->notifier_id = $user_id; 
				$notif->setUserById($me['user_id'])->clearNotifications();
			}
		}
	}
}

elseif ($action == 'share_post_on'){
	$data['status'] = 400;
    $result = false;
	$post_id = 0;
	if (!empty($_GET['post_id'])) {
		if (is_numeric($_GET['post_id'])) {
			$post_id = Generic::secure($_GET['post_id']);
		}
	}	
	if (empty($post_id)) {
		exit("Invalid POST ID");
	}



	$posts  = new Posts();
	$posts->setPostId($post_id);
	$getPost = o2array($posts->postData());
	if (empty($getPost)) {
		exit("Invalid POST ID");
	}	

	$post_text = '';
	if (!empty($_GET['text'])) {
		$post_text = Generic::secure($_GET['text']);
	}else{
		$post_text = $getPost['description'];
	}
	$user_id = $getPost['user_id'];

	if( $user_id <> $me['user_id'] ){
		$re_data = array(
			'user_id' 			=> $me['user_id'],
			'description' 		=> $post_text,
			'link'				=> $getPost['link'],
			'youtube'			=> $getPost['youtube'],
			'vimeo'				=> $getPost['vimeo'],
			'dailymotion'		=> $getPost['dailymotion'],
			'playtube'			=> $getPost['playtube'],
			'mp4'				=> $getPost['mp4'],
			'type'				=> $getPost['type'],
			'registered'		=> sprintf('%s/%s',date('Y'),date('n')),
			'time' 				=> time()
		);
		$pid = $db->insert(T_POSTS, $re_data);
		if (is_numeric($pid) && $pid > 0) {
			$media_items = $db->where('post_id',$getPost['post_id'])->get(T_MEDIA,null,array('*'));
			foreach($media_items as $key => $media_item){
				$db->insert(T_MEDIA,array(
					'user_id' => $me['user_id'],
					'post_id' => $pid,
					'file' => $media_item->file,
					'extra' => $media_item->extra
				));
			}
			$notif   = new Notifications();
			$re_data_notify = array(
				'notifier_id' => $me['user_id'],
				'recipient_id' => $user_id,
				'type' => 'shared_your_post',
				'url' => $config['site_url'] . "/post/".$pid,
				'time' => time()
			);
			try {
				$notif->notify($re_data_notify);
			} catch (Exception $e) {
			}
			$db->insert(T_ACTIVITIES,array('user_id' => $me['user_id'],
	                                       'post_id' => $pid,
	                                       'type'    => 'share_post',
	                                       'time'    => time()));

			$data['status'] = 200;
			$data['message'] = 'Your post has been published successfully';
		}
	}else{
		$data['status'] = 400;
		$data['message'] = lang('cant_share_own');
	}

}

elseif ($action == 'get-share-modal'){
	$postid = 0;
	if (!empty($_GET['id'])) {
		if (is_numeric($_GET['id'])) {
			$post_id = Generic::secure($_GET['id']);
		}
	}	
	if (empty($post_id)) {
		exit("Invalid POST ID");
	}

	$posts  = new Posts();
	$posts->setPostId($post_id);
	$getPost = o2array($posts->postData());
	if (empty($getPost)) {
		exit("Invalid POST ID");
	}	

	$data['status'] = 400;

    $description  = $posts->likifyMentions($getPost['description']);
    $description  = $posts->tagifyHTags($getPost['description']);
    $description  = $posts->linkifyHTags($getPost['description']);
    $description  = $posts->obsceneWords($getPost['description']);
    $description  = htmlspecialchars_decode($getPost['description']);

	$html = $pixelphoto->PX_LoadPage('main/templates/includes/share-post', [
		't_title' => strip_tags($description),
		's_user' => $getPost['username'],
		't_url' => urlencode($config['site_url'] . '/post/'.$getPost['post_id']),
		't_url_original' => 'post/'.$getPost['post_id'],
		't_thumbnail' => $getPost['avatar'],
		't_post_id' => $getPost['post_id'],
		'postData' => $getPost
	]);
	//$db->where('id', $getPost->id)->update(T_POSTS, ['shares' => ($getPost->shares + 1)]);
	$data    = array(
		'status' => 200,
		'html' => $html
	);
}
elseif ($action == 'search-users' && !empty($_POST['kw'])){
	if (len($_POST['kw']) >= 0) {
		$kword    = $_POST['kw'];
		$data     = array('status' => 304);
		$queryset = $user->seachUsers($kword);
		$html     = "";

		if( substr($kword, 0,1) == '#' ){

            if (len($_POST['kw']) >= 0) {
                $posts    = new Posts();
                $kword    = $_POST['kw'];
                $data     = array('status' => 304);
                $queryset = $posts->searchPosts($kword);
                $html     = "";

                if(!empty($queryset)){
                    $queryset = o2array($queryset);

                    foreach ($queryset as $htag) {
                        $htag['url'] = sprintf('%s/explore/tags/%s',$site_url,$htag['tag']);
                        $context['htag'] = $htag;
                        $html    .= $pixelphoto->PX_LoadPage('main/templates/header/search-posts');
                    }

                    $data['status'] = 200;
                    $data['html']   = $html;
                }
            }

//            $data['status'] = 200;
//            $data['html']   = $kword;
        }else {
            if (!empty($queryset)) {
                $queryset = o2array($queryset);

                foreach ($queryset as $udata) {
                    $html .= $pixelphoto->PX_LoadPage('main/templates/header/search-usrls');
                }

                $data['status'] = 200;
                $data['html'] = $html;
            } else {

//                $html = '';
//                $posts = new Posts();
//                $queryset2 = $posts->searchPosts($kword);
//                if (!empty($queryset2)) {
//                    $queryset2 = o2array($queryset2);
//                    foreach ($queryset2 as $htag) {
//                        $htag['url'] = sprintf('%s/explore/tags/%s', $site_url, $htag['tag']);
//                        $context['htag'] = $htag;
//                        $html .= $pixelphoto->PX_LoadPage('main/templates/header/search-posts');
//                    }
//
//                    $data['status'] = 200;
//                    $data['html'] = $html;
//                }
            }
        }
	}
}

elseif ($action == 'search-blog' && !empty($_POST['kw'])){
    if (len($_POST['kw']) >= 0) {
        $kword    = $_POST['kw'];
        $data['status'] = 200;
        $data['html']   = $pixelphoto->PX_LoadPage('blog/templates/blog/includes/no-articles-found');
        $html     = "";

        if (len($_POST['kw']) >= 0) {
            $queryset = $db->arrayBuilder()->where('title','%' . $kword . '%', 'like')->orWhere('content','%' . $kword . '%', 'like')->orWhere('description','%' . $kword . '%', 'like')->orderBy('id','DESC')->get(T_BLOG,20);
            $html     = "";
            if(!empty($queryset)){
                $queryset = o2array($queryset);
                foreach ($queryset as $key => $post_data) {
                    $post_data['category_name'] = $context['lang'][$post_data['category']];
                    $post_data['full_thumbnail'] = media($post_data['thumbnail']);
                    $post_data['text_time'] = time2str($post_data['created_at']);
                    $html    .= $pixelphoto->PX_LoadPage('blog/templates/blog/includes/list');
                }
                $data['status'] = 200;
                $data['html']   = $html;
            }
        }
    }
}

elseif ($action == 'search-posts' && !empty($_POST['kw'])){
	if (len($_POST['kw']) >= 0) {
		$posts    = new Posts();
		$kword    = $_POST['kw'];
		$data     = array('status' => 304);
		$queryset = $posts->searchPosts($kword);
		$html     = "";

		if(!empty($queryset)){
			$queryset = o2array($queryset);

			foreach ($queryset as $htag) {
				$htag['url'] = sprintf('%s/explore/tags/%s',$site_url,$htag['tag']);
				$context['htag'] = $htag;
				$html    .= $pixelphoto->PX_LoadPage('main/templates/header/search-posts');
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}
elseif ($action == 'contact_us'){
	$data['status'] = 400;
	if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['message'])) {
		$data['message'] = lang('please_check_details');
	}
	else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $data['message'] = lang('email_invalid_characters');
    }
	else{
		$first_name        = Generic::secure($_POST['first_name']);
        $last_name         = Generic::secure($_POST['last_name']);
        $email             = Generic::secure($_POST['email']);
        $message           = Generic::secure($_POST['message']);
        $name              = $first_name . ' ' . $last_name;
		$message_text = "<p><strong>Name</strong> : {$name}</p>
						 <br>
						 <p><strong>Email</strong> : {$email}</p>
						 <br>
						 <p><strong>Message</strong> : {$message}</p>
						 ";

        $send_email_data = array(
            'from_email' => $email,
            'from_name' => $name,
            'reply-to' => $email,
            'to_email' => $config['site_email'],
            'to_name' => $user->user_data->name,
            'subject' => 'Contact us new message',
            'charSet' => 'UTF-8',
            'message_body' => $message_text,
            'is_html' => true
        );
        $send_message = Generic::sendMail($send_email_data);
        if ($send_message) {
            $data['status'] = 200;
            $data['message'] = lang('email_sent');
        }else{
        	$data['message'] = lang('unknown_error');
        }
	}
}

elseif ($action == 'change_mode') {

	if ($_COOKIE['mode'] == 'day') {
		setcookie("mode", 'night', time() + (10 * 365 * 24 * 60 * 60), "/");
		$data = array('status' => 200,
	                  'type' => 'night',
	                  'link' => $config['site_url'].'/apps/'.$config['theme'].'/main/static/css/styles.master_night.css');
	}
	else{
		setcookie("mode", 'day', time() + (10 * 365 * 24 * 60 * 60), "/");
		$data = array('status' => 200,
	                  'type' => 'day');
	}
}

elseif ($action == 'get_more_activities') {
	$data = array('status' => 400);

	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$html = '';
		$posts  = new Posts();
		$offset = Generic::secure($_POST['id']);
		$activities = $posts->getUsersActivities($offset,5);
		$activities = o2array($activities);
		if (!empty($activities)) {
			foreach ($activities as $key => $value) {
				$context['activity'] = $value;
				$html    .= $pixelphoto->PX_LoadPage('home/templates/home/includes/activity');
			}
			$data = array('status' => 200,
		                  'html'   => $html);
		}
		else{
			$data['text'] = lang('no_more_activities');
		}
	}
	
}
elseif ($action == 'update_user_lastseen') {
	if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $db->where('session_id', $_SESSION['user_id'])->update(T_SESSIONS, array('time' => time()));
    } else if (!empty($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
    	$db->where('session_id', $_COOKIE['user_id'])->update(T_SESSIONS, array('time' => time()));
	}
	
	$data = array('status' => 200);
}

elseif ($action == 'get_payment_methods') {
	$context['pay_type'] = 'pro';
	$pay_type = array('pro','wallet','store');
	if (!empty($_POST['type']) && in_array($_POST['type'], $pay_type)) {
		$context['pay_type'] = $_POST['type'];
	}
	$html    = $pixelphoto->PX_LoadPage('main/templates/modals/go_pro');
	$data = array('status' => 200,'html' => $html);
}

elseif ($action == 'checkout') {
	
	if (empty($_POST['card_number']) || empty($_POST['card_cvc']) || empty($_POST['card_month']) || empty($_POST['card_year']) || empty($_POST['token']) || empty($_POST['card_name']) || empty($_POST['card_address']) || empty($_POST['card_city']) || empty($_POST['card_state']) || empty($_POST['card_zip']) || empty($_POST['card_country']) || empty($_POST['card_email']) || empty($_POST['card_phone']) || empty($_POST['amount']) || !is_numeric($_POST['amount'])) {
        $data = array(
            'status' => 400,
            'error' => lang('unknown_error')
        );
    }
    else {
		require_once("sys/import3p/2checkout/Twocheckout.php");
        Twocheckout::privateKey($config['checkout_private_key']);
        Twocheckout::sellerId($config['checkout_seller_id']);
        if ($config['checkout_mode'] == 'sandbox') {
            Twocheckout::sandbox(true);
        } else {
            Twocheckout::sandbox(false);
        }
        try {
			$amount = $org_amount = Generic::secure(intval($_POST['amount']));
			if (!empty($config['currency_array']) && in_array($config['checkout_currency'], $config['currency_array']) && $config['checkout_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['checkout_currency']])) {
		        $amount= (($amount * $config['exchange'][$config['checkout_currency']]));
		    }

			$charge  = Twocheckout_Charge::auth(array(
                "merchantOrderId" => "123",
                "token" => $_POST['token'],
                "currency" => $config['checkout_currency'],
                "total" => $amount,
                "billingAddr" => array(
                    "name" => $_POST['card_name'],
                    "addrLine1" => $_POST['card_address'],
                    "city" => $_POST['card_city'],
                    "state" => $_POST['card_state'],
                    "zipCode" => $_POST['card_zip'],
                    "country" => $countries_name[$_POST['card_country']],
                    "email" => $_POST['card_email'],
                    "phoneNumber" => $_POST['card_phone']
                )
			));
			
			if ($charge['response']['responseCode'] == 'APPROVED') {
				$wallet = $me['wallet'] + $org_amount;
	            $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

	            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
	                                          'amount' => $org_amount,
	                                          'type' => 'Advertise',
	                                          'time' => time()));
	            if (!empty($_COOKIE['redirect_page'])) {
	                $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
	                $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
	                $url = $redirect_page;
	            }
	            else{
	            	$url = $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '');
	            }



				if ($me['address'] != $_POST['card_address'] ||
					$me['city'] != $_POST['card_city'] || 
					$me['state'] != $_POST['card_state'] || 
					$me['zip'] != $_POST['card_zip'] || 
					$me['country_id'] != $_POST['card_country'] || 
					$me['phone_number'] != $_POST['card_phone']
				) {
			    	$update_data = array(
						'address' => Generic::secure($_POST['card_address']),
						'city' => Generic::secure($_POST['card_city']),
						'state' => Generic::secure($_POST['card_state']),
						'zip' => Generic::secure($_POST['card_zip']),
						'country_id' => Generic::secure($_POST['card_country']),
						'phone_number' => Generic::secure($_POST['card_phone'])
					);
					$user->updateStatic($me['user_id'],$update_data);
				}
				
				$data = array(
                    'status' => 200,
                    'url' => $url
                );

            }
            else{
            	$data = array(
                    'status' => 400,
                    'error' => lang('checkout_declined')
                );
			}
			
		}
		catch (Twocheckout_Error $e) {
            $data = array(
                'status' => 400,
                'error' => $e->getMessage()
            );
        }
	}

}
else if ($action == 'update_store_image' && IS_LOGGED) {
	$license_array = array();
	if( !empty($_POST['license']) && !empty($_POST['price'])){
		foreach($_POST['license'] as $key => $value){
			if(isset($_POST['price'][$key]) && !empty($_POST['price'][$key])){
				$license_array[$value] = (float)$_POST['price'][$key];
			}
		}
	}

	$data    = array('status' => 400);
    $me      = $user->user_data;
    //if (!empty($_FILES['photo'])) {
        $inserted_data = array();
        $is_ok = true;





        $media = new Media();
        $media->setFile(array(
            'file' => $_FILES['photo']['tmp_name'],
            'name' => $_FILES['photo']['name'],
            'size' => $_FILES['photo']['size'],
            'type' => $_FILES['photo']['type'],
            'allowed' => 'jpeg,jpg,png',
            'crop' => array(),
            'avatar' => false
        ));

        $upload = $media->uploadFile();

        if (!empty($upload['filename'])) {

            $size = getimagesize($upload['filename']);
            if( $size[0] < $config['min_image_width'] || $size[1] < $config['min_image_height'] ){
                @unlink($upload['filename']);
                $media->uploadToFtp($upload['filename'], true);
                $media->uploadToS3($upload['filename'], true);
                $data['message'] = str_replace(array('{0}','{1}'), array($config['min_image_width'],$config['min_image_height']), lang('image_dimension_error')) ;
                echo json_encode($data, JSON_PRETTY_PRINT);
                exit();
            }
            $is_ok = true;
            $inserted_data['full_file'] = $upload['filename'];

            $logo = $config['site_url'] . '/media/img/logo.' . $config['logo_extension'];

            $dir         = "media/upload";
            $generate    = date('Y') . '/' . date('m') . '/' . date('Y') . md5(time()) . date('m') . '_' . date('d') . '_' . md5(time());
            $file_path   = "photos/" . $generate . "_image.jpg";
            $filename    = $dir . '/' . $file_path;
            try {
                $image = new \claviska\SimpleImage();

                $image
                    ->fromFile($upload['filename'])
                    ->autoOrient()
                    ->overlay($logo, $config['watermark_anchor'], $config['watermark_opacity'], 0, 0)
                    ->toFile($filename, 'image/jpeg');

                $inserted_data['small_file'] = $filename;

            } catch(Exception $err) {

                $data['message'] = lang('unknown_error');
            }


        }
        if ($is_ok == true) {
            $inserted_data['title'] = !empty($_POST['title']) ? Generic::secure($_POST['title']) : '';
            $inserted_data['tags'] = !empty($_POST['tags']) ? Generic::secure($_POST['tags']) : '';
            $inserted_data['license'] =  'none';
            $inserted_data['price'] =  '0.00';
            $inserted_data['category'] = !empty($_POST['category']) ? Generic::secure($_POST['category']) : '';
            //$inserted_data['user_id'] = $me->user_id;
			//$inserted_data['created_date'] = time();
			$inserted_data['license_options'] = serialize($license_array);
            $id = Generic::$db->where('id', $_POST['id'])->update(T_STORE, $inserted_data);
            if ($id > 0) {
                $data['message'] = lang('img_upload_success');
                $data['status'] = 200;
            }
            else{
                $data['message'] = lang('unknown_error');
            }
        }
    //}
    //else{
    //    $data['message'] = lang('please_check_details');
    //}
}
else if ($action == 'upload_store_image' && IS_LOGGED) {
	$license_array = array();
	if( !empty($_POST['license']) && !empty($_POST['price'])){
		foreach($_POST['license'] as $key => $value){
			if(isset($_POST['price'][$key]) && !empty($_POST['price'][$key])){
				$license_array[$value] = (float)$_POST['price'][$key];
			}
		}
	}
	
    $data    = array('status' => 400);
    $me      = $user->user_data;
    if (!empty($_FILES['photo'])) {
        $inserted_data = array();
        $is_ok = false;
        
        $media = new Media();
        $amazone_s3 = $media::$config['amazone_s3'];
        $ftp_upload = $media::$config['ftp_upload'];
        $google_cloud_storage = $media::$config['google_cloud_storage'];
        $digital_ocean = $media::$config['digital_ocean'];
        $media::$config['amazone_s3'] = 0;
        $media::$config['ftp_upload'] = 0;
        $media::$config['google_cloud_storage'] = 0;
        $media::$config['digital_ocean'] = 0;
        $media->setFile(array(
            'file' => $_FILES['photo']['tmp_name'],
            'name' => $_FILES['photo']['name'],
            'size' => $_FILES['photo']['size'],
            'type' => $_FILES['photo']['type'],
            'allowed' => 'jpeg,jpg,png',
            'crop' => array(),
			'avatar' => true,
			'compress' => false
        ));

        $upload = $media->uploadFile();
 
        if (!empty($upload['filename'])) {

            $size = getimagesize($upload['filename']);
            if( $size[0] < $config['min_image_width'] || $size[1] < $config['min_image_height'] ){
                @unlink($upload['filename']);
                $media->deleteFromFTPorS3($upload['filename']);
                $data['message'] = str_replace(array('{0}','{1}'), array($config['min_image_width'],$config['min_image_height']), lang('image_dimension_error')) ;
                echo json_encode($data, JSON_PRETTY_PRINT);
                exit();
            }
            $is_ok = true;
            $inserted_data['full_file'] = $upload['filename'];

            $logo = $config['site_url'] . '/media/img/logo.' . $config['logo_extension'];

            $dir         = "media/upload";
            $generate    = date('Y') . '/' . date('m') . '/' . date('Y') . md5(time()) . date('m') . '_' . date('d') . '_' . md5(time());
            $file_path   = "photos/" . $generate . "_image.jpg";
            $filename    = $dir . '/' . $file_path;
            try {
                $image = new \claviska\SimpleImage();

                $image
                    ->fromFile($upload['filename'])
                    ->autoOrient()
					->overlay($logo, $config['watermark_anchor'], $config['watermark_opacity'], 0, 0)
					->fitToHeight(400)
                    ->toFile($filename, 'image/jpeg');

                $inserted_data['small_file'] = $filename;

                $media::$config['amazone_s3'] = $amazone_s3;
		        $media::$config['ftp_upload'] = $ftp_upload;
		        $media::$config['google_cloud_storage'] = $google_cloud_storage;
		        $media::$config['digital_ocean'] = $digital_ocean;
		        $delete_from_stroage = false;

		        if ($media::$config['ftp_upload'] == 1) {
					$upload_     = $media->uploadToFtp($filename, false);
					$upload_     = $media->uploadToFtp($upload['filename'], $delete_from_stroage);
				} else if ($media::$config['amazone_s3'] == 1) {
					$upload_     = $media->uploadToS3($filename, false);
					$upload_     = $media->uploadToS3($upload['filename'], $delete_from_stroage);
				} else if ($media::$config['google_cloud_storage'] == 1) {
					$upload_     = $media->uploadToGoogleCloud($filename, false);
					$upload_     = $media->uploadToGoogleCloud($upload['filename'], $delete_from_stroage);
				} else if ($media::$config['digital_ocean'] == 1) {
					$upload_     = $media->UploadToDigitalOcean($filename, false);
					$upload_     = $media->UploadToDigitalOcean($upload['filename'], $delete_from_stroage);
				}

            } catch(Exception $err) {

                $data['message'] = lang('unknown_error');
            }


        }
        else{
			if (!empty($upload['error'])) {
				$data['message'] = $upload['error'];
			}else{
				$data['message'] = lang('your_photo_invalid');
			}
        }

        if ($is_ok == true) {
            $inserted_data['title'] = !empty($_POST['title']) ? Generic::secure($_POST['title']) : '';
            $inserted_data['tags'] = !empty($_POST['tags']) ? Generic::secure($_POST['tags']) : '';
            $inserted_data['license'] = 'none';
            $inserted_data['price'] = '0.00';
            $inserted_data['category'] = !empty($_POST['category']) ? Generic::secure($_POST['category']) : '';
            $inserted_data['user_id'] = $me->user_id;
			$inserted_data['created_date'] = time();
			$inserted_data['license_options'] = serialize($license_array);
            $id = Generic::$db->insert(T_STORE, $inserted_data);
            if ($id > 0) {
                $data['message'] = lang('img_upload_success');
                $data['status'] = 200;
            }
            else{
                $data['message'] = lang('unknown_error');
            }
        }
    }
    else{
        $data['message'] = lang('please_check_details');
    }
}else if($action == 'delete-funding' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$id      = $_POST['id'];
		$data['status'] = 304;
		$deleted = Generic::$db->where('user_id', $me['user_id'])->where('id', $id)->delete(T_FUNDING);
		if ($deleted) {
		//	$delete = $posts->deletePostComment($id);
			$data['status'] = 200;
		}
	}
}else if($action == 'fund_report' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$id      = $_POST['id'];
		$message = $_POST['message'];
		$data['status'] = 304;
		Generic::$db->where('user_id',$me['user_id']);
		Generic::$db->where('fund_id',$id);
		if(Generic::$db->getValue(T_FUND_REPORTS,'COUNT(*)') > 0){
			Generic::$db->where('user_id',$me['user_id']);
			Generic::$db->where('fund_id',$id);
			Generic::$db->delete(T_FUND_REPORTS);

			$data['status'] = 200;
			$data['code'] = 0;
			$data['message'] = lang('report_canceled');
		}else{
			Generic::$db->insert(T_FUND_REPORTS,array(
				'user_id' => $me['user_id'],
				'fund_id' => $id,
				'text' => $message,
				'time' => time()
			));

			$data['status'] = 200;
			$data['code'] = 1;
			$data['message'] = lang('report_sent');
		}
	}
}

exit_xhr: