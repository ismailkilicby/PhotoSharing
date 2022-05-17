<?php
if (IS_LOGGED !== true) {
	$data['status'] = 400;
	$data['error'] = "Not logged in";
	goto exit_xhr;
}


if ($action == 'create'){
    if ($config['live_video'] == 1 && ($config['who_use_live'] == 'all' || ($config['who_use_live'] == 'admin' && IS_ADMIN) || ($config['who_use_live'] == 'pro' && $me['is_pro'] > 0))) {
    }
    else{
        $data['message'] = lang('please_check_details');
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
	if (empty($_POST['stream_name'])) {
		$data['message'] = lang('please_check_details');
	}
	else{
        $video_id        = PT_GenerateKey(15, 15);
        $check_for_video = $db->where('post_key', $video_id)->getValue(T_POSTS, 'count(*)');
        if ($check_for_video > 0) {
            $video_id = PT_GenerateKey(15, 15);
        }
		$post_id = $db->insert(T_POSTS,array('user_id' => $me['user_id'],
                                             'type' => 'live',
                                             'description' => 'live video '.$me['username'],
                                             'stream_name' => Generic::secure($_POST['stream_name']),
                                             'registered' => date('Y') . '/' . intval(date('m')),
                                             'post_key' => $video_id,
                                             'time' => time()));
        PT_RunInBackground(array('status' => 200,'post_id' => $post_id));
        if ($config['agora_live_video'] == 1 && !empty($config['agora_app_id']) && !empty($config['agora_customer_id']) && !empty($config['agora_customer_certificate']) && $config['live_video_save'] == 1) {
            if ($config['amazone_s3_2'] == 1 && !empty($config['bucket_name_2']) && !empty($config['amazone_s3_key_2']) && !empty($config['amazone_s3_s_key_2']) && !empty($config['region_2'])) {
                $region_array = array(
                    'us-east-1' => 0,
                    'us-east-2' => 1,
                    'us-west-1' => 2,
                    'us-west-2' => 3,
                    'eu-west-1' => 4,
                    'eu-west-2' => 5,
                    'eu-west-3' => 6,
                    'eu-central-1' => 7,
                    'ap-southeast-1' => 8,
                    'ap-southeast-2' => 9,
                    'ap-northeast-1' => 10,
                    'ap-northeast-2' => 11,
                    'sa-east-1' => 12,
                    'ca-central-1' => 13,
                    'ap-south-1' => 14,
                    'cn-north-1' => 15,
                    'us-gov-west-1' => 17);
                if (in_array(strtolower($config['region_2']),array_keys($region_array) )) {
                    StartCloudRecording(1,
                                        $region_array[strtolower($config['region_2'])],
                                        $config['bucket_name_2'],
                                        $config['amazone_s3_key_2'],
                                        $config['amazone_s3_s_key_2'],
                                        $_POST['stream_name'],
                                        explode('_', $_POST['stream_name'])[1],
                                        $post_id);
                }
            }
        }
        pt_push_channel_notifiations($post_id,'started_live_video');
        $data['status'] = 200;
        $data['post_id'] = $post_id;
	}
	header("Content-type: application/json");
    echo json_encode($data);
    exit();
}else if ($action == 'check_comments'){

	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && $_POST['post_id'] > 0) {
		$post_id = Generic::secure($_POST['post_id']);
        $post_data = $context['video_data'] = $db->where('post_id',$post_id)->getOne(T_POSTS);
        $_user = new User();

		if (!empty($post_data)) {
            if ($post_data->live_ended == 0) {
                //if ($_POST['page'] == 'watch') {
                    $user_comment = $db->where('post_id',$post_id)->where('user_id',$me['user_id'])->getOne(T_POST_COMMENTS);
                    if (!empty($user_comment)) {
                        $db->where('id',$user_comment->id,'>');
                    }
                //}
                if (!empty($_POST['ids'])) {
                    $ids = array();
                    foreach ($_POST['ids'] as $key => $one_id) {
                        $ids[] = Generic::secure($one_id);
                    }
                    $db->where('id',$ids,'NOT IN')->where('id',end($ids),'>');
                }
                //if ($_POST['page'] == 'watch') {
                    $db->where('user_id',$me['user_id'],'!=');
                //}
				$comments = $db->where('post_id',$post_id)->where('text','','!=')->get(T_POST_COMMENTS);
				$html = '';
                $count = 0;
				foreach ($comments as $key => $get_comment) {
					if (!empty($get_comment->text)) {
                        $user_data   = $_user->getUserDataById($get_comment->user_id);
                        $context['is_comment_owner'] = false;
                        $context['is_verified']      = ($user_data->verified == 1) ? true : false;
                        $context['video_owner']      = false;

                        if ($user->id == $get_comment->user_id) {
                            $context['is_comment_owner'] = true;
                        }

                        if ($video_data->user_id == $user->id) {
                            $context['video_owner'] = true;
                        }
                        $get_comment->text = PT_Duration($get_comment->text);

                        $html     .= $pixelphoto->PX_LoadPage('main/templates/live_comment', array(
                            'ID' => $get_comment->id,
                            'TEXT' => PT_Markup($get_comment->text),
                            'TIME' => time2str($get_comment->time),
                            'USER_DATA' => $user_data,
                            'LIKES' => 0,
                            'DIS_LIKES' => 0,
                            'LIKED' => '',
                            'DIS_LIKED' => '',
                            'LIKED_ATTR' => '',
                            'COMM_REPLIES' => '',
                            'VID_ID' => $get_comment->id
                        ));
						$count = $count + 1;
						if ($count == 4) {
	                      break;
	                    }
					}
				}

                $word = lang('Offline');
                if (!empty($post_data->live_time) && $post_data->live_time >= (time() - 10)) {
                    //$db->where('post_id',$post_id)->where('time',time()-6,'<')->update(T_LIVE_SUB,array('is_watching' => 0));
                    $word = lang('Live');
                    $count = $db->where('post_id',$post_id)->where('time',time()-6,'>=')->getValue(T_LIVE_SUB,'COUNT(*)');

                    if ($me['user_id'] == $post_data->user_id) {
                        $joined_users = $db->where('post_id',$post_id)->where('time',time()-6,'>=')->where('is_watching',0)->get(T_LIVE_SUB);
                        $joined_ids = array();
                        if (!empty($joined_users)) {
                            foreach ($joined_users as $key => $value) {
                                $joined_ids[] = $value->user_id;
                                
                                $user_data   = $_user->getUserDataById($value->user_id);
                                $context['is_verified']      = ($user_data->verified == 1) ? true : false;
                                $html     .= $pixelphoto->PX_LoadPage('main/templates/live_comment', array(
                                    'ID' => '',
                                    'TEXT' => lang('joined live video'),
                                    'TIME' => '',
                                    'USER_DATA' => $user_data,
                                    'LIKES' => 0,
                                    'DIS_LIKES' => 0,
                                    'LIKED' => '',
                                    'DIS_LIKED' => '',
                                    'LIKED_ATTR' => '',
                                    'COMM_REPLIES' => '',
                                    'VID_ID' => ''
                                ));
                            }
                            if (!empty($joined_ids)) {
                                $db->where('post_id',$post_id)->where('user_id',$joined_ids,'IN')->update(T_LIVE_SUB,array('is_watching' => 1));
                            }
                        }

                        $left_users = $db->where('post_id',$post_id)->where('time',time()-6,'<')->where('is_watching',1)->get(T_LIVE_SUB);
                        $left_ids = array();
                        if (!empty($left_users)) {
                            foreach ($left_users as $key => $value) {
                                $left_ids[] = $value->user_id;
                                $user_data   = $_user->getUserDataById($value->user_id);
                                $context['is_verified']      = ($user_data->verified == 1) ? true : false;
                                $html     .= $pixelphoto->PX_LoadPage('main/templates/live_comment', array(
                                    'ID' => '',
                                    'TEXT' => lang('left live video'),
                                    'TIME' => '',
                                    'USER_DATA' => $user_data,
                                    'LIKES' => 0,
                                    'DIS_LIKES' => 0,
                                    'LIKED' => '',
                                    'DIS_LIKED' => '',
                                    'LIKED_ATTR' => '',
                                    'COMM_REPLIES' => '',
                                    'VID_ID' => ''
                                ));
                            }
                            if (!empty($left_ids)) {
                                $db->where('post_id',$post_id)->where('user_id',$left_ids,'IN')->delete(T_LIVE_SUB);
                            }
                        }
                    }
                }
                $still_live = 'offline';
                if (!empty($post_data) && $post_data->live_time >= (time() - 10)){
                    $still_live = 'live';
                }
                $data = array(
                    'status' => 200,
                    'html' => $html,
                    'count' => $count,
                    'word' => $word,
                    'still_live' => $still_live
                );

                if ($me['user_id'] == $post_data->user_id) {
                    if ($_POST['page'] == 'live') {
                        $time = time();
                        $db->where('post_id',$post_id)->update(T_POSTS,array('live_time' => $time));
                    }
                }
                else{
                    if (!empty($post_data->live_time) && $post_data->live_time >= (time() - 10) && $_POST['page'] == 'watch') {
                        $is_watching = $db->where('user_id',$me['user_id'])->where('post_id',$post_id)->getValue(T_LIVE_SUB,'COUNT(*)');
                        if ($is_watching > 0) {
                            $db->where('user_id',$me['user_id'])->where('post_id',$post_id)->update(T_LIVE_SUB,array('time' => time()));
                        }
                        else{
                            $db->insert(T_LIVE_SUB,array('user_id' => $me['user_id'],
                                                         'post_id' => $post_id,
                                                         'time' => time(),
                                                         'is_watching' => 0));
                        }
                    }
                }
            }
            else{
                $data['message'] = lang('please_check_details');
            }
            
		}
		else{
			$data['message'] = lang('please_check_details');
            $data['removed'] = 'yes';
		}
	}
	else{
		$data['message'] = lang('please_check_details');
	}
	header("Content-type: application/json");
    echo json_encode($data);
    exit();
}else if ($action == 'delete'){
    if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && $_POST['post_id'] > 0) {
        $db->where('post_id',Generic::secure($_POST['post_id']))->where('user_id',$me['user_id'])->update(T_POSTS,array('live_ended' => 1));
        if ($config['live_video_save'] == 0) {
            Stream_DeleteVideo(Generic::secure($_POST['post_id']));
        }
        else{
            if ($config['agora_live_video'] == 1 && !empty($config['agora_app_id']) && !empty($config['agora_customer_id']) && !empty($config['agora_customer_certificate']) && $config['live_video_save'] == 1) {
                $post = $db->where('post_id',Generic::secure($_POST['post_id']))->getOne(T_POSTS);
                if (!empty($post)) {
                    StopCloudRecording(array(
                                            'resourceId' => $post->agora_resource_id,
                                            'sid' => $post->agora_sid,
                                            'cname' => $post->stream_name,
                                            'post_id' => $post->post_id,
                                            'uid' => explode('_', $post->stream_name)[1]
                                        )
                                    );
                }
            }
            if ($config['agora_live_video'] == 1 && $config['amazone_s3_2'] != 1) {
                try {
                    Stream_DeleteVideo(Generic::secure($_POST['post_id']));
                } catch (Exception $e) {
                    
                }
            }
        }
    }
}else if ($action == 'create_thumb'){//done
    if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && $_POST['post_id'] > 0 && !empty($_FILES['thumb'])) {
        $is_post = $db->where('post_id',Generic::secure($_POST['post_id']))->where('user_id',$me['user_id'])->getValue(T_POSTS,'COUNT(*)');
        if ($is_post > 0) {

            $media = new Media();
            $media->setFile(array(
                'file' => $_FILES['thumb']['tmp_name'],
                'name' => $_FILES['thumb']['name'],
                'size' => $_FILES['thumb']['size'],
                'type' => $_FILES['thumb']['type'],
                'allowed' => 'jpeg,jpg,png',
                'crop' => array(
                    'width' => 1076,
                    'height' => 604
                ),
                'avatar' => false
            ));
    
            $upload = $media->uploadFile();

            if (!empty($upload['filename'])) {
                $thumb = $upload['filename'];
                if (!empty($thumb)) {
                    $db->where('post_id',Generic::secure($_POST['post_id']))->where('user_id',$me['user_id'])->update(T_POSTS,array('thumbnail' => $thumb));
                    $data['status'] = 200;
                    header("Content-type: application/json");
                    echo json_encode($data);
                    exit();
                }
            }
        }
    }
}


exit_xhr: