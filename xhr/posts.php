<?php 

if ($action == 'new-image' && IS_LOGGED && ($config['upload_images'] == 'on')) {
	$data['status'] = 200;
	$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/upload-image');
}

else if ($action == 'new-video' && IS_LOGGED && ($config['upload_videos'] == 'on')) {
	$context['video_type'] = '';
	if ($config['upload_360_videos'] == 'on' && !empty($_GET['video_type']) && $_GET['video_type'] == 360) {
		$context['video_type'] = '360';
	}
	if ($config['ffmpeg_sys'] == 'on') {
		$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/ffmpeg-upload-video');
		$data['status'] = 200;
	}
	else{
		$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/upload-video');
		$data['status'] = 200;
	}
}
else if ($action == 'new-reels' && IS_LOGGED && ($config['upload_videos'] == 'on')) {

	$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/upload-reels');
	$data['status'] = 200;
}
else if ($action == 'new-embed' && IS_LOGGED && ($config['import_videos'] == 'on')) {
	if (!empty($config['playtube_url'])) {
		$re = str_replace("/", "\/", $config['playtube_url'].'/watch/');
		$playtube_support = "/({$re})([^\/]*)/";
		$context['playtube_support'] = $playtube_support;
		$context['playtube_link'] = $config['playtube_url'];
	}
	$data['status'] = 200;
	$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/embed-video');
}

else if ($action == 'new-gif' && IS_LOGGED && ($config['import_images'] == 'on')) {
	$data['status'] = 200;
	$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/import-gifs');
}

else if ($action == 'new-story' && IS_LOGGED && ($config['story_system'] == 'on')) {
	$story          = new Story();
	$data['status'] = 400;
	$can_addstory   = $story->setUserById($me['user_id'])->canAddStory();
	
	if ($can_addstory) {
		$data['status']  = 200;
		$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/create-story');
	}
	else{
		$data['message'] = lang('story_system_limit');
	}
}

else if ($action == 'upload-post-images' && IS_LOGGED && ($config['upload_images'] == 'on')) {
	$ext = '';
	$error = '';
	if (isset($_FILES['filter_video']) && !empty($_FILES['filter_video']['tmp_name']) && !empty($_FILES['filter_video_thumb']) && empty($_FILES['images']) && empty($_FILES['filter_image'])) {
		$media  = new Media();
		$posts  = new Posts();

		$media->setFile(array(
			'file' => $_FILES['filter_video']['tmp_name'],
			'name' => $_FILES['filter_video']['name'],
			'size' => $_FILES['filter_video']['size'],
			'type' => $_FILES['filter_video']['type'],
			'allowed' => 'mp4,mov,3gp,webm',
		));
		$video = $media->uploadFile();
		$media->setFile(array(
			'file' => $_FILES['filter_video_thumb']['tmp_name'],
			'name' => $_FILES['filter_video_thumb']['name'],
			'size' => $_FILES['filter_video_thumb']['size'],
			'type' => $_FILES['filter_video_thumb']['type'],
			'allowed' => 'jpeg,jpg,png',
			'crop' => array(
				'width' => '600',
				'height' => '400',
			)
		));

		$image = $media->uploadFile();
		if (!empty($video['filename']) && !empty($image['filename'])) {
            
            $re_data = array(
            	'user_id' => $me['user_id'],
            	'time' => time(),
            	'type' => 'video',
            );

            if (!empty($_POST['caption'])) {
				$text = Generic::cropText($_POST['caption'],500);
				$re_data['description'] = $text;
			}

			$post_id = $posts->insertPost($re_data);

			if (is_numeric($post_id)) {
				$re_data = array(
					'post_id' => $post_id,
					'file' => $video['filename'],
					'extra' => $image['cname'],
				);

				$posts->setPostId($post_id);
				$posts->insertMedia($re_data);
	
				$post_data = o2array($posts->postData());

				$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-video');	
				$data['status']  = 200;
				$data['message'] = lang('post_published');

			}
			else{
				$data['status']  = 500;
				$data['message'] = lang('unknown_error');
			}
		}
		else{
			$data['status']  = 400;
			$data['message'] = $video['error'];
		}
	}
	else{
		if (!empty($_FILES)) {
			if (!empty($_FILES['images']) && empty($_FILES['filter_image'])) {
				$images  = px_multiple_files($_FILES['images']);
			}
			elseif (empty($_FILES['images']) && !empty($_FILES['filter_image'])) {
				$images  = $_FILES['filter_image'];
			}
			else{
				$images  = px_multiple_files($_FILES['images']);
			}
			if ($config['private_photos'] == 'on' && !empty($_POST['photo_price']) && is_numeric($_POST['photo_price']) && $_POST['photo_price'] > 0) {
				$context['ftp_upload'] = $config['ftp_upload'];
				$context['amazone_s3'] = $config['amazone_s3'];
				$context['google_cloud_storage'] = $config['google_cloud_storage'];
				$context['digital_ocean'] = $config['digital_ocean'];
				Generic::$config['ftp_upload'] = 0;
				Generic::$config['amazone_s3'] = 0;
				Generic::$config['google_cloud_storage'] = 0;
				Generic::$config['digital_ocean'] = 0;
			}
			
			$media   = new Media();
			$posts   = new Posts();
			$notif   = new Notifications();
			$uploads = array();
			$attach  = explode(',', $_POST['attach']);
			$up_size = 0;
			$mx_size = $config['max_upload'];
			if (!empty($_FILES['filter_image'])) {
				$up_size  = $images['size'];
			}
			else{
				foreach ($images as $image){
					$up_size += $image['size'];
				}
			}
			if ($up_size <= $mx_size) {
				if (!empty($_FILES['filter_image'])) {
					if ($media->isImage($images['tmp_name'])) {
						$file_info = array(
							'file' => $images['tmp_name'],
							'name' => $images['name'],
							'size' => $images['size'],
							'type' => $images['type'],
							'allowed' => 'jpeg,jpg,png,webp,gif',
						);
						$media->setFile($file_info);
						$upload = $media->uploadFile();
						if (!empty($upload['filename'])) {
							
							$uploads[] = $upload; 
						}
					}
				}
				else{
					if (count($images) <= 10) {
						
						foreach ($images as $key => $image) {
							if($attach[$key] !== ''){
								if($image['type'] == 'image/webp'){
									$ext = 'image/webp';
								}
								if ($media->isImage($image['tmp_name'])) {
									$file_info = array(
										'file' => $image['tmp_name'],
										'name' => $image['name'],
										'size' => $image['size'],
										'type' => $image['type'],
										'allowed' => 'jpeg,jpg,png,gif',
									);
									
									if (count($images) > 1) {
										// $file_info['crop'] = array(
										// 	'width' => 600,
										// 	'height' => 600
										// );
									}
	
									$media->setFile($file_info);
									if ($config['private_photos'] == 'on' && !empty($_POST['photo_price']) && is_numeric($_POST['photo_price']) && $_POST['photo_price'] > 0) {
										Generic::$config['ftp_upload'] = 0;
										Generic::$config['amazone_s3'] = 0;
										Generic::$config['google_cloud_storage'] = 0;
										Generic::$config['digital_ocean'] = 0;
									}

									$upload = $media->uploadFile();
	
									if (!empty($upload['filename'])) {
										if ($config['private_photos'] == 'on' && !empty($_POST['photo_price']) && is_numeric($_POST['photo_price']) && $_POST['photo_price'] > 0) {
											$blured_file = BlurUploadedImage($upload['filename']);
											if (!empty($blured_file)) {
												$upload['blured_file'] = $blured_file;
											}
										}
										$uploads[] = $upload; 
										
									}else{
										$error = $upload['error'];
									}
								}
							}
						}
					}
				}

				if (!empty($uploads)) {
					$re_data = array(
						'user_id' => $me['user_id'],
						'time' => time(),
						'type' => 'image',
					);

					if (!empty($_POST['caption'])) {
						$text = Generic::cropText($_POST['caption'],$config['caption_len']);
						$re_data['description'] = $text;
					}
					if ($config['private_photos'] == 'on' && !empty($_POST['photo_price']) && is_numeric($_POST['photo_price']) && $_POST['photo_price'] > 0) {
						$re_data['price'] = Generic::secure($_POST['photo_price']);
					}

					$post_id = $posts->insertPost($re_data);
					if (is_numeric($post_id)) {
						foreach ($uploads as $key => $file) {
							$re_data = array(
								'post_id' => $post_id,
								'file' => $file['filename'],
								'extra' => $file['name']
							);
							if (!empty($file['blured_file'])) {
								$re_data['blured_file'] = $file['blured_file'];
							}
							$posts->insertMedia($re_data);
						}


						$posts->setPostId($post_id);
						$post_data = o2array($posts->postData());

						$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-image');
						$data['status']  = 200;
						$data['message'] = lang('post_published');

						#Notify mentioned users
						$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));
					}
				}
				else{
					$data['status']  = 400;
					if( !empty($error)){
						$data['message'] = $error;
					} else if( empty($ext) ){
						$data['message'] = lang('unknown_error');
					}else{
						$data['message'] = lang('file_is_not_support');
					}
					
				}
			}
			else{
				$mx_size         = $mx_size;
				$data['status']  = 400;
				$data['message'] = str_replace('{{size}}', $mx_size, lang('max_upload_limit'));
			}
		}

	}

	
}

else if($action == 'ffmpeg-video-upload' && IS_LOGGED && ($config['upload_videos'] == 'on')) {
	require_once('sys/import3p/ffmpeg-php/vendor/autoload.php');
	if ($config['ffmpeg_sys'] == 'on' && !empty($_FILES['video']) && file_exists($_FILES['video']['tmp_name'])){
	    if(!file_exists($config['ffmpeg_binary'])){
            header("Content-type: application/json");
            $data['status']  = 400;
            $data['message'] = 'FFMPEG Executable file not found';
            $data['ffmpeg_binary']  = $config['ffmpeg_binary'];
            $data['exist']  = file_exists($config['ffmpeg_binary']);
            echo json_encode($data, JSON_PRETTY_PRINT);
            exit();
        }
		$ffmpeg  = new FFmpeg($config['ffmpeg_binary']);
		$media   = new Media();
		$posts   = new Posts();
		$notif   = new Notifications();
		$up_size = (!empty($_FILES['video']['size'])) ? $_FILES['video']['size'] : 0;
		$mx_size = $config['max_upload'];

		if ($up_size <= $mx_size) {
			$media->setFile(array(
				'file' => $_FILES['video']['tmp_name'],
				'name' => $_FILES['video']['name'],
				'size' => $_FILES['video']['size'],
				'type' => $_FILES['video']['type'],
				'allowed' => 'mp4,mov,3gp,webm',
			));
			//we remove false "$media->uploadFile(0, false);" to fix Upload delete etc isn't working with amazon s3
			$upload = $media->uploadFile(0, false);
			// print_r($upload);

			if (!empty($upload)) {
				try{
					$filepath = explode('.', $upload['filename'])[0];
					$filext   = explode('.', $upload['filename'])[1];
					// echo $filepath;
					// echo "<br>";
					// echo $filext;

                    $ffmpeg->input($upload['filename']);
                    $ffmpeg->set('-ss', '0');
                    if((int)$config['max_video_duration'] > 0) {
                        $ffmpeg->set('-t', (int)$config['max_video_duration']);
                    }
                    $ffmpeg->set('-vcodec', 'h264');
                    $ffmpeg->set('-c:v', 'libx264');
                    $ffmpeg->set('-preset', 'ultrafast');
                    $ffmpeg->set('-acodec', 'mp3');
                    $ffmpeg->set('-hide_banner');
                    $ffmpeg->forceFormat('mp4');

					$time  = time();
					$video = $ffmpeg->output("$filepath.final.mp4")->ready();
					// print_r($video);die('1');
					$video = "$filepath.final.mp4";
					
					$media->initDir('photos');

	                $dir      = "media/upload/photos/" . date('Y') . '/' . date('m');
	                $hash     = sha1(time() + time() - rand(9999,9999));

	                $thumb    = "$dir/$hash.video_thumb.jpeg";
	                $full_dir = $root;

	                $input_path = $upload['filename'];
	                $output_path = $thumb;

	                #Generete thumb

	                $_ffmpeg = new FFmpeg($config['ffmpeg_binary']);
	                $_ffmpeg->input($upload['filename']);
					$_ffmpeg->set('-ss','2');
					$_ffmpeg->set('-vframes','1');
					$_ffmpeg->set('-f','mjpeg');
	                $output_thumb = $_ffmpeg->output("$output_path")->ready();

	                $re_data = array(
	                	'user_id' => $me['user_id'],
	                	'time' => time(),
	                	'type' => 'video',
	                );

	                if (!empty($_POST['caption'])) {
						$text = Generic::cropText($_POST['caption'],$config['caption_len']);
						$re_data['description'] = $text;
					}
					if ($config['upload_360_videos'] == 'on' && !empty($_POST['video_type']) && $_POST['video_type'] == 360) {
						$re_data['video_type'] = 360;
					}
					if ($config['private_videos'] == 'on' && !empty($_POST['video_price']) && is_numeric($_POST['video_price']) && $_POST['video_price'] > 0) {
						$re_data['price'] = Generic::secure($_POST['video_price']);
					}

					$post_id = $posts->insertPost($re_data);

					if (is_numeric($post_id)) {
						$re_data = array(
							'post_id' => $post_id,
							'file' => $video,
							'extra' => $thumb,
						);

						$media = new Media;
						if ($config['ftp_upload'] == 1) {
							$media->uploadToFtp($video);
							$media->uploadToFtp($thumb);
						} else if ($config['amazone_s3'] == 1) {
							$media->uploadToS3($video);
							$media->uploadToS3($thumb);
						} else if ($config['google_cloud_storage'] == 1) {
							$media->uploadToGoogleCloud($video);
							$media->uploadToGoogleCloud($thumb);
						}

						$posts->setPostId($post_id);
						$posts->insertMedia($re_data);

						
						$post_data = o2array($posts->postData());
						if ($config['upload_360_videos'] == 'on' && !empty($_POST['video_type']) && $_POST['video_type'] == 360) {
							$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/video_360');	
						}
						else{
							$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-video');	
						}

						
						$data['status']  = 200;
						$data['message'] = lang('post_published');

						#Notify mentioned users
						$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));

						@unlink($upload['filename']);
						$media->deleteFromFTPorS3($upload['filename']);
					}
					else{
						$data['status']  = 500;
						$data['message'] = lang('unknown_error');
					}

				}
				catch(Exception $error){
					$data['status']  =  400;
					$data['message'] = lang('unknown_error');
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
}

else if($action == 'upload-post-video' && IS_LOGGED){
	if (!empty($_FILES['video']) && !empty($_FILES['thumb'])){
		$media  = new Media();
		$posts  = new Posts();

		$media->setFile(array(
			'file' => $_FILES['video']['tmp_name'],
			'name' => $_FILES['video']['name'],
			'size' => $_FILES['video']['size'],
			'type' => $_FILES['video']['type'],
			'allowed' => 'mp4,mov,3gp,webm',
		));

		$video = $media->uploadFile();

		$media->setFile(array(
			'file' => $_FILES['thumb']['tmp_name'],
			'name' => $_FILES['thumb']['name'],
			'size' => $_FILES['thumb']['size'],
			'type' => $_FILES['thumb']['type'],
			'allowed' => 'jpeg,jpg,png',
			'crop' => array(
				'width' => '600',
				'height' => '400',
			)
		));

		$image = $media->uploadFile();
		if (!empty($video['filename']) && !empty($image['filename'])) {
            
            $re_data = array(
            	'user_id' => $me['user_id'],
            	'time' => time(),
            	'type' => 'video',
            );

            if (!empty($_POST['caption'])) {
				$text = Generic::cropText($_POST['caption'],500);
				$re_data['description'] = $text;
			}
			if ($config['upload_360_videos'] == 'on' && !empty($_POST['video_type']) && $_POST['video_type'] == 360) {
				$re_data['video_type'] = 360;
			}
			if ($config['private_videos'] == 'on' && !empty($_POST['video_price']) && is_numeric($_POST['video_price']) && $_POST['video_price'] > 0) {
				$re_data['price'] = Generic::secure($_POST['video_price']);
			}

			$post_id = $posts->insertPost($re_data);

			if (is_numeric($post_id)) {
				$re_data = array(
					'post_id' => $post_id,
					'file' => $video['filename'],
					'extra' => $image['cname'],
				);

				$posts->setPostId($post_id);
				$posts->insertMedia($re_data);
	
				$post_data = o2array($posts->postData());

				if ($config['upload_360_videos'] == 'on' && !empty($_POST['video_type']) && $_POST['video_type'] == 360) {
					$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/video_360');	
				}
				else{
					$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-video');	
				}
				$data['status']  = 200;
				$data['message'] = lang('post_published');

			}
			else{
				$data['status']  = 500;
				$data['message'] = lang('unknown_error');
			}
		}
		else{
			$data['status']  = 400;
			$data['message'] = $video['error'];
		}
	}
}
else if($action == 'upload-post-reels' && IS_LOGGED){

	if (!empty($_FILES['video']) && !empty($_FILES['thumb'])){
		if ($config['ffmpeg_sys'] == 'on' && !empty($_FILES['video']) && file_exists($_FILES['video']['tmp_name'])){
			require_once('sys/import3p/ffmpeg-php/vendor/autoload.php');
			
		    if(!file_exists($config['ffmpeg_binary'])){
	            header("Content-type: application/json");
	            $data['status']  = 400;
	            $data['message'] = 'FFMPEG Executable file not found';
	            $data['ffmpeg_binary']  = $config['ffmpeg_binary'];
	            $data['exist']  = file_exists($config['ffmpeg_binary']);
	            echo json_encode($data, JSON_PRETTY_PRINT);
	            exit();
	        }
			$ffmpeg  = new FFmpeg($config['ffmpeg_binary']);
			$media   = new Media();
			$posts   = new Posts();
			$notif   = new Notifications();
			$up_size = (!empty($_FILES['video']['size'])) ? $_FILES['video']['size'] : 0;
			$mx_size = $config['max_upload'];

			if ($up_size <= $mx_size) {
				$media->setFile(array(
					'file' => $_FILES['video']['tmp_name'],
					'name' => $_FILES['video']['name'],
					'size' => $_FILES['video']['size'],
					'type' => $_FILES['video']['type'],
					'allowed' => 'mp4,mov,3gp,webm',
				));
				//we remove false "$media->uploadFile(0, false);" to fix Upload delete etc isn't working with amazon s3
				$upload = $media->uploadFile(0, false);
						// print_r($upload);

				if (!empty($upload)) {
					try{
						$filepath = explode('.', $upload['filename'])[0];
						$filext   = explode('.', $upload['filename'])[1];
						// print_r($filepath);
	                    $ffmpeg->input($upload['filename']);
	                    $ffmpeg->set('-ss', '0');
	                    if((int)$config['max_video_duration'] > 0) {
	                        //$ffmpeg->set('-t', (int)$config['max_video_duration']);
	                    }
	                    $ffmpeg->set('-vcodec', 'h264');
	                    //$ffmpeg->set('-c:v', 'libx264');
	                    //$ffmpeg->set('-crf', '23');
	                   // $ffmpeg->set('-profile:v', 'main');
	                    $ffmpeg->set('-level', '3.0');
	                    //$ffmpeg->set('-preset', 'ultrafast');
	                    //$ffmpeg->set('-acodec', 'aac');
	                   //$ffmpeg->set('-ac', '2');
	                   // $ffmpeg->set('-b:a', '128k');
	                   // $ffmpeg->set('-pix_fmt', 'yuv420p');
	                   // $ffmpeg->set('-movflags', 'faststart');
	                    //$ffmpeg->set('-hide_banner');
	                    $ffmpeg->forceFormat('mp4');
						 // -c:v libx264 -crf 23 -profile:v baseline -level 3.0 -pix_fmt yuv420p \
						 //  -c:a aac -ac 2 -b:a 128k \
						 //  -movflags faststart \
						$time  = time();
						$video = $ffmpeg->output("$filepath.final.mp4")->ready();
						// print_r($video);
						// die();
						$video = "$filepath.final.mp4";

						$media->initDir('photos');

		                $dir      = "media/upload/photos/" . date('Y') . '/' . date('m');
		                $hash     = sha1(time() + time() - rand(9999,9999));

		                $thumb    = "$dir/$hash.video_thumb.jpeg";
		                $full_dir = $root;

		                $input_path = $upload['filename'];
		                $output_path = $thumb;

		                #Generete thumb

		                $_ffmpeg = new FFmpeg($config['ffmpeg_binary']);
		                $_ffmpeg->input($upload['filename']);
						$_ffmpeg->set('-ss','2');
						$_ffmpeg->set('-vframes','1');
						$_ffmpeg->set('-f','mjpeg');
		                $output_thumb = $_ffmpeg->output("$output_path")->ready();

		                $re_data = array(
		                	'user_id' => $me['user_id'],
		                	'time' => time(),
		                	'type' => 'reels',
		                );

		                if (!empty($_POST['caption'])) {
							$text = Generic::cropText($_POST['caption'],$config['caption_len']);
							$re_data['description'] = $text;
						}

						$post_id = $posts->insertPost($re_data);

						if (is_numeric($post_id)) {
							$re_data = array(
								'post_id' => $post_id,
								'file' => $video,
								'extra' => $thumb,
							);

							$media = new Media;
							if ($config['ftp_upload'] == 1) {
								$media->uploadToFtp($video);
								$media->uploadToFtp($thumb);
							} else if ($config['amazone_s3'] == 1) {
								$media->uploadToS3($video);
								$media->uploadToS3($thumb);
							} else if ($config['google_cloud_storage'] == 1) {
								$media->uploadToGoogleCloud($video);
								$media->uploadToGoogleCloud($thumb);
							}

							$posts->setPostId($post_id);
							$posts->insertMedia($re_data);

							
							$post_data = o2array($posts->postData());

							$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-video');	
							$data['status']  = 200;
							$data['message'] = lang('post_published');

							#Notify mentioned users
							$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));

							@unlink($upload['filename']);
							$media->deleteFromFTPorS3($upload['filename']);
						}
						else{
							$data['status']  = 500;
							$data['message'] = lang('unknown_error');
						}

					}
					catch(Exception $error){
						$data['status']  =  400;
						$data['message'] = lang('unknown_error');
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
			$media  = new Media();
			$posts  = new Posts();
			// print_r( $_FILES['video']);die;
			$media->setFile(array(
				'file' => $_FILES['video']['tmp_name'],
				'name' => $_FILES['video']['name'],
				'size' => $_FILES['video']['size'],
				'type' => $_FILES['video']['type'],
				'allowed' => 'mp4,mov,3gp,webm',
			));

			$video = $media->uploadFile();

			$media->setFile(array(
				'file' => $_FILES['thumb']['tmp_name'],
				'name' => $_FILES['thumb']['name'],
				'size' => $_FILES['thumb']['size'],
				'type' => $_FILES['thumb']['type'],
				'allowed' => 'jpeg,jpg,png',
				'crop' => array(
					'width' => '600',
					'height' => '400',
				)
			));

			$image = $media->uploadFile();
			if (!empty($video['filename']) && !empty($image['filename'])) {
	            
	            $re_data = array(
	            	'user_id' => $me['user_id'],
	            	'time' => time(),
	            	'type' => 'reels',
	            );

	            if (!empty($_POST['caption'])) {
					$text = Generic::cropText($_POST['caption'],500);
					$re_data['description'] = $text;
				}

				$post_id = $posts->insertPost($re_data);

				if (is_numeric($post_id)) {
					$re_data = array(
						'post_id' => $post_id,
						'file' => $video['filename'],
						'extra' => $image['cname'],
					);

					$posts->setPostId($post_id);
					$posts->insertMedia($re_data);
		
					$post_data = o2array($posts->postData());

					$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-video');	
					$data['status']  = 200;
					$data['message'] = lang('post_published');

				}
				else{
					$data['status']  = 500;
					$data['message'] = lang('unknown_error');
				}
			}
			else{
				$data['status']  = 400;
				$data['message'] = $video['error'];
			}
		}
	}
}
else if($action == 'embed-post-video' && IS_LOGGED && ($config['import_videos'] == 'on')) {

	if (!empty($_POST['embed']) && !empty($_POST['video_id']) && !empty($_POST['url'])) {
		$posts    = new Posts();
		$embed    = new Embed();
		$notif    = new Notifications();
		$emsrc    = (in_array($_POST['embed'], array('youtube','vimeo','dailymotion','playtube','mp4')) === true);
		$id_val   = false;
 
		if ($_POST['embed'] == 'youtube' && preg_match('/^([a-zA-Z0-9_-]{4,15})$/', $_POST['video_id'])) {
			$id_val = true;
		}

		else if($_POST['embed'] == 'vimeo' && preg_match('/^([0-9]+)$/', $_POST['video_id'])){
			$id_val = true;
		}

		else if($_POST['embed'] == 'dailymotion' && preg_match('/^([a-zA-Z0-9_]{4,15})$/', $_POST['video_id'])){
			$id_val = true;
		}
		else if($_POST['embed'] == 'playtube' && !empty($_POST['video_id']) && $config['playtube_links'] == 'on'){
			$id_val = true;
		}
		else if($_POST['embed'] == 'mp4' && !empty($_FILES['thumb']) && $config['mp4_links'] == 'on'){
			$id_val = true;
		}

		if ($emsrc && $id_val) {
			$media  = new Media();
            $url     = ((Generic::isUrl($_POST['url'])) ? $_POST['url'] : '');
            $emsrc   = $_POST['embed'];

            $re_data = array(
            	"user_id" => $me['user_id'],
            	"link" => $url,
            	"time" => time(),
            	"type" => $_POST['embed'],
            	"$emsrc" => $_POST['video_id'],
            );

            if (!empty($_POST['caption'])) {
				$text = Generic::cropText($_POST['caption'],$config['caption_len']);
				$re_data['description'] = $text;
			}

			if($_POST['embed'] == 'mp4' && !empty($_FILES['thumb'])){
				$media->setFile(array(
					'file' => $_FILES['thumb']['tmp_name'],
					'name' => $_FILES['thumb']['name'],
					'size' => $_FILES['thumb']['size'],
					'type' => $_FILES['thumb']['type'],
					'allowed' => 'jpeg,jpg,png',
					'crop' => array(
						'width' => '600',
						'height' => '400',
					)
				));
				$image = $media->uploadFile();
				if (empty($image['filename'])) {
					$data['status']  = 500;
					$data['message'] = lang('unknown_error');
					goto xhr_exit;
				}
			}

			$post_id = $posts->insertPost($re_data);
			
			if($_POST['embed'] == 'mp4' && !empty($image['filename'])){
				$re_data = array(
					'post_id' => $post_id,
					'file' => $image['filename'],
					'extra' => $image['filename']
				);

				$posts->setPostId($post_id);
				$posts->insertMedia($re_data);
			}
			
			if (is_numeric($post_id)) {
				$em_data = array();
				$re_data = array(
					'post_id' => $post_id,
				);

				try {
	            	$em_data = $embed->fetchVideo($url);
	            } 
	            catch (Exception $e) {
	            	$data['status']  = 500;
					$data['message'] = lang('unknown_error');
					goto xhr_exit;
	            }

            	if (!empty($em_data['images'])) {
            		$re_data['file'] = $em_data['images']['filename'];
            		$re_data['extra'] = $em_data['images']['extra'];
	            	$posts->insertMedia($re_data);
            	}
	            	

				$posts->setPostId($post_id);
				$post_data = o2array($posts->postData());

				$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-'.$emsrc);	
				$data['status']  = 200;
				$data['message'] = lang('post_published');

				#Notify mentioned users
				$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));
			}
			else{
				$data['status']  = 500;
				$data['message'] = lang('unknown_error');
			}
		}

		else{
			$data['status']  = 400;
		}
	}
	elseif (!empty($_POST['thumb_url']) && Generic::isUrl($_POST['thumb_url'])) {
		$media    = new Media();
		$posts    = new Posts();
		$embed    = new Embed();
		$notif    = new Notifications();

		$img = $media->ImportImageAndCrop($_POST['thumb_url']);
		if (!empty($img['filename'])) {
			$re_data = array(
				'user_id' => $me['user_id'],
				'time' => time(),
				'type' => 'fetched',
				'link' => Generic::secure($_POST['url'])
			);

			if (!empty($_POST['caption'])) {
				$text = Generic::cropText($_POST['caption'],$config['caption_len']);
				$re_data['description'] = $text;
			}
			$post_id = $posts->insertPost($re_data);

			$posts->setPostId($post_id);
			$_data['file'] = $img['filename'];
			$_data['extra'] = $img['extra'];
	        $posts->insertMedia($_data);

			$post_data = o2array($posts->postData());

			$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-fetched');
			$data['status']  = 200;
			$data['message'] = lang('post_published');

			#Notify mentioned users
			$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));
		}
		
	}
}

else if($action == 'import-post-gifs' && IS_LOGGED && ($config['import_images'] == 'on')) {
	$media    = new Media();
	if (!empty($_POST['gif_url'])){
		$posts = new Posts();
		$notif = new Notifications();

		if (Generic::isUrl($_POST['gif_url'])) {
            $gif_url = urlencode($_POST['gif_url']);
            $img = $media->ImportImageAndCrop($_POST['gif_url'],'gif');
            if (!empty($img['extra'])) {
            	$re_data = array(
	            	"user_id" => $me['user_id'],
	            	"time" => time(),
	            	"type" => 'gif',
	            );

	            if (!empty($_POST['caption'])) {
					$text = Generic::cropText($_POST['caption'],$config['caption_len']);
					$re_data['description'] = $text;
				}

				$post_id = $posts->insertPost($re_data);

				if (is_numeric($post_id)) {
					$re_data = array(
						'post_id' => $post_id,
						'file' => $gif_url,
						'extra' => $img['extra']
					);

					$posts->setPostId($post_id);
					$posts->insertMedia($re_data);

					
					$post_data = o2array($posts->postData());

					$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-image');	
					$data['status']  = 200;
					$data['message'] = lang('post_published');

					#Notify mentioned users
					$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));
	            }
	            else{
					$data['status']  = 500;
					$data['message'] = lang('unknown_error');
				}

			}
			else{
				$data['status']  = 500;
				$data['message'] = lang('unknown_error');
			}

		}

		else{
			$data['status']  = 400;
			$data['message'] = lang('unknown_error');
		}
	}
}

else if($action == 'delete-post' && IS_LOGGED) {	
	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id'])) {
		$posts   = new Posts();
		$post_id = $_POST['post_id'];
		$data['status']  = 304;
		$data['message'] = lang('unknown_error');

		$posts->setPostId($post_id);
		$posts->setUserById($me['user_id']);

		if ($posts->isPostOwner() || IS_ADMIN) {
			$del = $posts->deletePost();
			if ($del) {
				$data['status']  = 200;
				$data['message'] = 200;
			}
		}
	}
}

else if($action == 'add-comment' && IS_LOGGED) {
	
	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && !empty($_POST['text'])) {
		$posts   = new Posts();
		$notif   = new Notifications();
		$post_id = $_POST['post_id'];
		$text    = Generic::cropText($_POST['text'],$config['comment_len']);
		$text    = Generic::secure($text);
		$data['status'] = 304;

		$posts->setPostId($post_id);
		$posts->setUserById($me['user_id']);

		$link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
        $i          = 0;
        preg_match_all($link_regex, $text, $matches);
        foreach ($matches[0] as $match) {
            $match_url = strip_tags($match);
            $syntax    = '[a]' . urlencode($match_url) . '[/a]';
            $text      = str_replace($match, $syntax, $text);
        }

		$re_data = array(
			'text' => $text,
			'time' => time(),
		);


		$insert = $posts->addPostComment($re_data);


		if (!empty($insert)) {
			$comment = $posts->postCommentData($insert);
			if (!empty($comment)) {
				$comment = o2array($comment);
				$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/comments');
				$data['status'] = 200;

				#Notify post owner
				if (!$posts->isPostOwner()) {
					try {
						$posts->setPostId($post_id);
						$post_owner = $posts->getPostOwnerData();

						if (!empty($post_owner)) {
							$notif_conf = $notif->notifSettings($post_owner->user_id,'on_comment');

							if ($notif_conf) {
								$re_data = array(
									'notifier_id' => $me['user_id'],
									'recipient_id' => $post_owner->user_id,
									'type' => 'commented_ur_post',
									'url' => pid2url($post_id),
									'time' => time()
								);

								$notif->notify($re_data);
							}
						}
					} 
					catch (Exception $e) {
						
					}
				}

				#Notify mentioned users
				$notif->notifyMentionedUsers($_POST['text'],pid2url($post_id));
			}
		}
	}
}

else if($action == 'delete-comment' && IS_LOGGED) {
	
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$posts   = new Posts();
		$id      = $_POST['id'];
		$data['status'] = 304;
		$posts->setUserById($me['user_id']);
		if ($posts->isCommentOwner($id)) {
			$delete = $posts->deletePostComment($id);
			$data['status'] = 200;
		}
	}
}

else if($action == 'explore-posts' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id      = Generic::secure($_GET['offset']);
		$posts        = new Posts();
		$latest_posts = $posts->explorePosts($last_id);
		$context['posts'] = array();
		$data['status'] = 404;
		$data['html']   = "";
		$context['app_name'] = 'explore';

		if (!empty($latest_posts)) {
			$context['posts'] = o2array($latest_posts);

			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='explore';
				$data['html']    .= $pixelphoto->PX_LoadPage('explore/templates/explore/includes/list');
			}

			$data['status'] = 200;
		}
	}
}
else if($action == 'explore-reels' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id      = Generic::secure($_GET['offset']);
		$posts        = new Posts();
		$latest_posts = $posts->exploreReels($last_id);
		$context['posts'] = array();
		$data['status'] = 404;
		$data['html']   = "";
		$context['app_name'] = 'reels';

		if (!empty($latest_posts)) {
			$context['posts'] = o2array($latest_posts);

			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='reels';
				$data['html']    .= $pixelphoto->PX_LoadPage('explore/templates/explore/includes/list');
			}

			$data['status'] = 200;
		}
	}
}
else if($action == 'explore-lives' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id      = Generic::secure($_GET['offset']);
		$posts        = new Posts();
		$latest_posts = $posts->livePosts($last_id);
		$context['posts'] = array();
		$data['status'] = 404;
		$data['html']   = "";
		$context['app_name'] = 'lives';

		if (!empty($latest_posts)) {
			$context['posts'] = o2array($latest_posts);

			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='lives';
				$data['html']    .= $pixelphoto->PX_LoadPage('lives/templates/lives/includes/list');
			}

			$data['status'] = 200;
		}
	}
}

else if($action == 'explore-tags') {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset']) && !empty($_SESSION['tag_id'])) {
		$last_id = Generic::secure($_GET['offset']);
		$htag    = Generic::secure($_SESSION['tag_id']);
		$posts   = new Posts();


		$latest_posts     = $posts->exploreTags($htag,$last_id);

		$context['posts'] = array();	
		$data['status']   = 404;
		$html             = "";
		$context['app_name'] = 'explore';

		if (!empty($latest_posts)) {

			$context['posts'] = o2array($latest_posts);

			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='tags';
				$html   .= $pixelphoto->PX_LoadPage('explore/templates/explore/includes/list');
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

else if($action == 'load-user-posts') {
	$vl1 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));
	$vl2 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));

	if ($vl1 && $vl2) {
		$last_id = Generic::secure($_GET['offset']);
		$user_id = Generic::secure($_GET['user_id']);
		$posts   = new Posts();

		try {

			$ami_blocked = $user->isBlocked($user_id,true);

			if ($user->profilePrivacy($user_id) && empty($ami_blocked)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserPosts($last_id);
			}
			elseif (empty(IS_LOGGED)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserPosts($last_id);
			}
		} 

		catch (Exception $e) {
			goto xhr_exit;
		}

		$context['posts'] = array();

		$data['status'] = 404;
		$data['html']   = "";

		if (!empty($user_posts)) {
			$context['posts'] = o2array($user_posts);
			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='posts';
				$data['html']    .= $pixelphoto->PX_LoadPage('profile/templates/profile/includes/list');
			}

			$data['status'] = 200;
		}
	}
}
else if($action == 'load-user-reels') {
	$vl1 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));
	$vl2 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));

	if ($vl1 && $vl2) {
		$last_id = Generic::secure($_GET['offset']);
		$user_id = Generic::secure($_GET['user_id']);
		$posts   = new Posts();

		try {

			$ami_blocked = $user->isBlocked($user_id,true);

			if ($user->profilePrivacy($user_id) && empty($ami_blocked)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserReels($last_id);
			}
			elseif (empty(IS_LOGGED)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserReels($last_id);
			}
		} 

		catch (Exception $e) {
			goto xhr_exit;
		}

		$context['posts'] = array();

		$data['status'] = 404;
		$data['html']   = "";

		if (!empty($user_posts)) {
			$context['posts'] = o2array($user_posts);
			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='posts';
				$data['html']    .= $pixelphoto->PX_LoadPage('profile/templates/profile/includes/reels_list');
			}

			$data['status'] = 200;
		}
	}
}
else if($action == 'load-saved-posts'  && IS_LOGGED) {
	$request = (!empty($_GET['offset']) && is_numeric($_GET['offset']));

	if ($request == true) {
		$last_id = Generic::secure($_GET['offset']);
		$posts   = new Posts();
		
		$data['status']   = 404;
		$user_posts       = $posts->getSavedPosts($last_id);
		$html             = "";

		if (!empty($user_posts)) {

			$context['posts'] = o2array($user_posts);
			foreach ($context['posts'] as $key => $post_data) {
				$context['page'] ='favourites';
				$data['html']    .= $pixelphoto->PX_LoadPage('profile/templates/profile/includes/fav');
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

else if($action == 'lightbox') {
	if ((!empty($_GET['post_id']) && is_numeric($_GET['post_id']))) {

		$post_id = $_GET['post_id'];
		$page    = (!empty($_GET['page'])) ? $_GET['page'] : false;
		$posts   = new Posts();
		$posts->setPostId($post_id);
		$posts->setPostId($post_id)->add_view();

		$post_data = $posts->postData();
		$data['status'] = 404;
		$data['html']   = "";

		

		if (!empty($post_data) && !empty($page)) {
			$posts->setPostId($post_id);
			if ($page == 'tags' && !empty($_SESSION['tag_id'])) {
				$posts->tag_id = $_SESSION['tag_id'];
			}

			$post_data      = o2array($post_data);
			$thumb          = "";
			$is_following   = false;
			$has_next       = $posts->hasNext($page);
			$has_prev       = $posts->hasPrev($page);
			
			if (IS_LOGGED) {
				$is_following = $user->isFollowing($post_data['user_id']);
			}

			if (in_array($post_data['type'], array('youtube','dailymotion','vimeo','playtube'))) {
				if (!empty($post_data['thumb'])) {
					$thumb = $post_data['thumb'];
				}
			}

            $context['thumb'] = $thumb;
            $context['post_data'] = $post_data;
            $context['is_following'] = $is_following;
            $context['prev'] = $has_prev;
            $context['next'] = $has_next;
            $context['page'] = $page;
            $data['html']    = $pixelphoto->PX_LoadPage('main/templates/includes/lightbox');
			$data['status'] = 200;
		}
	}
}

else if($action == 'like' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id = $_POST['id'];
		$posts   = new Posts();
		$data    = array('status' => 304);

		$posts->setPostId($post_id);
		$code    = $posts->likePost();

		if ($code == 1 || $code == -1) {
			$data['code'] = $code;
			$data['status'] = 200;
			
			if ($posts->isPostOwner(false) == false && $code == 1) {
				$post_owner = $posts->getPostOwnerData();
				if (!empty($post_owner)) {
					$notif      = new Notifications();
					$notif_conf = $notif->notifSettings($post_owner->user_id,'on_like');
					if ($notif_conf) {
						$re_data = array(
							'notifier_id' => $me['user_id'],
							'recipient_id' => $post_owner->user_id,
							'type' => 'liked_ur_post',
							'url' => pid2url($post_id),
							'time' => time()
						);

						$notif->notify($re_data);
					}
				}
			}
		}
	}
}

else if($action == 'save' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id = $_POST['id'];
		$posts   = new Posts();
		$data    = array('status' => 304);

		$posts->setPostId($post_id);
		
		$code            = $posts->savePost();
		$code            = ($code == -1) ? 0 : 1;
		$data['code']    = $code;
		$data['status']  = 200;
		$data['message'] = lang('post_added2fav');
		if ($code == 0) {
			$data['message'] = lang('post_rem_from_fav');
		}
	}
}

elseif ($action == 'update' && IS_LOGGED) {
	$vl1 = (!empty($_POST['id']) && is_numeric($_POST['id']));
	$vl2 = (isset($_POST['text']));

	if ($vl1 && $vl2) {
		$post_id  = $_POST['id'];
		$text     = Generic::secure($_POST['text']);
		$posts    = new Posts();
		$notif    = new Notifications();
		$is_owner = $posts->setPostId($post_id)->isPostOwner();
		$data     = array('status' => 304,'message' => lang('unknown_error'));

		if ($is_owner === true|| IS_ADMIN) {
			$update = $posts->updatePost(array('description' => $text));
			$data['status']  = 200;
			$data['message'] = lang('changes_saved');

			#Notify mentioned users
			$notif->notifyMentionedUsers($text,pid2url($post_id));
		}
	}
}

elseif ($action == 'report' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id  = $_POST['id'];
		$posts    = new Posts();
		$report   = $posts->setPostId($post_id)->reportPost();
		$data     = array('status' => 304);

		if ($report == 1) {
			$data['status']  = 200;
			$data['code']    = 1;
			$data['message'] = lang('report_sent');
		}
		else if($report == -1){
			$data['status']  = 200;
			$data['code']    = 0;
			$data['message'] = lang('report_canceled');
		}
	}
}

elseif ($action == 'load-tl-posts' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id  = $_GET['offset'];
		$posts    = new Posts();
		$data     = array('status' => 404);
		$qset     = $posts->getTimelinePosts($last_id);
		$qset     = (!empty($qset)) ? o2array($qset) : 0;
		$html     = "";

		if (len($qset) > 0) {
			foreach ($qset as $post_data) {


				if ($post_data['type'] == 'image' || $post_data['type'] == 'gif') {
					$html  .= $pixelphoto->PX_LoadPage('home/templates/home/includes/post-image');
				}

				elseif ($post_data['type'] == 'video') {
					$html  .= $pixelphoto->PX_LoadPage('home/templates/home/includes/post-video');
				}

				elseif ($post_data['type'] == 'youtube') {
					$html  .= $pixelphoto->PX_LoadPage('home/templates/home/includes/post-youtube');
				}
				
				elseif ($post_data['type'] == 'vimeo') {
					$html  .= $pixelphoto->PX_LoadPage('home/templates/home/includes/post-vimeo');
				}	
							
				elseif ($post_data['type'] == 'dailymotion') {
					$html  .= $pixelphoto->PX_LoadPage('home/templates/home/includes/post-dailymotion');
				}
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

elseif ($action == 'view-likes' && !empty($_GET['post_id'])) {
	if (is_numeric($_GET['post_id'])) {
		$posts   = new Posts();
		$post_id = $posts::secure($_GET['post_id']);
		$query   = $posts->setPostId($post_id)->getLikedUsers();
		$query   = (!empty($query)) ? o2array($query) : array();
		$data    = array('status' => 404);
		if (!empty($query)) {
			$context['users'] = $query;

			$data['status']  = 200;
			$data['html']    = $pixelphoto->PX_LoadPage('main/templates/modals/view-post-likes');

		}
		else{
			$data['message'] = lang('unknown_error');
		}
	}
}

else if($action == 'load-tlp-comments' && IS_LOGGED && !empty($_POST['post_id'])) {
	
	if (is_numeric($_POST['post_id']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) {
		$data    = array(
			'status' => 404
		);

		$posts   = new Posts();
		$posts->comm_limit = 30;
		$post_id = $_POST['post_id'];
		$offset  = $_POST['offset'];
		$query   = $posts->setPostId($post_id)->getPostComments($offset);
		$html    = '';


		if (!empty($query)) {
			$comments = o2array($query);

			foreach ($comments as $comment) {
				$html  .= $pixelphoto->PX_LoadPage('home/templates/home/includes/comments');
			}

			$data['status']  = 200;
			$data['html']    = $html;
		}
		else{
			$data['message'] = lang('no_more_comments');
		}
	}
}


else if($action == 'add_view' && !empty($_POST['post_id'])) {
	
	if (is_numeric($_POST['post_id'])) {
		$data    = array(
			'status' => 400
		);

		$posts   = new Posts();
		$post_id = $_POST['post_id'];
		$count   = $posts->setPostId($post_id)->add_view();

		if (!empty($count)) {
			$data['status']  = 200;
			$data['count']    = $count;
		}
	}
}

else if($action == 'is_playtube' && !empty($_POST['url'])) {
	//[https:\/\/playtubescript.com\/watch\/]([^\/][a-zA-Z0-9]*)
	$data    = array(
		'status' => 400
	);
	if (strpos($_POST['url'], $config['playtube_url']) !== false) {
		$re = str_replace("/", "\/", $config['playtube_url'].'/watch/');
		preg_match_all("/({$re})([^\/]*)/", $_POST['url'] , $matches);
		if (!empty($matches[2][0])) {
			$data    = array(
				'status' => 200,
				'video_id' => $matches[2][0],
				'url' =>$config['playtube_url'].'/embed/'.$matches[2][0]
			);
		}
	}
}

else if($action == 'url_fetch' && !empty($_POST['url'])) {
	$data['status'] = 400;
	$page_title = '';
    $image_urls = array();
    $page_body = '';
    if (Generic::isUrl($_POST['url'])) {
    	include './sys/import3p/simple_html_dom.inc.php';
		$get_content = file_get_html($_POST['url']);

	    foreach ($get_content->find('title') as $element) {
	        @$page_title = $element->plaintext;
	    }

	    if (empty($page_title)) {
	        $page_title = '';
	    }

	    @$page_body = $get_content->find("meta[name='description']", 0)->content;
	    $page_body = mb_substr($page_body, 0, 250, "utf-8");
	    if ($page_body === false) {
	        $page_body = '';
	    }
	    if (empty($page_body)) {
	        @$page_body = $get_content->find("meta[property='og:description']", 0)->content;
	        $page_body = mb_substr($page_body, 0, 250, "utf-8");
	        if ($page_body === false) {
	            $page_body = '';
	        }
	    }
	    $image_urls = array();
	    @$page_image = $get_content->find("meta[property='og:image']", 0)->content;
	    if (!empty($page_image)) {
	        if (preg_match('/[\w\-]+\.(jpg|png|gif|jpeg)/', $page_image)) {
	            $image_urls[] = $page_image;
	        }
	    } else {
	        foreach ($get_content->find('img') as $element) {
	            if (!preg_match('/blank.(.*)/i', $element->src)) {
	                if (preg_match('/[\w\-]+\.(jpg|png|gif|jpeg)/', $element->src)) {
	                    $image_urls[] = $element->src;
	                }
	            }
	        }
	    }
	    if (!empty($image_urls)) {
	    	$data = array(
		        'title' => $page_title,
		        'images' => $image_urls,
		        'content' => $page_body,
		        'url' => $_POST["url"],
		        'status' => 200
		    );
	    }
    }
}
else if($action == 'boost' && !empty($_POST['post_id'])) {
	$data    = array(
		'status' => 400
	);
	$post_id = Generic::secure($_POST['post_id']);
	$posts   = new Posts();
	$boost = $posts->BoostPost($post_id);
	if ($boost == 1) {
		$data = array('status' => 200,'code' => 1,'message' => lang('unboost_post'));
	}
	elseif($boost == 2){
		$data = array('status' => 200,'code' => 2,'message' => lang('boost_post'));
	}
}
xhr_exit: