<?php
if ($action == 'like_dislike' && IS_LOGGED && !empty($_POST['comment_id'])) {
	$post_object = new Posts();
	$comment_object = new Comments();
	$comment_id = Generic::secure($_POST['comment_id']);
	$comment = $post_object->postCommentData($comment_id);
	if (!empty($comment)) {
		if ($comment->is_liked) {
			$db->where('comment_id',$comment_id)->where('user_id',$me['user_id'])->delete(T_COMMENTS_LIKES);
			$data['status']  = 200;
			$data['code'] = 0;
			$data['likes'] = $comment->likes - 1;
		}
		else{
			$comment_object->insertCommentLike(array('comment_id' => $comment_id,
		                                             'user_id'    => $me['user_id']));
			if ($comment->user_id != $me['user_id']) {
				$notif   = new Notifications();
				$notif_conf = $notif->notifSettings($comment->user_id,'on_comment_like');

				if ($notif_conf) {
					$re_data = array(
						'notifier_id' => $me['user_id'],
						'recipient_id' => $comment->user_id,
						'type' => 'liked_ur_comment',
						'url' => pid2url($comment->post_id),
						'time' => time()
					);

					$notif->notify($re_data);
				}
			}
			$data['status']  = 200;
			$data['code'] = 1;
			$data['likes'] = $comment->likes + 1;
		}
	}
}
elseif ($action == 'add_comment_reply' && IS_LOGGED && !empty($_POST['comment_id']) && !empty($_POST['text'])) {
	$post_object = new Posts();
	$comment_object = new Comments();
	$comment_id = Generic::secure($_POST['comment_id']);
	$comment = $post_object->postCommentData($comment_id);
	if (!empty($comment)) {
		$text    = Generic::cropText($_POST['text'],$config['comment_len']);
		$text    = Generic::secure($text);

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
			'comment_id' => $comment_id
		);


		$insert = $comment_object->addCommentReply($re_data);


		if (!empty($insert)) {


			$reply = $comment_object->commentReplyData($insert);
			if (!empty($reply)) {

				$context['reply'] = o2array($reply);
				$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/comment_reply');
				$data['status'] = 200;

				if ($comment->user_id != $me['user_id']) {
					$notif   = new Notifications();
					$notif_conf = $notif->notifSettings($comment->user_id,'on_comment_reply');

					if ($notif_conf) {
						$re_data = array(
							'notifier_id' => $me['user_id'],
							'recipient_id' => $comment->user_id,
							'type' => 'reply_ur_comment',
							'url' => pid2url($comment->post_id),
							'time' => time()
						);

						$notif->notify($re_data);
					}
				}
			}
		}
	}
}

elseif ($action == 'get_comment_reply' && IS_LOGGED && !empty($_POST['comment_id'])) {
	$post_object = new Posts();
	$comment_object = new Comments();
	$comment_id = Generic::secure($_POST['comment_id']);
	$comment = $post_object->postCommentData($comment_id);
	if (!empty($comment)) {
		$replies = $comment_object->get_comment_replies($comment_id);
		$html = '';
		foreach ($replies as $key => $reply) {
			$context['reply'] = o2array($reply);
			$html    .= $pixelphoto->PX_LoadPage('home/templates/home/includes/comment_reply');
			

		}
		$data['status'] = 200;
		$data['html'] = $html;
	}
}
elseif ($action == 'reply_like_dislike' && IS_LOGGED && !empty($_POST['reply_id'])) {
	$post_object = new Posts();
	$comment_object = new Comments();
	$reply_id = Generic::secure($_POST['reply_id']);
	$comment = $comment_object->commentReplyData($reply_id);
	if (!empty($comment)) {
		if ($comment->is_liked) {
			$db->where('reply_id',$reply_id)->where('user_id',$me['user_id'])->delete(T_COMMENTS_REPLY_LIKES);
			$data['status']  = 200;
			$data['code'] = 0;
			$data['likes'] = $comment->likes - 1;
		}
		else{
			$comment_object->insertCommentReplyLike(array('reply_id' => $reply_id,
		                                                  'user_id'    => $me['user_id']));
			if ($comment->user_id != $me['user_id']) {
				$notif   = new Notifications();
				$notif_conf = $notif->notifSettings($comment->user_id,'on_comment_like');

				if ($notif_conf) {
					$comment_info = $post_object->postCommentData($comment->comment_id);
					$re_data = array(
						'notifier_id' => $me['user_id'],
						'recipient_id' => $comment->user_id,
						'type' => 'liked_ur_comment',
						'url' => pid2url($comment_info->post_id),
						'time' => time()
					);

					$notif->notify($re_data);
				}
			}
			$data['status']  = 200;
			$data['code'] = 1;
			$data['likes'] = $comment->likes + 1;
		}
	}
}
elseif ($action == 'delete_reply' && IS_LOGGED && !empty($_POST['reply_id'])) {
	$post_object = new Posts();
	$comment_object = new Comments();
	$reply_id = Generic::secure($_POST['reply_id']);
	$reply = $comment_object->deleteCommentReply($reply_id);
	$data['status'] = 400;
	if ($reply) {
		$data['status'] = 200;
	}
}