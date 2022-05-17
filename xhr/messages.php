<?php 
if (IS_LOGGED !== true) {
	$data['status'] = 400;
	$data['error'] = "Not logged in";
	goto exit_xhr;
}

else if ($action == 'send') {
	$vl1 = ((!empty($_POST['text']) || !empty($_FILES['send_file'])) && !empty($_SESSION['to_id']));
	$vl2 = (is_numeric($_SESSION['to_id']) === true);


	if ($vl1 && $vl2) {
		$to_id       = $_SESSION['to_id'];
		$c_privacy   = $user->chatPrivacy($to_id);
		$data        = array(
			'status' => 400
		);

		if ($c_privacy) {
			$user_id   = $me['user_id'];
			$text      = Generic::secure($_POST['text']);
			$messages  = new Messages();
			$user_data = $user->getUserDataById($to_id);
			$re_data   = array(
				'from_id' => $user_id,
				'to_id' => $to_id,
				'text' => $text,
				'time' => time()
			);

			if (!empty($_FILES['send_file'])) {

				if ($_FILES['send_file']['error'] !== UPLOAD_ERR_OK) {
					$data['status']  = 400;
					$data['message'] = "Upload failed with error code " . $_FILES['send_file']['error'];
				}else{
					$new_string        = pathinfo($_FILES['send_file']['name'], PATHINFO_FILENAME) . '.' . strtolower(pathinfo($_FILES['send_file']['name'], PATHINFO_EXTENSION));
					$file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);

					$allow_upload_this = false;

					if ($file_extension == 'jpg' || $file_extension == 'jpeg' || $file_extension == 'png' || $file_extension == 'gif') {
						$re_data['media_type'] = 'image';
						$info = getimagesize($_FILES['send_file']['tmp_name']);
						if ($info === FALSE) {
							$data['status']  = 400;
							$data['message'] = "Unable to determine image type of uploaded file";
						}else if ( ($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
							$data['status']  = 400;
							$data['message'] = "Not a jpeg or png or gif";
						}else{
							$allow_upload_this = true;
						}
					}
					elseif ($file_extension == 'mp4' || $file_extension == 'webm' || $file_extension == 'flv') {
						$re_data['media_type'] = 'video';
						if(isVideo($_FILES['send_file']['tmp_name'])){
							$allow_upload_this = true;
						}else{
							$data['status']  = 400;
							$data['message'] = "Unable to determine video type of uploaded file";
						}
					}
					else{
						$re_data['media_type'] = 'file';
					}

					if ($allow_upload_this === true) {
						$media  = new Media();
		
						$media->setFile(array(
							'file' => $_FILES['send_file']['tmp_name'],
							'name' => $_FILES['send_file']['name'],
							'size' => $_FILES['send_file']['size'],
							'type' => $_FILES['send_file']['type'],
							'allowed' => 'jpg,jpeg,png,gif,zip,txt',
						));
		
						$file = $media->uploadFile();
						if (!empty($file) && !empty($file['filename'])) {
							if ($file_extension == 'jpg' || $file_extension == 'jpeg' || $file_extension == 'png' || $file_extension == 'gif') {
								$re_data['media_type'] = 'image';
							}
							elseif ($file_extension == 'mp4' || $file_extension == 'webm' || $file_extension == 'flv') {
								$re_data['media_type'] = 'video';
							}
							else{
								$re_data['media_type'] = 'file';
							}
							$re_data['media_file'] = $file['filename'];
							$re_data['media_name'] = $file['name'];
							$re_data['text'] = $re_data['media_type'];
						}
					}
				}

			}

			if (!empty($re_data['text']) || !empty($re_data['media_file'])) {

				$msg_data = $messages->sendMessage($re_data);
				
				if (!empty($msg_data)) {
					$msg_data = o2array($msg_data);
					$context['user_data'] = o2array($user_data);
					$data['html']    = $pixelphoto->PX_LoadPage('messages/templates/messages/includes/messages-list');
					$data['status'] = 200;
				}
			}
		}
	}
}

else if($action == 'update-chat'){

	$lm_id = null;

	if (!empty($_GET['lid']) && is_numeric($_GET['lid']) && !empty($_SESSION['to_id'])) {
		$lm_id = $_GET['lid'];
		$to_id = $_SESSION['to_id'];

		$messages = new Messages();
		$html     = "";
		$messages->setUserById($me['user_id']);
		$new_messages = $messages->getMessages($to_id,$lm_id,true);
		$user_data    = $user->getUserDataById($to_id);
		$data         = array(
			'status' => 404
		);


		if (!empty($new_messages)) {
			$new_messages = o2array($new_messages);

			foreach ($new_messages as $msg_data) {
				$msg_data = o2array($msg_data);
				$context['user_data'] = o2array($user_data);
				$html    .= $pixelphoto->PX_LoadPage('messages/templates/messages/includes/messages-list');
			}

			$data['status'] = 200;
			$data['html'] = $html;
		}
	}
}

else if ($action == 'delete-chat') {
	if (!empty($_SESSION['to_id'])) {
		$to_id    = $_SESSION['to_id'];
		$messages = new Messages();
		$messages->setUserById($me['user_id']);
		$delete   = $messages->deleteChat($to_id);
		$data     = array(
			'status' => 404
		);

		if (!empty($delete)) {
			$data['status'] = 200;
			$data['url'] = sprintf('%s/messages',$site_url);
		}
	}
}

else if ($action == 'clear-chat') {
	if (!empty($_SESSION['to_id'])) {
		$to_id    = $_SESSION['to_id'];
		$messages = new Messages();
		$messages->setUserById($me['user_id']);
		$clear    = $messages->clearChat($to_id);
		$data     = array(
			'status' => 404,
			'message' => lang('unknown_error')
		);

		if (!empty($clear)) {
			$data['status']  = 200;
			$data['message'] = lang('conversation_deleted');
		}
	}
}

else if ($action == 'delete-messages' && !empty($_POST['messages'])) {
	if (!empty($_SESSION['to_id']) && is_array($_POST['messages'])) {
		if (!in_array(false, array_map('is_numeric',$_POST['messages']))) {
			$to_id    = $_SESSION['to_id'];
			$messages = new Messages();
			$messages->setUserById($me['user_id']);
			$clear    = $messages->deleteMessages($to_id,$_POST['messages']);
			$data     = array(
				'status' => 404,
			);

			if (!empty($clear)) {
				$data['status']  = 200;
			}
		}	
	}
}
else if ($action == 'get_old_messages' && !empty($_GET['last_msg'])) {
	$lm_id = $_GET['last_msg'];
	$to_id = $_SESSION['to_id'];
	$messages = new Messages();
	$html     = "";
	$messages->setUserById($me['user_id']);
	$new_messages = $messages->getMessages($to_id,$lm_id,false,'DESC','<');
	$user_data    = $user->getUserDataById($to_id);
	$data         = array(
		'status' => 404
	);


	if (!empty($new_messages)) {
		$new_messages = o2array($new_messages);

		foreach ($new_messages as $msg_data) {
			$msg_data = o2array($msg_data);
			$context['user_data'] = o2array($user_data);
			$html    .= $pixelphoto->PX_LoadPage('messages/templates/messages/includes/messages-list');
		}

		$data['status'] = 200;
		$data['html'] = $html;
	}
}

exit_xhr: