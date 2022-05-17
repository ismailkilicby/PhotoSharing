<?php

if (IS_LOGGED !== true) {
	$data['status'] = 400;
	$data['error'] = "Not logged in";
}

else if ($action == 'add'  && ($config['story_system'] == 'on')) {
	if (!empty($_FILES['file']) && file_exists($_FILES['file']['tmp_name'])) {
		$media   = new Media();
		$getid   = new getID3();
		$story   = new Story();
	    $can_add = $story->setUserById($me['user_id'])->canAddStory();
	
		if ($can_add) {
			$up_size = (!empty($_FILES['file']['size'])) ? $_FILES['file']['size'] : 0;
			$mx_size = $config['max_upload'];

			$metainf = $getid->analyze($_FILES['file']['tmp_name']);

			$file_ex = null;
			$file_tp = null;

			if ($up_size <= $mx_size) {
				if (is_array($metainf) && isset($metainf['fileformat'])) {
					$file_ex = $metainf['fileformat'];
				}

				if (in_array($file_ex, $imgtypes)) {
					$media->setFile(array(
						'file' => $_FILES['file']['tmp_name'],
						'name' => $_FILES['file']['name'],
						'size' => $_FILES['file']['size'],
						'type' => $_FILES['file']['type'],
						'allowed' => 'bmp,gif,jpeg,png,jpg',
					));

					$upload = $media->uploadFile();

					if (!empty($upload) && !empty($upload['filename'])) {

						$sfile   = $upload['filename'];
						$re_data = array(
		                	'user_id' => $me['user_id'],
		                	'time' => time(),
		                	'type' => '1',
		                	'media_file' => $sfile
		                );

		                if (!empty($_POST['caption'])) {
							$text = Generic::cropText($_POST['caption'],500);
							$text = Generic::secure($text);
							$re_data['caption'] = $text;
						}

						$story_id = $story->addStory($re_data);

						if (is_numeric($story_id)) {
							$data = array(
								'status' => 200,
								'message' => lang('post_published'),
							);
						}

						else{
							$data['status']  = 500;
							$data['message'] = lang('unknown_error');
						}
					}
				}

				else if(in_array($file_ex, $vidtypes)) {
					$duration = 0;
					$metainf = $getid->analyze($_FILES['file']['tmp_name']);
					if (!empty($metainf['playtime_seconds'])) {
						$duration = intval($metainf['playtime_seconds'] * 1000);
					}
					$file_tp = 'video';

					$media->setFile(array(
						'file' => $_FILES['file']['tmp_name'],
						'name' => $_FILES['file']['name'],
						'size' => $_FILES['file']['size'],
						'type' => $_FILES['file']['type'],
						'allowed' => 'mp4,mov,3gp,webm',
					));

					$upload = $media->uploadFile();

					if (!empty($upload) && !empty($upload['filename'])) {

						$sfile = $upload['filename'];

						if ($config['ffmpeg_sys'] == 'on') {
							require_once('sys/import3p/ffmpeg-php/vendor/autoload.php');

							try{
								$ffmpeg   = new FFmpeg($config['ffmpeg_binary']);
								$filepath = explode('.', $upload['filename'])[0];
								$ffmpeg->input($upload['filename']);
								$ffmpeg->set('-ss','0');
								$ffmpeg->set('-t',$config['story_duration']);
								$ffmpeg->set('-vcodec','h264');
								$ffmpeg->set('-c:v','libx264');
								$ffmpeg->set('-preset','ultrafast');
								$ffmpeg->set('-acodec','mp3');
								$ffmpeg->set('-hide_banner');

								if ($file_ex != 'mp4') {	
									$ffmpeg->forceFormat('mp4');
								}

								$time  = time();
								$video = $ffmpeg->output("$filepath.$time.mp4")->ready();
								$sfile = "$filepath.$time.mp4";
							}

							catch(Exception $error){
								$data['status']  = 400;
								$data['message'] = lang('unknown_error');
								goto xhr_exit;
							}
						}

						$re_data = array(
		                	'user_id' => $me['user_id'],
		                	'time' => time(),
		                	'type' => '2',
		                	'media_file' => $sfile,
		                	'duration' => $duration
		                );

		                if (!empty($_POST['caption'])) {
							$text = Generic::cropText($_POST['caption'],500);
							$text = Generic::secure($text);
							$re_data['caption'] = $text;
						}

						$story_id = $story->addStory($re_data);

						if (is_numeric($story_id)) {
							$data = array(
								'status' => 200,
								'message' => lang('post_published')
							);
						}

						else{
							$data['status']  = 500;
							$data['message'] = lang('unknown_error');
						}
					}
				}

				else{
					$data['status']  = 400;
					$data['message'] = lang('unknown_error');
				}
			}
			else{
				$mx_size         = $mx_size;
				$data['status']  = 400;
				$data['message'] = str_replace('{{size}}', $mx_size, lang('max_upload_limit'));
			}
		}

		else{
			$data['message'] = lang('story_system_limit');
		}
	}
}

else if($action == 'show' && !empty($_GET['user_id'])) {
	if (is_numeric($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
		$story   = new Story();
		$stories = $story->getUserStory($user_id);
		$us_data = $user::getStaticUser($user_id);

		if (!empty($stories) && is_array($stories) && !empty($us_data)) {
			$context['us_data'] = o2array($us_data);
			$context['stories'] = o2array($stories);
			$context['w'] = (100 / count($stories));

			$data['status'] = 200;
			$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/story');
		}
	}
}

else if($action == 'delete-story' && !empty($_GET['id'])) {
	if (is_numeric($_GET['id'])) {
		$story_id = $_GET['id'];
		$story    = new Story();	
		$data     = array('status' => 304);
		$stories  = $story->setUserById($me['user_id'])->deleteStory($story_id);
	}
}

else if($action == 'rs' && !empty($_POST['stories'])) {
	if (is_array($_POST['stories'])) {
		$stories  = $_POST['stories'];
		$story    = new Story();	
		$data     = array('status' => 304);
		$insert   = $story->setUserById($me['user_id'])->regStoryViews($stories);
		if (!empty($insert)) {
			$data = array(
				'status' => 200
			); 
		}
	}
}

xhr_exit: