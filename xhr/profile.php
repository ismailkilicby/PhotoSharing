<?php

if ($action == 'load-user-followers') {
	$vl1 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));
	$vl2 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));

	if ($vl1 && $vl2) {
		$offset  = $_GET['offset'];
		$user_id = $_GET['user_id'];

		$user->setUserById($user_id);
		$following_ls   = $user->getFollowers($offset,50);
		$data['status'] = 404;

		if (!empty($following_ls)) {
			$data['html']   = "";
			$data['status'] = 200;
			$following_ls   = o2array($following_ls);

			foreach ($following_ls as $udata) {
				$data['html']    .= $pixelphoto->PX_LoadPage('profile/templates/profile/includes/followers-ls-item');
			}
		}
	}
}

if ($action == 'load-user-following') {
	$vl1 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));
	$vl2 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));

	if ($vl1 && $vl2) {
		$offset  = $_GET['offset'];
		$user_id = $_GET['user_id'];

		$user->setUserById($user_id);
		$following_ls   = $user->getFollowing($offset,50);
		$data['status'] = 404;

		if (!empty($following_ls)) {
			$data['html']   = "";
			$data['status'] = 200;
			$following_ls   = o2array($following_ls);

			foreach ($following_ls as $udata) {
				$data['html']    .= $pixelphoto->PX_LoadPage('profile/templates/profile/includes/following-ls-item');
			}
		}
	}
}

