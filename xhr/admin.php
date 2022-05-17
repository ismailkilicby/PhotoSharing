<?php 
use Aws\S3\S3Client;
use Google\Cloud\Storage\StorageClient;

if ( empty(IS_ADMIN) && ( $action !== 'add_new_blog_article' || $action !== 'edit_blog_article' ) ) {
	echo "Unknown dolphin";
	exit();
}
elseif ($action == 'ReadNotify') {
	$admin::$db->where('recipient_id',0)->where('admin',1)->where('seen',0)->update(T_NOTIF,array('seen' => time()));
}
elseif ($action == 'search_in_pages') {
    $keyword = $admin::secure($_POST['keyword']);
    $html = '';

    $files = scandir('./admin-panel/pages');
    $not_allowed_files = array('edit-custom-page','edit-lang','edit-movie','edit-profile-field','edit-terms-pages'); 
    foreach ($files as $key => $file) {
        if (file_exists('./admin-panel/pages/'.$file.'/content.phtml') && !in_array($file, $not_allowed_files)) {
            
            $string = file_get_contents('./admin-panel/pages/'.$file.'/content.phtml');
            preg_match_all("@(?s)<h2([^<]*)>([^<]*)<\/h2>@", $string, $matches1);

            if (!empty($matches1) && !empty($matches1[2])) {
                foreach ($matches1[2] as $key => $title) {
                    if (strpos(strtolower($title), strtolower($keyword)) !== false) {
                        $page_title = '';
                        preg_match_all("@(?s)<h2([^<]*)>([^<]*)<\/h2>@", $string, $matches3);
                        if (!empty($matches3) && !empty($matches3[2])) {
                            foreach ($matches3[2] as $key => $title2) {
                                $page_title = $title2;
                                break;
                            }
                        }
                        $html .= '<a href="'.pxp_acp_link($file).'?highlight='.$keyword.'"><div  style="padding: 5px 2px;">'.$page_title.'</div><div><small style="color: #333;">'.$title.'</small></div></a>';
                        break;
                    }
                }
            }

            preg_match_all("@(?s)<label([^<]*)>([^<]*)<\/label>@", $string, $matches2);
            if (!empty($matches2) && !empty($matches2[2])) {
                foreach ($matches2[2] as $key => $lable) {
                    if (strpos(strtolower($lable), strtolower($keyword)) !== false) {
                        $page_title = '';
                        preg_match_all("@(?s)<h2([^<]*)>([^<]*)<\/h2>@", $string, $matches3);
                        if (!empty($matches3) && !empty($matches3[2])) {
                            foreach ($matches3[2] as $key => $title2) {
                                $page_title = $title2;
                                break;
                            }
                        }

                        $html .= '<a href="'.pxp_acp_link($file).'?highlight='.$keyword.'"><div  style="padding: 5px 2px;">'.$page_title.'</div><div><small style="color: #333;">'.$lable.'</small></div></a>';
                        break;
                    }
                }
            }
        }
    }
    $data = array(
                'status' => 200,
                'html'   => $html
            );
}
elseif ($action == 'general-settings' && !empty($_POST)) {
	$admin  = new Admin();
	$update = $_POST;
	
	$data   = array('status' => 304);
	$error  = false;

	if (!empty($_POST['import_videos']) && $_POST['import_videos'] == 'on' && empty($config['yt_api'])) {
		$error = "Youtube api key is reqired to import videos";
	}

	if (!empty($_POST['import_images']) && $_POST['import_images'] == 'on' && empty($config['giphy_api'])) {
		$error = "Giphy api key is reqired to import images/gifs";
	}

	if (empty($error)) {
		$query  = $admin->updateSettings($update);

		if ($query == true) {
			$data['status'] = 200;
		}
	}

	else{
		$data['status'] = 400;
		$data['error']  = $error;
	}
}
elseif ($action == 'ad-settings' && !empty($_POST)) {
	$admin  = new Admin();
	$update = array();	
	$data   = array('status' => 304);
	$error  = false;
    
    if (ISSET($_POST['ad1'])) {
    	if (!empty($_POST['ad1'])) {
    		$update['ad1'] = base64_decode($_POST['ad1']);
    	}else{
    	    $update['ad1'] = '';
    	}
    }
	
    if (ISSET($_POST['ad2'])) {
    	if (!empty($_POST['ad2'])) {
    		$update['ad2'] = base64_decode($_POST['ad2']);
    	}else{
    	    $update['ad2'] = '';
    	}
    }
    
    if (ISSET($_POST['ad3'])) {
    	if (!empty($_POST['ad3'])) {
    		$update['ad3'] = base64_decode($_POST['ad3']);
    	}else{
    	    $update['ad3'] = '';
    	}
    }

	if (empty($error)) {
		$query  = $admin->updateSettings($update);

		if ($query == true) {
			$data['status'] = 200;
		}
	}

	//else{
	//	$data['status'] = 400;
	//	$data['error']  = $error;
	//}
}
elseif ($action == 'site-settings' && !empty($_POST)) {
	$admin  = new Admin();
	$update = $_POST;	
	$data   = array('status' => 304);
	$error  = false;

	if (!empty($update['google_analytics'])) {
		$update['google_analytics'] = $admin::encode($update['google_analytics']);
	}

	if (empty($error)) {
		$query  = $admin->updateSettings($update);

		if ($query == true) {
			$data['status'] = 200;
		}
	}

	else{
		$data['status'] = 400;
		$data['error']  = $error;
	}
}
elseif ($action == 'email-settings' && !empty($_POST)) {
	$admin  = new Admin();
	$update = $_POST;
	$data   = array('status' => 304);
	$error  = false;

	if (empty($error)) {
		$query  = $admin->updateSettings($update);
		foreach ($_POST as $key => $value) {
			if ($key == 'smtp_password') {
				$value = openssl_encrypt($value, "AES-128-ECB", 'mysecretkey1234');
				$admin->updateSettings(array('smtp_password' => $value));
			}
			if ($key == 'agora_chat_video') {
		        if ($config['twilio_video_chat'] == 'on'){
		            $admin->updateSettings(array('twilio_video_chat' => 'off'));
		        }
		    }
			if ($key == 'twilio_video_chat') {
		        if ($config['agora_chat_video'] == 'on'){
		            $admin->updateSettings(array('agora_chat_video' => 'off'));
		        }
		    }
		}
		

		if ($query == true) {
			$data['status'] = 200;
		}
	}

	else{
		$data['status'] = 400;
		$data['error']  = $error;
	}
}
elseif ($action == 'storeg-settings' && !empty($_POST)) {
	$admin  = new Admin();
	$update = $_POST;
	$data   = array('status' => 304);
	$error  = false;

    $ftp_upload = (ISSET($_POST['ftp_upload']) ? $_POST['ftp_upload'] : '');
    $amazone_s3 = (ISSET($_POST['amazone_s3']) ? $_POST['amazone_s3'] : '');
	$digital_ocean = (ISSET($_POST['digital_ocean']) ? $_POST['digital_ocean'] : '');
	$google_cloud_storage = (ISSET($_POST['google_cloud_storage']) ? $_POST['google_cloud_storage'] : '');

    if( $ftp_upload == 1 ){
        $admin->updateSettings(array(
					'amazone_s3' => 0,
					'digital_ocean' => 0,
					'google_cloud_storage' => 0
		));	
    }
    if( $amazone_s3 == 1 ){
		$admin->updateSettings(array(
					'ftp_upload' => 0,
					'digital_ocean' => 0,
					'google_cloud_storage' => 0
		));
	}
	if( $digital_ocean == 1 ){
		$admin->updateSettings(array(
					'ftp_upload' => 0,
					'amazone_s3' => 0,
					'google_cloud_storage' => 0
		));
	}
	if( $google_cloud_storage == 1 ){
		$admin->updateSettings(array(
					'ftp_upload' => 0,
					'amazone_s3' => 0,
					'digital_ocean' => 0
		));
	}
	
	$query  = $admin->updateSettings($update);

	if ($query == true) {
		$data['status'] = 200;
	}else{
	    $data['status'] = 400;
	    $data['error']  = "";
	}
                
}
elseif ($action == 'login-settings' && !empty($_POST)) {
	$admin  = new Admin();
	$update = $_POST;
	$data   = array('status' => 304);
	$error  = false;

	$en_fb  = (!empty($_POST['fb_login']) && $_POST['fb_login'] == 'on');
	$en_tw  = (!empty($_POST['tw_login']) && $_POST['tw_login'] == 'on');
	$en_gl  = (!empty($_POST['gl_login']) && $_POST['gl_login'] == 'on');

	// if  ($en_fb && (empty($config['facebook_app_id']) || empty($config['facebook_app_key']))) {
	// 	$error = "To enable facebook login application key and id are required";
	// }

	// elseif ($en_tw && (empty($config['twitter_app_id']) || empty($config['twitter_app_key']))) {
	// 	$error = "To enable twitter login application key and id are required";
	// }

	// elseif ($en_gl && (empty($config['google_app_id']) || empty($config['google_app_key']))) {
	// 	$error = "To enable google login application key and id are required";
	// }

	if (empty($error)) {
		$query  = $admin->updateSettings($update);

		if ($query == true) {
			$data['status'] = 200;
		}
	}

	else{
		$data['status'] = 400;
		$data['error']  = $error;
	}
}
elseif ($action == 'delete_multi_users') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('activate','deactivate','delete','free'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$value = $user::secure($value);
                if ($_POST['type'] == 'delete') {
                	$user->setUserById($value)->delete();
                }
                elseif ($_POST['type'] == 'activate') {
                    $db->where('user_id', $value);

                    $update_data = array('active' => '1','email_code' => '');
                    $update = $db->update(T_USERS, $update_data);
                }
                elseif ($_POST['type'] == 'deactivate') {
                    $db->where('user_id', $value);

                    $update_data = array('active' => 0,'email_code' => '');
                    $update = $db->update(T_USERS, $update_data);
                }
                elseif ($_POST['type'] == 'free') {
                	$db->where('user_id', $value);

                    $update_data = array('is_pro' => '0');
                    $update = $db->update(T_USERS, $update_data);
                }
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'delete_multi_article') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('publish','unpublish','delete'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$value = $user::secure($value);
            	$article = GetArticle($value);
                if ($_POST['type'] == 'delete') {
                	DeleteArticle($value, $article['thumbnail']);
                }
                elseif ($_POST['type'] == 'publish') {
                    PublishArticle($value);
                }
                elseif ($_POST['type'] == 'unpublish') {
                    UnPublishArticle($value);
                }
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'delete_multi_report') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('mark_safe','delete'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$value = $user::secure($value);
            	$report = $admin::$db->where('id', $value)->getOne(T_USER_REPORTS);
                if ($_POST['type'] == 'delete') {
                	$user->setUserById($report->profile_id)->delete();
                }
                elseif ($_POST['type'] == 'mark_safe') {
					$admin   = new Admin();
					$delete  = $admin::$db->where('id',$value)->delete(T_USER_REPORTS);
                }
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'delete_multi_post_report') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('mark_safe','delete'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$value = $user::secure($value);
            	$report = $admin::$db->where('id', $value)->getOne(T_POST_REPORTS);
                if ($_POST['type'] == 'delete') {
                	$posts   = new Posts();
					$delete  = $posts->setPostId($report->post_id)->deletePost();
                }
                elseif ($_POST['type'] == 'mark_safe') {
					$admin   = new Admin();
					$delete  = $admin::$db->where('id',$value)->delete(T_POST_REPORTS);
                }
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'delete_multi_fund_report') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('mark_safe','delete'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$value = $user::secure($value);
            	$report = $admin::$db->where('id', $value)->getOne(T_FUND_REPORTS);
                if ($_POST['type'] == 'delete') {
                	$id = $report->fund_id;
					$admin   = new Admin();
					$fund = $admin::$db->where('id',$id)->getOne(T_FUNDING);
					$media = new Media();
					$photo_file = $fund->image;
					if (file_exists($photo_file)) {
				        @unlink(trim($photo_file));
				    }
				    else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
				        $media->deleteFromFTPorS3($photo_file);
				    }
					$delete  = $admin::$db->where('id',$id)->delete(T_FUNDING);
					$delete  = $admin::$db->where('funding_id',$id)->delete(T_FUNDING_RAISE);
                }
                elseif ($_POST['type'] == 'mark_safe') {
					$admin   = new Admin();
					$delete  = $admin::$db->where('id',$value)->delete(T_FUND_REPORTS);
                }
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'cancel_pro') {
	$users_id = $admin::$db->where('type', 'pro_member')->where('time', strtotime("-30 days"), '<')->get(T_TRANSACTIONS, null, array('user_id'));
    $ids = array();
    foreach ($users_id as $key => $value) {
        $ids[] = $value->user_id;
    }
    if(!empty($ids)) {
        $admin::$db->where('user_id', $ids, "IN")->update(T_USERS, array('is_pro' => '0'));
    }
    $data = ['status' => 200];
}


elseif ($action == 'delete-user' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$user_id = $user::secure($_POST['id']);
	$delete  = $user->setUserById($user_id)->delete();
	$data    = array('status' => 304);
	
	if ($delete) {
		$data['status'] = 200;
	}
}
elseif ($action == 'delete-post' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$post_id = $user::secure($_POST['id']);
	$posts   = new Posts();
	$delete  = $posts->setPostId($post_id)->deletePost();
	$data    = array('status' => 304);

	if ($delete) {
		$data['status'] = 200;
	}
}
elseif ($action == 'delete-multi-post' && !empty($_POST['ids'])) {

	foreach ($_POST['ids'] as $key => $id) {
        $post_id = $user::secure($id);
		$posts   = new Posts();
		$delete  = $posts->setPostId($post_id)->deletePost();
    }
	
	$data    = array('status' => 304);

	if ($delete) {
		$data['status'] = 200;
	}
}
elseif ($action == 'delete-ad' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$ad_id = $user::secure($_POST['id']);
	$data    = array('status' => 304);
	$user = new User();
	$media = new Media();
	
	$ad = $user->GetAdByID($ad_id);
	if (!empty($ad)) {
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
}
elseif ($action == 'delete-fund' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	$id = $user::secure($_POST['id']);
	$admin   = new Admin();
	$fund = $admin::$db->where('id',$id)->getOne(T_FUNDING);
	$media = new Media();
	$photo_file = $fund->image;
	if (file_exists($photo_file)) {
        @unlink(trim($photo_file));
    }
    else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
        $media->deleteFromFTPorS3($photo_file);
    }
	$delete  = $admin::$db->where('id',$id)->delete(T_FUNDING);
	$delete  = $admin::$db->where('funding_id',$id)->delete(T_FUNDING_RAISE);

    

	$data    = array('status' => 304);
	
	if ($delete) {
		$data['status'] = 200;
	}
}
elseif ($action == 'remove_multi_ban') {
    if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
            if (!empty($value) && is_numeric($value) && $value > 0) {
            	$admin    = new Admin();
				$id = $user::secure($value);
				$admin::$db->where('id',$id);
				$admin::$db->delete(T_BLACKLIST);
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'remove_multi_blog_category') {
    if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
        	if (!empty($value) && in_array($value, array_keys(blog_categories()))) {
		        $db->where('lang_key',Generic::secure($value))->delete(T_LANGS);
		    }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'remove_multi_ads') {
    if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
            if (!empty($value) && is_numeric($value) && $value > 0) {
            	$ad_id = $user::secure($value);
				$user = new User();
				$media = new Media();
				
				$ad = $user->GetAdByID($ad_id);
				if (!empty($ad)) {
					$db->where('id',$ad->id)->delete(T_ADS);
					$photo_file = $ad->ad_media;
					if (file_exists($photo_file)) {
			            @unlink(trim($photo_file));
			        }
			        else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
			            $media->deleteFromFTPorS3($photo_file);
			        }
				}
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'remove_multi_fund') {
    if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
            if (!empty($value) && is_numeric($value) && $value > 0) {
            	$id = $user::secure($value);
				$admin   = new Admin();
				$fund = $admin::$db->where('id',$id)->getOne(T_FUNDING);
				$media = new Media();
				$photo_file = $fund->image;
				if (file_exists($photo_file)) {
			        @unlink(trim($photo_file));
			    }
			    else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
			        $media->deleteFromFTPorS3($photo_file);
			    }
				$delete  = $admin::$db->where('id',$id)->delete(T_FUNDING);
				$delete  = $admin::$db->where('funding_id',$id)->delete(T_FUNDING_RAISE);
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'activate-theme' && !empty($_POST['theme'])) {
	$theme   = $user::secure($_POST['theme']);
	$admin   = new Admin();
	$data    = array('status' => 304);
	$update  = $admin->updateSettings(array('theme' => $theme));
	if ($update) {
		$data['status'] = 200;
	}
}
elseif ($action == 'delete-report' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	if (!empty($_POST['t']) && is_numeric($_POST['t'])) {
		$rid     = $user::secure($_POST['id']);
		$type    = $user::secure($_POST['t']);
		$admin   = new Admin();
		$table   = ($type == 2) ? T_POST_REPORTS : T_USER_REPORTS;
		$data    = array('status' => 304);
		$delete  = $admin::$db->where('id',$rid)->delete($table);
		if ($delete) {
			$data['status'] = 200;
		}
	}
}
elseif ($action == 'delete-fund-report' && !empty($_POST['id']) && is_numeric($_POST['id'])) {
	if (!empty($_POST['t']) && is_numeric($_POST['t'])) {
		$rid     = $user::secure($_POST['id']);
		$type    = $user::secure($_POST['t']);
		$admin   = new Admin();
		$table   = T_FUND_REPORTS;
		$data    = array('status' => 304);
		$delete  = $admin::$db->where('id',$rid)->delete($table);
		if ($delete) {
			$data['status'] = 200;
		}
	}
}
elseif ($action == 'generate-sitemap') {
	try {
		$sitemap = new Sitemap($site_url);
		$admin   = new Admin();
		$sitemap->setPath('sitemap/');

		{ 
			$sitemap->addItem('/about-us', '0.8', 'yearly', 'Never');
			$sitemap->addItem('/terms-of-use', '0.8', 'yearly', 'Never');
			$sitemap->addItem('/privacy-and-policy','0.8', 'yearly', 'Never');
			$sitemap->addItem('/welcome','0.8', 'yearly', 'Never');
			$sitemap->addItem('/signup','0.8', 'yearly', 'Never');
			$sitemap->addItem('/explore','0.8', 'yearly', 'Never');
		}
		
		{   
			$posts = $admin::$db->get(T_POSTS,null,array('post_id','time'));
			foreach ($posts as $post) {
				$pid = $post->post_id;
				$sitemap->addItem("/post/$pid", '0.8', 'daily', $post->time);
			}
		}

		$sitemap->createSitemapIndex("$site_url/sitemap/");
		$data['status']  = 200;
		$data['message'] = "New sitemap has been successfully generated";
		$data['time']    = date('Y-m-d h:i:s');
	} 
	catch (Exception $e) {
		$data['status']  = 500;
		$data['message'] = "ERROR: Permission denied in " . ROOT . '/sitemap/';
	}
}
elseif ($action == 'create-backup') {
	$error  = false;
	$admin  = new Admin();
	$zip_ex = class_exists('ZipArchive');

	if (empty($zip_ex)) {
		$error = 'ERROR: ZipArchive is not installed on your server';
	}

	else if(empty(is_writable(ROOT))){
		$error = 'ERROR: Permission denied in ' . ROOT . '/script_backups';
	}

	if (empty($error)) {
		try {
			$backup = $admin->createBackup();
			if ($backup == true) {
				$data['status']  = 200;
				$data['message'] = "New site backup has been successfully created";
				$data['time']    = date('Y-m-d h:i:s');
			}
		} 

		catch (Exception $e) {
			$data['status']  = 500;
			$data['message'] = "Something went wrong Please try again later!";
		}
	}

	else{
		$data['status']  = 500;
		$data['message'] = $error;
	}
}
elseif ($action == 'edit-lang-key') {
	$admin  = new Admin();
	$vl1    = (!empty($_POST['id']) && is_numeric($_POST['id']));
	$vl2    = (!empty($_POST['val']) && is_string($_POST['val']));
	$vl3    = (!empty($_POST['lang']) && in_array($_POST['lang'], array_keys($langs)));
	$vl4    = ($vl1 && $vl2 && $vl3);
	$data   = array(
		'status' => 400,
		'message' => "Something went wrong Please try again later!"
	);

	if ($vl4) {
		$key_id = $admin::secure($_POST['id']);
		$key_vl = $admin::secure($_POST['val']);
		$lang   = $admin::secure($_POST['lang']);

		$admin::$db->where('id',$key_id)->update(T_LANGS,array($lang => $key_vl));
		$data['status']  = 200;
		$data['message'] = "Language changes has been successfully saved";
	}
}
elseif ($action == 'delete-lang') {
	$admin  = new Admin();
	$t_lang = T_LANGS;
	$data   = array(
		'status' => 400,
	);

	if (!empty($_POST['id']) && in_array($_POST['id'], array_keys($langs)) && len(array_keys($langs)) >= 2) {
		$lang = $_POST['id'];
		try {
			@$admin::$db->rawQuery("ALTER TABLE `$t_lang` DROP `$lang`");
			$data   = array(
				'status' => 200,
			);
		} 

		catch (Exception $e) {
			
		}
	}
}
elseif ($action == 'remove_multi_lang') {
	$admin  = new Admin();
	$t_lang = T_LANGS;
	$data   = array(
		'status' => 400,
	);
	if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
        	if (!empty($value) && in_array($value, array_keys($langs)) && len(array_keys($langs)) >= 2) {
				$lang = $admin::secure($value);
				try {
					@$admin::$db->rawQuery("ALTER TABLE `$t_lang` DROP `$lang`");
				} 
				catch (Exception $e) {
					
				}
			}
        }
        $data   = array(
			'status' => 200,
		);
    }

			
}
elseif ($action == 'terms-of-use' && !empty($_POST['terms'])) {
	$admin = new Admin();
	$page  = base64_decode(encode($_POST['terms']));
	$data  = array(
		'status' => 400,
		'message' => 'Can not save page, please check the details'
	);

	$save  = $admin->savePage('terms_of_use',$page);
	if ($save) {
		$data  = array(
			'status' => 200,
			'message' => 'New terms of use has been successfully saved!'
		);
	}
}
elseif ($action == 'about-us' && !empty($_POST['about_us'])) {
	$admin = new Admin();
	$page  = base64_decode(encode($_POST['about_us']));
	$data  = array(
		'status' => 400,
		'message' => 'Can not save page, please check the details'
	);

	$save  = $admin->savePage('about_us',$page);
	if ($save) {
		$data  = array(
			'status' => 200,
			'message' => 'Your changes has been successfully saved!'
		);
	}
}
elseif ($action == 'contact_us' && !empty($_POST['contact_us'])) {
	$admin = new Admin();
	$page  = base64_decode(encode($_POST['contact_us']));
	$data  = array(
		'status' => 400,
		'message' => 'Can not save page, please check the details'
	);

	$save  = $admin->savePage('contact_us',$page);
	if ($save) {
		$data  = array(
			'status' => 200,
			'message' => 'Your changes has been successfully saved!'
		);
	}
}
elseif ($action == 'privacy-and-policy' && !empty($_POST['privacy'])) {
	$admin = new Admin();
	$page  = base64_decode(encode($_POST['privacy']));
	$data  = array(
		'status' => 400,
		'message' => 'Can not save page, please check the details'
	);

	$save  = $admin->savePage('privacy_and_policy',$page);
	if ($save) {
		$data  = array(
			'status' => 200,
			'message' => 'Your changes has been successfully saved!'
		);
	}
}
elseif ($action == 'new-lang' && !empty($_POST['lang']) && is_string($_POST['lang'])) {
	$admin    = new Admin();
	$newlang  = strtolower($_POST['lang']);
	$stat     = 400;


	if (len($newlang) > 20) {
		$stat = 401;
	}
	elseif (in_array($newlang, array_keys($langs))) {
		$stat = 402;
	}
	else{
		try {
			$sql      = "ALTER TABLE `pxp_langs` ADD `$newlang` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
			$add_lang =  mysqli_query($mysqli,$sql);
		} 

		catch (Exception $e) {
			
		}

		if (!empty($add_lang)) {
			$def_items = $admin->fetchLanguage();
			$stat      = 200;
			if (!empty($def_items)) {
				foreach ($def_items as $lang_key => $lang_val) {
					$admin::$db->where('lang_key',$lang_key);
					$admin::$db->update(T_LANGS,array($newlang => $def_items[$lang_key]));
				}
			}
		}
	}

	$data['status'] = $stat;
}
elseif ($action == 'new-key' && !empty($_POST['lang_key']) && is_string($_POST['lang_key'])) {
	$admin    = new Admin();
	$lang_key = strtolower($_POST['lang_key']);
	$stat     = 400;

	if (preg_match('/[^a-z0-9_]/', $lang_key)) {
		$stat = 401;
	}
	else if(len($lang_key) > 100){
		$stat = 402;
	}
	else if(in_array($lang_key, array_keys($lang))){
		$stat = 403;
	}
	else{
		$stat = 200;
		$admin::$db->insert(T_LANGS,array('lang_key' => $lang_key));
	}

	$data['status'] = $stat;
}
elseif ($action == 'test_s3_2'){
	

	try {
		require_once('sys/import3p/s3/vendor/autoload.php');
		$s3Client = S3Client::factory(array(
			'version' => 'latest',
			'region' => $config['region_2'],
			'credentials' => array(
				'key' => $config['amazone_s3_key_2'],
				'secret' => $config['amazone_s3_s_key_2']
			)
		));
		$buckets  = $s3Client->listBuckets();
		$result = $s3Client->putBucketCors([
			'Bucket' => $config['bucket_name_2'], // REQUIRED
			'CORSConfiguration' => [ // REQUIRED
				'CORSRules' => [ // REQUIRED
					[
						'AllowedHeaders' => ['Authorization'],
						'AllowedMethods' => ['POST', 'GET', 'PUT'], // REQUIRED
						'AllowedOrigins' => ['*'], // REQUIRED
						'ExposeHeaders' => [],
						'MaxAgeSeconds' => 3000
					],
				],
			]
		]);

		if (!empty($buckets)) {
			if ($s3Client->doesBucketExist($config['bucket_name_2'])) {
				$stat = 200;
				$array          = array(
					'media/img/d-avatar.jpg',
					'media/img/story-bg.jpg',
					'media/img/user-m.png'
				);
				$media = new Media();
				foreach ($array as $key => $value) {
					$upload = $media->uploadToS3($value, false);
				}
			} else {
				$stat = 300;
			}
		} else {
			$stat = 500;
		}
	}
	catch (Exception $e) {
		$stat  = 400;
		$data['message'] = $e->getMessage();
	}

	$data['status'] = $stat;
	
}
elseif ($action == 'test_spaces') {
	try {
            $key        = $config['digital_ocean_key'];
            $secret     = $config['digital_ocean_s_key'];
            $space_name = $config['digital_ocean_space_name'];
            $region     = $config['digital_ocean_region'];
            $space      = new SpacesConnect($key, $secret, $space_name, $region);
            $buckets    = $space->ListSpaces();
            $result     = $space->PutCORS(array(
                'AllowedHeaders' => array(
                    'Authorization'
                ),
                'AllowedMethods' => array(
                    'POST',
                    'GET',
                    'PUT'
                ), // REQUIRED
                'AllowedOrigins' => array(
                    '*'
                ), // REQUIRED
                'ExposeHeaders' => array(),
                'MaxAgeSeconds' => 3000
            ));
            if (!empty($buckets)) {
                if (!empty($space->GetSpaceName())) {
                    $data['status'] = 200;
                    $array          = array(
                        'media/img/d-avatar.jpg',
						'media/img/story-bg.jpg',
						'media/img/user-m.png'
                    );
                    $media = new Media();
					foreach ($array as $key => $value) {
						$upload = $media->uploadToS3($value, false);
					}
                } else {
                    $data['status'] = 300;
                }
            } else {
                $data['status'] = 500;
            }
        }
        catch (Exception $e) {
            $data['status']  = 400;
            $data['message'] = $e->getMessage();
        }
}
elseif ($action == 'test_cloud') {
	if ($config['google_cloud_storage'] == 0 || empty($config['google_cloud_storage_service_account']) || empty($config['google_cloud_storage_bucket_name'])) {
        $data['message'] = 'Please enable Google Cloud Storage and fill all fields.';
    }
    elseif (!file_exists($config['google_cloud_storage_service_account'])) {
        $data['message'] = 'Google Cloud File not found on your server Please upload it to your server.';
    }
    else{


        try {
        	require_once('sys/import3p/google-storage/autoload.php');
            $storage = new StorageClient([
               'keyFile' => json_decode($config['google_cloud_storage_service_account'], true)
            ]);
            // set which bucket to work in
            $bucket = $storage->bucket($config['google_cloud_storage_bucket_name']);
            if ($bucket) {

                $array          = array(
                    'media/img/d-avatar.jpg',
					'media/img/story-bg.jpg',
					'media/img/user-m.png'
                );
                foreach ($array as $key => $value) {
                    $fileContent = file_get_contents($value);

                    // upload/replace file 
                    $storageObject = $bucket->upload(
                                            $fileContent,
                                            ['name' => $value]
                                    );
                }

                $data['status'] = 200;
            }
            else{
                $data['message'] = 'Error in connection';
            }
        } catch (Exception $e) {
            $data['message'] = "".$e;
            // maybe invalid private key ?
            // print $e;
            // exit();
        }
    }
}
elseif ($action == 'test_s3'){
	

	try {
		require_once('sys/import3p/s3/vendor/autoload.php');
		$s3Client = S3Client::factory(array(
			'version' => 'latest',
			'region' => $config['region'],
			'credentials' => array(
				'key' => $config['amazone_s3_key'],
				'secret' => $config['amazone_s3_s_key']
			)
		));
		$buckets  = $s3Client->listBuckets();
		$result = $s3Client->putBucketCors([
			'Bucket' => $config['bucket_name'], // REQUIRED
			'CORSConfiguration' => [ // REQUIRED
				'CORSRules' => [ // REQUIRED
					[
						'AllowedHeaders' => ['Authorization'],
						'AllowedMethods' => ['POST', 'GET', 'PUT'], // REQUIRED
						'AllowedOrigins' => ['*'], // REQUIRED
						'ExposeHeaders' => [],
						'MaxAgeSeconds' => 3000
					],
				],
			]
		]);

		if (!empty($buckets)) {
			if ($s3Client->doesBucketExist($config['bucket_name'])) {
				$stat = 200;
				$array          = array(
					'media/img/d-avatar.jpg',
					'media/img/story-bg.jpg',
					'media/img/user-m.png'
				);
				$media = new Media();
				foreach ($array as $key => $value) {
					$upload = $media->uploadToS3($value, false);
				}
			} else {
				$stat = 300;
			}
		} else {
			$stat = 500;
		}
	}
	catch (Exception $e) {
		$stat  = 400;
		$data['message'] = $e->getMessage();
	}

	$data['status'] = $stat;
	
} elseif ($action == 'test_ftp') {
	try {
		require_once('sys/import3p/ftp/vendor/autoload.php');
		$ftp = new \FtpClient\FtpClient();
		$ftp->connect($config['ftp_host'], false, $config['ftp_port']);
		$login = $ftp->login($config['ftp_username'], $config['ftp_password']);
	    $array = array(
			'media/img/d-avatar.jpg',
			'media/img/story-bg.jpg',
			'media/img/user-m.png'
        );
        $media = new Media();
        foreach ($array as $key => $value) {
            $upload = $media->uploadToFtp($value,false);
        }
		$stat  = 200;
	} catch (Exception $e) {
		$stat  = 400;
		$data['message'] = $e->getMessage();
	}
	$data['status'] = $stat;
}
elseif ($action == 'reset_server_key') {
	$app_key    = sha1(rand(111111111, 999999999)) . '-' . md5(microtime()) . '-' . rand(11111111, 99999999);
    $data_array = array(
        'server_key' => $app_key
    );
    $admin  = new Admin();
	$query  = $admin->updateSettings($data_array);
	$data['status']  = 200;
    $data['app_key'] = $app_key;
}
elseif ($action == 'remove_multi_verification') {
    if (!empty($_POST['ids']) && !empty($_POST['type']) && in_array($_POST['type'], array('verify','delete'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (is_numeric($value) && $value > 0) {
            	$id = $user::secure($value);
                $request = $db->where('id',$id)->getOne(T_VERIFY);
                if ($_POST['type'] == 'delete') {
                	$admin::$db->where('id',$id);
					$admin::$db->delete(T_VERIFY);
                }
                elseif ($_POST['type'] == 'verify') {
                	$admin::$db->where('user_id',$request->user_id);
					$admin::$db->update(T_USERS,array('verified' => 1));

					$admin::$db->where('id',$id);
					$admin::$db->delete(T_VERIFY);
                }
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'delete_v_request_' && !empty($_POST['id'])) {
	$admin    = new Admin();
	$stat = 200;
	$id = $user::secure($_POST['id']);
	$admin::$db->where('id',$id);
	$request = $admin::$db->getOne(T_VERIFY);
	if (!empty($request)) {
		$admin::$db->where('id',$id);
		$admin::$db->delete(T_VERIFY);
	}
	$data['status'] = $stat;
}
elseif ($action == 'accept_v_request_' && !empty($_POST['id'])) {
	$admin    = new Admin();
	$stat = 200;
	$id = $user::secure($_POST['id']);
	$admin::$db->where('id',$id);
	$request = $admin::$db->getOne(T_VERIFY);
	if (!empty($request)) {
		$admin::$db->where('user_id',$request->user_id);
		$admin::$db->update(T_USERS,array('verified' => 1));

		$admin::$db->where('id',$id);
		$admin::$db->delete(T_VERIFY);
	}
	$data['status'] = $stat;
}
elseif ($action == 'delete_bus_request_' && !empty($_POST['id'])) {
	$admin    = new Admin();
	$stat = 200;
	$id = $user::secure($_POST['id']);
	$admin::$db->where('id',$id);
	$request = $admin::$db->getOne(T_BUS_REQUESTS);
	if (!empty($request)) {
		$media = new Media();
		if (!empty($request->photo)) {
			$photo_file = $request->photo;
			if (file_exists($photo_file)) {
		        @unlink(trim($photo_file));
		    }
		    else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
		        $media->deleteFromFTPorS3($photo_file);
		    }
		}

		if (!empty($request->passport)) {
			$photo_file = $request->passport;
			if (file_exists($photo_file)) {
		        @unlink(trim($photo_file));
		    }
		    else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
		        $media->deleteFromFTPorS3($photo_file);
		    }
		}

		

		$admin::$db->where('id',$id);
		$admin::$db->delete(T_BUS_REQUESTS);
	}
	$data['status'] = $stat;
}
elseif ($action == 'accept_bus_request_' && !empty($_POST['id'])) {
	$admin    = new Admin();
	$stat = 200;
	$id = $user::secure($_POST['id']);
	$admin::$db->where('id',$id);
	$request = $admin::$db->getOne(T_BUS_REQUESTS);
	if (!empty($request)) {
		$media = new Media();
		if (!empty($request->photo)) {
			$photo_file = $request->photo;
			if (file_exists($photo_file)) {
		        @unlink(trim($photo_file));
		    }
		    else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
		        $media->deleteFromFTPorS3($photo_file);
		    }
		}

		if (!empty($request->passport)) {
			$photo_file = $request->passport;
			if (file_exists($photo_file)) {
		        @unlink(trim($photo_file));
		    }
		    else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
		        $media->deleteFromFTPorS3($photo_file);
		    }
		}


		$admin::$db->where('user_id',$request->user_id);
		$admin::$db->update(T_USERS,array('business_account' => 1,'verified' => 1,'b_name' => $request->name,'b_email' => $request->email,'b_phone' => $request->phone,'b_site' => $request->site,'b_site_action' => 25));

		$admin::$db->where('id',$id);
		$admin::$db->delete(T_BUS_REQUESTS);
	}
	$data['status'] = $stat;
}
elseif ($action == 'playtube_support' && !empty($_POST['playtube'])) {
	$admin    = new Admin();
	$playtube = $user::secure($_POST['playtube']);
	$playtube_links = $user::secure($_POST['playtube_links']);
	$query  = $admin->updateSettings(array('playtube_url' => $playtube,
                                           'playtube_links' => $playtube_links));
	if ($query == true) {
		$data['status'] = 200;
	}
}
elseif ($action == 'add_ban' && !empty($_POST['value'])) {
	$admin    = new Admin();
	$value = $user::secure($_POST['value']);
	$admin::$db->insert(T_BLACKLIST,array('value' => $value,
                                          'time'  => time()));
	$data['status'] = 200;
}
elseif ($action == 'delete-ban' && !empty($_POST['id'])) {
	$admin    = new Admin();
	$id = $user::secure($_POST['id']);
	$admin::$db->where('id',$id);
	$admin::$db->delete(T_BLACKLIST);
	$data['status'] = 200;
}
elseif ($action == 'delete_receipt') {
	if (!empty($_GET['receipt_id'])) {
        $user_id = $user::secure($_GET['user_id']);
        $id = $user::secure($_GET['receipt_id']);
        $photo_file = $user::secure($_GET['receipt_file']);
        $receipt = $db->where('id',$id)->getOne(T_BANK_TRANSFER,array('*'));
        $notif   = new Notifications();
        $re_data = array(
						'notifier_id' => $me['user_id'],
						'recipient_id' => $receipt->user_id,
						'type' => 'bank_decline',
						'url' => $site_url,
						'time' => time()
					);
		$notif->notify($re_data);
		$media = new Media();
        $db->where('id',$id)->delete(T_BANK_TRANSFER);
        if (file_exists($photo_file)) {
            @unlink(trim($photo_file));
        }
        else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1){
            $media->deleteFromFTPorS3($photo_file);
        }
        $data = array(
            'status' => 200
        );
    }
}
elseif ($action == 'approve_receipt') {
	if (!empty($_GET['receipt_id'])) {
        $id = $user::secure($_GET['receipt_id']);
            $receipt = $db->where('id',$id)->getOne(T_BANK_TRANSFER,array('*'));

            if($receipt){
                $updated = $db->where('id',$id)->update(T_BANK_TRANSFER,array('approved'=>1,'approved_at'=>time()));
                if ($updated === true) {
                    if ($receipt->mode == 'wallet') {
                        $amount = $receipt->price;
                        $result = $db->where('user_id',$receipt->user_id)->update(T_USERS,array('wallet' => $db->inc($amount)));

                        $db->insert(T_TRANSACTIONS,array('user_id' => $receipt->user_id,
                                      'amount' => $amount,
                                      'type' => 'Advertise',
                                      'time' => time()));

                        // if ($result) {
                        //     $create_payment_log = mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENT_TRANSACTIONS . " (`userid`, `kind`, `amount`, `notes`) VALUES ('" . $receipt->user_id . "', 'WALLET', '" . $amount . "', 'bank receipts')");
                        // }
                        $user = new User();
                        $user_data = $user->getUserDataById($receipt->user_id);
                        $notif   = new Notifications();
                        $re_data = array(
										'notifier_id' => $me['user_id'],
										'recipient_id' => $receipt->user_id,
										'type' => 'bank_pro',
										'url' => $site_url.'/settings/wallet/'.$user_data->username,
										'time' => time()
									);
                        $notif->notify($re_data);
                    }
                    elseif ($receipt->mode == 'donate') {
                    	$amount = $receipt->price;
				        $fund_id = $receipt->funding_id;
				        $user = new User();

				        $fund = $user->GetFundingById($fund_id);
				        if (!empty($fund)) {
				        	$admin_com = 0;
				            if (!empty($config['donate_percentage']) && is_numeric($config['donate_percentage']) && $config['donate_percentage'] > 0) {
				                $admin_com = ($config['donate_percentage'] * $amount) / 100;
				                $amount = $amount - $admin_com;
				            }
				        	$db->insert(T_TRANSACTIONS,array('user_id' => $fund->user_id,
				                                      'amount' => $amount,
				                                      'type' => 'donate',
				                                      'time' => time(),
				                                      'admin_com' => $admin_com));

				            $db->where('user_id',$fund->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
				            $db->insert(T_FUNDING_RAISE,array('user_id' => $me['user_id'],
				                                              'funding_id' => $fund_id,
				                                              'amount' => $amount,
				                                              'time' => time()));
				            $notif   = new Notifications();
				            if ($fund->user_id != $me['user_id']) {

				            	$hashed_id = $fund_id;
				            	if (!empty($fund->hashed_id)) {
				            		$hashed_id = $fund->hashed_id;
				            	}

				            	
				                $re_data = array(
				                    'notifier_id' => $me['user_id'],
				                    'recipient_id' => $fund->user_id,
				                    'type' => 'donated',
				                    'url' => $config['site_url'] . "/funding/".$hashed_id,
				                    'time' => time()
				                );
				                try {
				                    $notif->notify($re_data);
				                } catch (Exception $e) {
				                }
				            }
				            $notif   = new Notifications();
					        $re_data = array(
											'notifier_id' => $me['user_id'],
											'recipient_id' => $receipt->user_id,
											'type' => 'bank_pro',
											'url' => $site_url.'/funding/'.$hashed_id,
											'time' => time()
										);

							$notif->notify($re_data);
				        }
                    }elseif ($receipt->mode == 'store') {
                    	$amount = $receipt->price;
						$id = $receipt->funding_id;
						$l = explode(":", $receipt->description);
						$license = trim($l[1]);
			
						$store_image = $db->arrayBuilder()->where('id',$id)->getOne(T_STORE);
						$u = $db->arrayBuilder()->where('user_id',$store_image['user_id'])->getOne(T_USERS);
						$commesion = $amount / 2;
						$wallet = $u['balance'] + $commesion;
						$update = $user->updateStatic($store_image['user_id'],array('balance' => $wallet));
						$db->insert(T_TRANSACTIONS,array(
							'user_id'       => $receipt->user_id,
							'amount'        => $amount,
							'type'          => 'store',
							'item_store_id' => $id,
							'admin_com'     => $commesion,
							'time'          => time(),
							'item_license'  => $license
							)
						);
						$db->where('id',$id)->update(T_STORE, array( 'sells' => $db->inc(1)));
						$notif   = new Notifications();
						$re_data = array(
							'notifier_id' => $me['user_id'],
							'recipient_id' => $store_image['user_id'],
							'type' => 'store_purchase',
							'url' => $config['site_url'] . "/store/".$id,
							'time' => time()
						);
						try {
							$notif->notify($re_data);
						} catch (Exception $e) {
						}
						$data = array(
							'status' => 200
						);

					}
                    else{
                        $update_array = array(
                            'is_pro' => 1,
                            'verified' => 1
                        );
                        $db->where('user_id',$receipt->user_id)->update(T_USERS,$update_array);
                        $db->insert(T_TRANSACTIONS,array('user_id' => $receipt->user_id,
                                      'amount' => $config['pro_price'],
                                      'type' => 'pro_member',
                                      'time' => time()));

                        $notif   = new Notifications();
				        $re_data = array(
										'notifier_id' => $me['user_id'],
										'recipient_id' => $receipt->user_id,
										'type' => 'bank_pro',
										'url' => $site_url.'/upgraded',
										'time' => time()
									);

						$notif->notify($re_data);
                    }
                    $data = array(
                        'status' => 200
                    );
                }
            }
            $data = array(
                'status' => 200,
                'data' => $receipt
            );
    }
}
elseif ($action == 'remove_multi_payment') {
    if (!empty($_POST['ids']) && !empty($_POST['action']) && in_array($_POST['action'], array('paid','decline','delete'))) {
        foreach ($_POST['ids'] as $key => $value) {
            if (!empty($value) && is_numeric($value) && $value > 0) {
            	$request_id = $user::secure($value);
		        if ($_POST['action'] == 'paid') {
		            $request_data = $db->where('id',$request_id)->getOne(T_WITHDRAWAL);
		            if (!empty($request_data) && $request_data->status != 1) {
		                $requiring = $db->where('user_id',$request_data->user_id)->getOne(T_USERS);
		                if (!empty($requiring)) {
		                    $db->where('user_id',$request_data->user_id)->update(T_USERS,array(
		                        'balance' => ($requiring->balance -= $request_data->amount)
		                    ));
		                }
		            }

		            $db->where('id',$request_id)->update(T_WITHDRAWAL,array('status' => 1));
		        }

		        else if ($_POST['action'] == 'decline') {
		            $db->where('id',$request_id)->update(T_WITHDRAWAL,array('status' => 2));
		        }

		        else if ($_POST['action'] == 'delete') {
		            $db->where('id',$request_id)->delete(T_WITHDRAWAL);
		        }
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'withdrawal-requests' && !empty($_POST['id']) && !empty($_POST['action'])) {
    $request = (is_numeric($_POST['id']) && is_numeric($_POST['action']) && in_array($_POST['action'], array(1,2,3)));

    if ($request === true) {
        $request_id = $user::secure($_POST['id']);
        if ($_POST['action'] == 1) {
            $request_data = $db->where('id',$request_id)->getOne(T_WITHDRAWAL);
            if (!empty($request_data) && $request_data->status != 1) {
                $requiring = $db->where('user_id',$request_data->user_id)->getOne(T_USERS);
                if (!empty($requiring)) {
                    $db->where('user_id',$request_data->user_id)->update(T_USERS,array(
                        'balance' => ($requiring->balance -= $request_data->amount)
                    ));
                }
            }

            $db->where('id',$request_id)->update(T_WITHDRAWAL,array('status' => 1));
        }

        else if ($_POST['action'] == 2) {
            $db->where('id',$request_id)->update(T_WITHDRAWAL,array('status' => 2));
        }

        else if ($_POST['action'] == 3) {
            $db->where('id',$request_id)->delete(T_WITHDRAWAL);
        }

        $data['status'] = 200;
    }
}
elseif ($action == 'update_design_setting') {
	$data['status'] = 200;
	$admin    = new Admin();

	if (isset($_FILES['logo']['name'])) {
        $fileInfo = array(
            'file' => $_FILES["logo"]["tmp_name"],
            'name' => $_FILES['logo']['name'],
            'size' => $_FILES["logo"]["size"]
        );
        $media    = $admin->Pxp_UploadLogo($fileInfo);
    }
    if (isset($_FILES['favicon']['name'])) {
        $fileInfo = array(
            'file' => $_FILES["favicon"]["tmp_name"],
            'name' => $_FILES['favicon']['name'],
            'size' => $_FILES["favicon"]["size"]
        );
        $media    = $admin->Pxp_UploadLogo($fileInfo,'fav');
    }
    if (isset($_FILES['light-logo']['name'])) {
        $fileInfo = array(
            'file' => $_FILES["light-logo"]["tmp_name"],
            'name' => $_FILES['light-logo']['name'],
            'size' => $_FILES["light-logo"]["size"]
        );
        $media    = $admin->Pxp_UploadLogo($fileInfo, 'logo-light');
	}
	if(isset($_POST['site_display_mode'])){
		$update = array();
		$update['site_display_mode'] = $_POST['site_display_mode'];
		$query  = $admin->updateSettings($update);
	}

}



elseif ($action == 'add_new_category') {
    $insert_data = array();
    $insert_data['ref'] = 'blog_categories';
    $add = false;
    foreach (LangsNamesFromDB() as $key_) {
        if (!empty($_POST[$key_])) {
            $insert_data[$key_] = Generic::secure($_POST[$key_]);
            $add = true;
        }
    }
    if ($add == true) {
        $id = $db->insert(T_LANGS, $insert_data);
        $db->where('id', $id)->update(T_LANGS, array('lang_key' => $id));
        $data['status'] = 200;
    } else {
        $data['status'] = 400;
        $data['message'] = 'please check details';
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'add_new_store_category') {
    $insert_data = array();
    $insert_data['ref'] = 'store_categories';
    $add = false;
    foreach (LangsNamesFromDB() as $key_) {
        if (!empty($_POST[$key_])) {
            $insert_data[$key_] = Generic::secure($_POST[$key_]);
            $add = true;
        }
    }
    if ($add == true) {
        $id = $db->insert(T_LANGS, $insert_data);
        $db->where('id', $id)->update(T_LANGS, array('lang_key' => $id));
        $data['status'] = 200;
    } else {
        $data['status'] = 400;
        $data['message'] = 'please check details';
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'get_lang_key') {
    $html  = '';
    $langs = GetLangDetails($_GET['id']);
    if (count($langs) > 0) {
        foreach ($langs as $key => $langs) {
            foreach ($langs as $key_ => $lang_vlaue) {
                $context['lang'] = array();
                $is_editale = 0;
                if ($_GET['lang_name'] == $key_) {
                    $is_editale = 1;
                }
                $context['lang'] = array('key_' => $key_, 'is_editale' => $is_editale, 'lang_vlaue' => $lang_vlaue);
                $html .= $admin->loadPage('edit-language/form-list');//Wo_LoadAdminPage('edit-lang/form-list', false);
            }
        }
    } else {
        $html = "<h4>Keyword not found</h4>";
    }
    $data['status'] = 200;
    $data['html']   = $html;
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'update_lang_key') {
    $array_langs = array();
    $lang_key    = Generic::secure($_POST['id_of_key']);
    $langs       = LangsNamesFromDB();
    foreach ($_POST as $key => $value) {
        if (in_array($key, $langs)) {
            $key   = Generic::secure($key);
            $value = Generic::secure($value);
            $query = mysqli_query($sqlConnect, "UPDATE `".T_LANGS."` SET `{$key}` = '{$value}' WHERE `lang_key` = '{$lang_key}'");
            if ($query) {
                $data['status'] = 200;
                $_SESSION['language_changed'] = true;
            }
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'delete_category') {
    header("Content-type: application/json");
    if (!empty($_GET['key']) && in_array($_GET['key'], array_keys(blog_categories()))) {
        $db->where('lang_key',Generic::secure($_GET['key']))->delete(T_LANGS);
        $data['status'] = 200;
    }
    echo json_encode($data);
    exit();
}
elseif ($action == 'delete_store_category') {
    header("Content-type: application/json");
    if (!empty($_GET['key']) && in_array($_GET['key'], array_keys(store_categories()))) {
        $db->where('lang_key',Generic::secure($_GET['key']))->delete(T_LANGS);
        $data['status'] = 200;
    }
    echo json_encode($data);
    exit();
}
elseif ($action == 'remove_multi_store_category') {
    if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
            if (!empty($value) && in_array($value, array_keys(store_categories()))) {
            	$db->where('lang_key',Generic::secure($value))->delete(T_LANGS);
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'remove_multi_store_item') {
    if (!empty($_POST['ids'])) {
        foreach ($_POST['ids'] as $key => $value) {
            if (!empty($value) && is_numeric($value) && $value > 0) {
            	$post_id = Generic::secure($value);

		        $store_image = $db->where('id',$post_id)->get(T_STORE,1);
		        if(isset($store_image[0]) && !empty($store_image[0])){

		            $del = new Media();
		            $del->deleteFromFTPorS3($store_image[0]->full_file);
		            $del->deleteFromFTPorS3($store_image[0]->small_file);

		            if (file_exists($store_image[0]->full_file)) {
		                try {
		                    @unlink($store_image[0]->full_file);	
		                }
		                catch (Exception $e) {
		                }
		            }
		            if (file_exists($store_image[0]->small_file)) {
		                try {
		                    @unlink($store_image[0]->small_file);	
		                }
		                catch (Exception $e) {
		                }
		            }

		            $db->where('id',$post_id)->delete(T_STORE);
		            $data['status'] = 200;

		        }else{
		            $data['status'] = 400;
		        } 
            }
        }
        $data = ['status' => 200];
    }
}
elseif ($action == 'delete_store_item') {
    header("Content-type: application/json");
    if (!empty($_GET['key'])) {
    	$post_id = Generic::secure($_GET['key']);

        $store_image = $db->where('id',$post_id)->get(T_STORE,1);
        if(isset($store_image[0]) && !empty($store_image[0])){

            $del = new Media();
            $del->deleteFromFTPorS3($store_image[0]->full_file);
            $del->deleteFromFTPorS3($store_image[0]->small_file);

            if (file_exists($store_image[0]->full_file)) {
                try {
                    @unlink($store_image[0]->full_file);	
                }
                catch (Exception $e) {
                }
            }
            if (file_exists($store_image[0]->small_file)) {
                try {
                    @unlink($store_image[0]->small_file);	
                }
                catch (Exception $e) {
                }
            }

            $db->where('id',$post_id)->delete(T_STORE);
            $data['status'] = 200;

        }else{
            $data['status'] = 400;
        } 
    }
    echo json_encode($data);
    exit();
}
elseif ($action == 'add_new_blog_article' ) {
    if (!empty($_POST['category']) && !empty($_POST['title']) && !empty($_POST['description'])) {
        $category           = Generic::secure($_POST['category']);
        $title              = Generic::secure($_POST['title']);
        $description        = Generic::secure($_POST['description']);
		$tags               = Generic::secure($_POST['tags']);
        $content            = Generic::secure(base64_decode($_POST['content']));

        $media_file = 'media/upload/photos/d-blog.jpg';
        if (!empty($_FILES['thumbnail']) && file_exists($_FILES['thumbnail']['tmp_name'])) {
            $media = new Media();
            $media->setFile(array(
                'file' => $_FILES['thumbnail']['tmp_name'],
                'name' => $_FILES['thumbnail']['name'],
                'size' => $_FILES['thumbnail']['size'],
                'type' => $_FILES['thumbnail']['type'],
                'allowed' => 'jpeg,jpg,png',
                'crop' => array(),
                'avatar' => false
            ));
            $upload = $media->uploadFile();
            if (!empty($upload)) {
                $media_file = $upload['filename'];
                //$media_file = Media::getMedia($photo);
            }
		}
		$posted = 0;
		if($me['admin'] === 1){
			$posted = 1;
		}
        $data_ = array(
			'user_id'		=> $me['user_id'],
			'posted'		=> $posted,
            'title'         => $title,
            'content'       => $content,
            'description'   => $description,
            'category'      => $category,
            'tags'          => $tags,
            'thumbnail'     => $media_file,
            'created_at'    => time()
		);
        $add   = RegisterNewBlogPost($data_);
        if ($add) {
			$data['status'] = 200;
			$data['message'] = lang('create_article_success');
        }
    } else {
        $data = array(
            'status' => 400,
            'message' => 'Please fill all the required fields'
        );
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'edit_blog_article' && (IS_ADMIN || $config['allow_user_create_blog'] == 'on')) {
    if (!empty($_POST['id']) && !empty($_POST['category']) && !empty($_POST['title']) && !empty($_POST['description'])) {
        $id                 = Generic::secure($_POST['id']);
        $category           = Generic::secure($_POST['category']);
        $title              = Generic::secure($_POST['title']);
        $description        = Generic::secure($_POST['description']);
        $tags               = Generic::secure($_POST['tags']);
        $content            = Generic::secure(base64_decode($_POST['content']));
        $article            = GetArticle($id);
        $remove_prev_img    = false;
        $old_thumb          = $article['thumbnail'];
        $media              = new Media();
        if (!empty($_FILES['thumbnail'])) {
            $media->setFile(array(
                'file' => $_FILES['thumbnail']['tmp_name'],
                'name' => $_FILES['thumbnail']['name'],
                'size' => $_FILES['thumbnail']['size'],
                'type' => $_FILES['thumbnail']['type'],
                'allowed' => 'jpeg,jpg,png',
                'crop' => array(),
                'avatar' => false
            ));
            $upload = $media->uploadFile();
            if (!empty($upload)) {
                $media_file = $upload['filename'];
                //$media_file = Media::getMedia($photo);
                $remove_prev_img    = true;
            }
        }else{
            $media_file = $article['thumbnail'];
        }
        $data_ = array(
            'title'         => $title,
            'content'       => $content,
            'description'   => $description,
            'category'      => $category,
            'tags'          => $tags,
            'thumbnail'     => $media_file
        );
        $add   = $db->where('id',$id)->update(T_BLOG, $data_);
        if ($add) {
            if( $old_thumb !== '' && $remove_prev_img == true ) {
                $cthumbnail = str_replace('_image','_image_c',$old_thumb);
                $media->deleteFromFTPorS3($old_thumb);
                @unlink($old_thumb);
                $media->deleteFromFTPorS3($cthumbnail);
                @unlink($cthumbnail);
            }
            $data['status'] = 200;
        }
    } else {
        $data = array(
            'status' => 400,
            'message' => 'Please fill all the required fields'
        );
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'delete_blog_article') {
    if (!empty($_GET['id'])) {
        $delete = DeleteArticle($_GET['id'], $_GET['thumbnail']);
        if ($delete) {
            $data = array(
                'status' => 200
            );
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}elseif ($action == 'publish_blog_article') {
    if (!empty($_GET['id'])) {
        $delete = PublishArticle($_GET['id']);
        if ($delete) {
            $data = array(
                'status' => 200
            );
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}elseif ($action == 'unpublish_blog_article') {
    if (!empty($_GET['id'])) {
        $delete = UnPublishArticle($_GET['id']);
        if ($delete) {
            $data = array(
                'status' => 200
            );
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'exchange'){
	if ($config['exchange_update'] < time()) {
        $exchange= $admin->curlConnect("https://api.exchangerate.host/latest?base=".$config['currency']."&symbols=".implode(",", array_values($config['currency_array'])));
        if (!empty($exchange) && $exchange['success'] == true && !empty($exchange['rates'])) {
        	$admin->updateSettings(array('exchange' => json_encode($exchange['rates']),
                                         'exchange_update' => (time() + (60 * 60 * 12))));
        }
    }
    $data = array(
        'status' => 200
    );
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'add_new_curreny'){
	if (!empty($_POST['currency']) && !empty($_POST['currency_symbol'])) {
        $config['currency_array'][] = Generic::secure($_POST['currency']);
        $config['currency_symbol_array'][Generic::secure($_POST['currency'])] = Generic::secure($_POST['currency_symbol']);
        $admin->updateSettings(array('currency_array' => json_encode($config['currency_array']),
                                     'currency_symbol_array' => json_encode($config['currency_symbol_array'])));
        $exchange= $admin->curlConnect("https://api.exchangerate.host/latest?base=".$config['currency']."&symbols=".implode(",", array_values($config['currency_array'])));
        if (!empty($exchange) && $exchange['success'] == true && !empty($exchange['rates'])) {
            $admin->updateSettings(array('exchange' => json_encode($exchange['rates']),
                                         'exchange_update' => (time() + (60 * 60 * 12))));
        }
    }
    $data = array(
                'status' => 200
            );
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'select_currency'){
	if (!empty($_POST['currency']) && in_array($_POST['currency'], $config['currency_array'])) {
        $currency = Generic::secure($_POST['currency']);
        $update_array = array('currency' => $currency);
        if (in_array($_POST['currency'], $config['stripe_currency_array'])) {
        	$update_array['stripe_currency'] = $currency;
        }
        if (in_array($_POST['currency'], $config['paypal_currency_array'])) {
        	$update_array['paypal_currency'] = $currency;
        }
        if (in_array($_POST['currency'], $config['2checkout_currency_array'])) {
        	$update_array['checkout_currency'] = $currency;
        }
        if (in_array($_POST['currency'], $config['paystack_currency_array'])) {
        	$update_array['paystack_currency'] = $currency;
        }
        if (in_array($_POST['currency'], $config['iyzipay_currency_array'])) {
        	$update_array['iyzipay_currency'] = $currency;
        }
        $admin->updateSettings($update_array);
        $exchange= $admin->curlConnect("https://api.exchangerate.host/latest?base=".$currency."&symbols=".implode(",", array_values($config['currency_array'])));
        if (!empty($exchange) && $exchange['success'] == true && !empty($exchange['rates'])) {
            $admin->updateSettings(array('exchange' => json_encode($exchange['rates']),
                                         'exchange_update' => (time() + (60 * 60 * 12))));
        }
    }
    $data = array(
                'status' => 200
            );
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'edit_curreny'){
	if (!empty($_POST['currency']) && !empty($_POST['currency_symbol']) && in_array($_POST['currency_id'], array_keys($config['currency_array']))) {
        $config['currency_array'][$_POST['currency_id']] = Generic::secure($_POST['currency']);
        $config['currency_symbol_array'][Generic::secure($_POST['currency'])] = Generic::secure($_POST['currency_symbol']);
        $admin->updateSettings(array('currency_array' => json_encode($config['currency_array']),
                                     'currency_symbol_array' => json_encode($config['currency_symbol_array'])));
        $exchange= $admin->curlConnect("https://api.exchangerate.host/latest?base=".$config['currency']."&symbols=".implode(",", array_values($config['currency_array'])));
        if (!empty($exchange) && $exchange['success'] == true && !empty($exchange['rates'])) {
            $admin->updateSettings(array('exchange' => json_encode($exchange['rates']),
                                         'exchange_update' => (time() + (60 * 60 * 12))));
        }
    }
    $data = array(
                'status' => 200
            );
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
elseif ($action == 'remove__curreny'){
	if (!empty($_POST['currency'])) {
        if (in_array($_POST['currency'], $config['currency_array'])) {
            foreach ($config['currency_array'] as $key => $currency) {
                if ($currency == $_POST['currency']) {
                    if (in_array($currency,array_keys($config['currency_symbol_array']))) {
                        unset($config['currency_symbol_array'][$currency]);
                    }
                    unset($config['currency_array'][$key]);
                }
            }
            if ($config['currency'] == $_POST['currency']) {
                if (!empty($config['currency_array'])) {
                    $admin->updateSettings(array('currency' => reset($config['currency_array'])));
                }
            }
            $admin->updateSettings(array('currency_array' => json_encode($config['currency_array']),
                                         'currency_symbol_array' => json_encode($config['currency_symbol_array'])));
        }
    }
    $data = array(
                'status' => 200
            );
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}