if ($action == 'load-user-subscriptions') {
	$data['status'] = 404;
	$vl1 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));
	$vl2 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));

	if ($vl1 && $vl2) {
		$offset  = $_GET['offset'];
		$user_id = $_GET['user_id'];
		$month = 60 * 60 * 24 * 30;
		$subscriptions = $db->where('subscriber_id',$user_id)->where('id',$offset,'<')->where('time',(time() - $month),'>=')->orderBy("id","DESC")->get(T_SUBSCRIBERS,50);
		$context['subscriptions'] = array();
		if (!empty($subscriptions)) {
			$data['html']   = "";
			$data['status'] = 200;
			foreach ($subscriptions as $key => $value) {
				$user_info = $user->setUserById($value->user_id)->getUser();
				$user_info->sub_id = $value->id;
				$udata = o2array($user_info);
				$data['html']    .= $pixelphoto->PX_LoadPage('profile/templates/profile/includes/subscriptions_list');
			}
		}
	}
}
if ($action == 'create_ad') {
	$bidding_array = array('clicks','views');
	$appears_array = array('post','sidebar');
	$data['status'] = 400;
	if (empty($_POST['company']) || empty($_POST['url']) || empty($_POST['title']) || empty($_POST['location']) || empty($_POST['description']) || empty($_POST['bidding']) || !in_array($_POST['bidding'], $bidding_array) || empty($_POST['appears']) || !in_array($_POST['appears'], $appears_array) || empty($_FILES['image']) || empty($_POST['country']) || empty($_POST['gender'])) {
		$data['message'] = lang('please_check_details');
	}
	elseif (!Generic::isUrl($_POST['url'])) {
		$data['message'] = lang('url_invalid');
	}
	elseif ($me['wallet'] < 1) {
		$data['message'] = lang('top_wallet');
	}
	else{
		$media  = new Media();
		$media->setFile(array(
			'file' => $_FILES['image']['tmp_name'],
			'name' => $_FILES['image']['name'],
			'size' => $_FILES['image']['size'],
			'type' => $_FILES['image']['type'],
			'allowed' => 'jpeg,jpg,png,webp,gif',
		));
		$image = $media->uploadFile();
		if (!empty($image['filename'])) {
			$country = '';
			if (!empty($_POST['country'])) {
				$country = Generic::secure('{'.implode('},{', $_POST['country']).'}');
			}

			$insert_array = array('name' => Generic::secure($_POST['company']),
		                          'url'  => Generic::secure($_POST['url']),
		                          'headline' => Generic::secure($_POST['title']),
		                          'location' => Generic::secure($_POST['location']),
		                          'appears'  => Generic::secure($_POST['appears']),
		                          'bidding'  => Generic::secure($_POST['bidding']),
		                          'audience' => $country,
		                          'gender'   => Generic::secure($_POST['gender']),
		                          'description'   => Generic::secure($_POST['description']),
		                          'posted'   => time(),
		                          'user_id'  => $me['user_id'],
		                          'ad_media' => $image['filename']);
			$db->insert(T_ADS,$insert_array);
			$data['status'] = 200;
			$data['message'] = lang('ad_created');
		}
		else{
			$data['message'] = lang('media_not_supported');
		}
	}
}
if ($action == 'edit_ad') {
	$bidding_array = array('clicks','views');
	$appears_array = array('post','sidebar');
	$data['status'] = 400;
	if (empty($_POST['company']) || empty($_POST['url']) || empty($_POST['title']) || empty($_POST['location']) || empty($_POST['description']) || empty($_POST['bidding']) || !in_array($_POST['bidding'], $bidding_array) || empty($_POST['appears']) || !in_array($_POST['appears'], $appears_array) || empty($_POST['country']) || empty($_POST['gender']) || empty($_POST['id'])) {
		$data['message'] = lang('please_check_details');
	}
	elseif (!Generic::isUrl($_POST['url'])) {
		$data['message'] = lang('url_invalid');
	}
	elseif ($me['wallet'] < 1) {
		$data['message'] = lang('top_wallet');
	}
	else{
		$user = new User();
		$ad = $user->GetAdByID($_POST['id']);
		if (!empty($ad) && $ad->user_id == $me['user_id']) {
			$country = '';
			$status_array = array('0','1');
			$status = 1;
			if (!empty($_POST['country'])) {
				$country = Generic::secure('{'.implode('},{', $_POST['country']).'}');
			}
			if (in_array($_POST['status'], $status_array)) {
				$status = Generic::secure($_POST['status']);
			}

			$insert_array = array('name' => Generic::secure($_POST['company']),
		                          'url'  => Generic::secure($_POST['url']),
		                          'headline' => Generic::secure($_POST['title']),
		                          'location' => Generic::secure($_POST['location']),
		                          'appears'  => Generic::secure($_POST['appears']),
		                          'bidding'  => Generic::secure($_POST['bidding']),
		                          'audience' => $country,
		                          'gender'   => Generic::secure($_POST['gender']),
		                          'description'   => Generic::secure($_POST['description']),
		                          'posted'   => time(),
		                          'user_id'  => $me['user_id'],
		                          'status'   => $status);
			$db->where('id',$ad->id)->update(T_ADS,$insert_array);
			$data['status'] = 200;
			$data['message'] = lang('ad_edited');
		}
		else{
			$data['message'] = lang('ad_not_found');
		}
	}
}
if ($action == 'delete_ad') {
	$data['status'] = 400;
	if (empty($_POST['id'])) {
		$data['message'] = lang('please_check_details');
	}
	else{

		$user = new User();
		$media = new Media();
		$ad = $user->GetAdByID($_POST['id']);
		if (!empty($ad) && $ad->user_id == $me['user_id']) {
			$db->where('id',$ad->id)->delete(T_ADS);
			$photo_file = $ad->ad_media;
			if (file_exists($photo_file)) {
	            @unlink(trim($photo_file));
	        }
	        else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
	            $media->deleteFromFTPorS3($photo_file);
	        }
			$data['status'] = 200;
		}
		else{
			$data['message'] = lang('ad_not_found');
		}
	}
}
if ($action == 'ad_click') {
	$data['status'] = 400;
	if (empty($_POST['id'])) {
		$data['message'] = lang('please_check_details');
	}
	else{
		$user = new User();
		$ad = $user->GetAdByID($_POST['id']);
		$ads_array = array();
		if (!empty($_SESSION['ads'])) {
			$ads_array = explode(',', $_SESSION['ads']);
		}
		if (!empty($ad) && $ad->bidding == 'clicks' && !in_array($ad->id, $ads_array)) {
			$db->where('id', $ad->id)->update(T_ADS,array('clicks' => $db->inc(1)));
			$db->where('user_id', $ad->user_id)->update(T_USERS,array('wallet' => $db->dec($config['ad_c_price'])));
			$ad_user = $db->where('user_id', $ad->user_id)->getOne(T_USERS);
			$user_wallet = $ad_user->wallet - $config['ad_c_price'];
			if ($user_wallet < $config['ad_c_price']) {
				$db->where('id', $ad->id)->delete(T_ADS);
			}
			$ads_array[] = $ad->id;
			$_SESSION['ads'] = implode(',', $ads_array);
		}
		$data['status'] = 200;
	}
}

if ($action == 'create_fund') {
	$data['status'] = 400;
	if (empty($_POST['title']) || empty($_POST['description']) || empty($_FILES['image']) || empty($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] < 1) {
		$data['message'] = lang('please_check_details');
	}
	else{
		$media  = new Media();
		$media->setFile(array(
			'file' => $_FILES['image']['tmp_name'],
			'name' => $_FILES['image']['name'],
			'size' => $_FILES['image']['size'],
			'type' => $_FILES['image']['type'],
			'allowed' => 'jpeg,jpg,png,webp,gif',
		));
		$image = $media->uploadFile();
		if (!empty($image['filename'])) {
			$insert_array = array('title' => Generic::secure($_POST['title']),
		                          'description'   => Generic::secure($_POST['description']),
		                          'amount'   => Generic::secure($_POST['amount']),
		                          'time'   => time(),
		                          'user_id'  => $me['user_id'],
		                          'image' => $image['filename'],
		                          'hashed_id' => $media->generateKey(15,15));
			$db->insert(T_FUNDING,$insert_array);
			$data['status'] = 200;
			$data['message'] = lang('funding_created');
		}
		else{
			$data['message'] = lang('media_not_supported');
		}
	}
}

if ($action == 'edit_fund') {
	$data['status'] = 400;
	if (empty($_POST['title']) || empty($_POST['description']) || empty($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] < 1 || empty($_POST['id'])) {
		$data['message'] = lang('please_check_details');
	}
	else{
		$id = Generic::secure($_POST['id']);
		$fund = $db->where('id',$id)->getOne(T_FUNDING);
		if (!empty($fund) || ($context['user']['user_id'] != $fund->user_id && IS_ADMIN == false)) {
			$insert_array = array('title' => Generic::secure($_POST['title']),
			                          'description'   => Generic::secure($_POST['description']),
			                          'amount'   => Generic::secure($_POST['amount']),
			                          'time'   => time());
			if (!empty($_FILES['image'])) {
				$media  = new Media();
				$media->setFile(array(
					'file' => $_FILES['image']['tmp_name'],
					'name' => $_FILES['image']['name'],
					'size' => $_FILES['image']['size'],
					'type' => $_FILES['image']['type'],
					'allowed' => 'jpeg,jpg,png,webp,gif',
				));
				$image = $media->uploadFile();
				if (!empty($image['filename'])) {
					$insert_array['image'] = $image['filename'];
				}
				else{
					$data['message'] = lang('media_not_supported');
				}
			}

			if (empty($data['message'])) {

				$db->where('id',$id)->update(T_FUNDING,$insert_array);
				$data['status'] = 200;
				$data['message'] = lang('funding_edited');
			}
		}
		else{
			$data['message'] = lang('please_check_details');
		}
	}
}

if ($action == 'load_more_fund') {
	$data['status'] = 400;
	if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
		$data['message'] = lang('please_check_details');
	}
	else{

		$user = new User();
		$id = Generic::secure($_POST['id']);
		$context['fund'] = $user->GetFunding(10,$id);
		$html = '';
		if (!empty($context['fund'])) {
			foreach ($context['fund'] as $key => $user) {
				// $html .= '<div class="item fund_item" data-fund="'.$user->id.'"><div class="wrapper"><img class="img-circle" src="'.$user->image.'" alt="Picture" /></div><div class="caption"><a href="'.$config['site_url'].'/funding/'.$user->id.'">'.$user->title.'</a><br><div class="progress"><div class="progress-bar" role="progressbar" style="width: '.$user->bar.'%" aria-valuenow="'.$user->bar.'" aria-valuemin="0" aria-valuemax="'.$user->amount.'"></div></div><p>'.$context['currency_symbol'].$user->raised.' '.lang('raised_of').' '.$context['currency_symbol'].$user->amount.'</p></div></div>';
				$hashed_id = $user->id;
				if (!empty($user->hashed_id)) {
					$hashed_id = $user->hashed_id;
				}

				$html .= '<div class="col-md-6 col-lg-4 item fund_item" data-fund="'.$user->id.'"><div class="fundings"><div class="wrapper"><a href="'.$config['site_url'].'/funding/'.$hashed_id.'" data-ajax="ajax_loading.php?app=funding&apph=funding&id='.$hashed_id.'"><img src="'.$user->image.'" alt="Picture" /></a></div><div class="caption"><h3><a href="'.$config['site_url'].'/funding/'.$hashed_id.'" data-ajax="ajax_loading.php?app=funding&apph=funding&id='.$hashed_id.'">'.$user->title.'</a></h3><div class="progress"><div class="progress-bar" role="progressbar" style="width: '.$user->bar.'%" aria-valuenow="'.$user->bar.'" aria-valuemin="0" aria-valuemax="'.$user->amount.'"></div></div><p>'.$context['currency_symbol'].$user->raised.' '.lang('raised_of').' '.$context['currency_symbol'].$user->amount.'</p></div></div></div>';
			}
		}
		$data['status'] = 200;
		$data['html'] = $html;
	}
}

if ($action == 'load_more_recent_raise') {
	$data['status'] = 400;
	if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1 || empty($_POST['fund_id']) || !is_numeric($_POST['fund_id']) || $_POST['fund_id'] < 1) {
		$data['message'] = lang('please_check_details');
	}
	else{

		$user = new User();
		$id = Generic::secure($_POST['id']);
		$fund_id = Generic::secure($_POST['fund_id']);
		$context['recent'] = $user->GetRecentRaise($fund_id,10,$id);
		$html = '';
		if (!empty($context['recent'])) {
			foreach ($context['recent'] as $key => $user) {
				$html .= '<div class="item fund_item" data-recent="'.$user->id.'"><div class="wrapper"><img class="img-circle" src="'.$user->user_data->avatar.'" alt="Picture" /></div><div class="caption"><a href="'.$user->user_data->url.'">'.$user->user_data->name.'</a><p>'.$context['currency_symbol'].$user->amount.'&nbsp;&nbsp;&nbsp;&nbsp;<time><span class="time-ago" title="'.ToDate($user->time).'">'.time2str($user->time).'</span></time></p></div></div>';
			}
		}
		$data['status'] = 200;
		$data['html'] = $html;
	}
}

if ($action == 'load_more_user_fund') {
	$data['status'] = 400;
	if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1 || empty($_POST['user']) || !is_numeric($_POST['user']) || $_POST['user'] < 1) {
		$data['message'] = lang('please_check_details');
	}
	else{

		$user = new User();
		$id = Generic::secure($_POST['id']);
		$user_id = Generic::secure($_POST['user']);
		$context['fund'] = $user->GetFundingByUserId($user_id,10,$id);
		$html = '';
		if (!empty($context['fund'])) {
			foreach ($context['fund'] as $key => $user) {
				$hashed_id = $user->id;
				if (!empty($user->hashed_id)) {
					$hashed_id = $user->hashed_id;
				}
				// $html .= '<div class="item fund_item" data-fund="'.$user->id.'"><div class="wrapper"><img class="img-circle" src="'.$user->image.'" alt="Picture" /></div><div class="caption"><a href="'.$config['site_url'].'/funding/'.$user->id.'">'.$user->title.'</a><br><div class="progress"><div class="progress-bar" role="progressbar" style="width: '.$user->bar.'%" aria-valuenow="'.$user->bar.'" aria-valuemin="0" aria-valuemax="'.$user->amount.'"></div></div><p>'.$context['currency_symbol'].$user->raised.' '.lang('raised_of').' '.$context['currency_symbol'].$user->amount.'</p></div></div>';
				$html .= '<div class="col-md-6 item fund_item" data-fund="'.$user->id.'"><div class="fundings"><div class="wrapper"><a href="'.$config['site_url'].'/funding/'.$hashed_id.'" data-ajax="ajax_loading.php?app=funding&apph=funding&id='.$hashed_id.'"><img src="'.$user->image.'" alt="Picture" /></a></div><div class="caption"><h3><a href="'.$config['site_url'].'/funding/'.$hashed_id.'" data-ajax="ajax_loading.php?app=funding&apph=funding&id='.$hashed_id.'">'.$user->title.'</a></h3><div class="progress"><div class="progress-bar" role="progressbar" style="width: '.$user->bar.'%" aria-valuenow="'.$user->bar.'" aria-valuemin="0" aria-valuemax="'.$user->amount.'"></div></div><p>'.$context['currency_symbol'].$user->raised.' '.lang('raised_of').' '.$context['currency_symbol'].$user->amount.'</p></div></div></div>';
			}
		}
		$data['status'] = 200;
		$data['html'] = $html;
	}
}

if ($action == 'delete-fund') {
	$data['status'] = 400;
	if (empty($_POST['fund_id'])) {
		$data['message'] = lang('please_check_details');
	}
	else{
		$id = Generic::secure($_POST['fund_id']);
		$fund = $db->where('id',$id)->getOne(T_FUNDING);
		if (!empty($fund) || ($context['user']['user_id'] != $fund->user_id && IS_ADMIN == false)) {
			$del = new Media();
			$del->deleteFromFTPorS3($fund->image);

			if (file_exists($fund->image)) {
				try {
					unlink($fund->image);
				}
				catch (Exception $e) {
				}
			}

			$db->where('id',$id)->delete(T_FUNDING);

			$data['status'] = 200;
		}
		else{
			$data['message'] = lang('please_check_details');
		}
	}
}
