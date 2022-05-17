<?php
if ($action == 'load-tl-articles' && IS_LOGGED) {
    if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
        $last_id  = $_GET['offset'];
        $posts = $db->arrayBuilder()->where('id',$last_id,'<')->orderBy('id','DESC')->get(T_BLOG,20);
        $data     = array('status' => 404);
        $html     = "";

        if (len($posts) > 0) {
            foreach ($posts as $key => $post_data) {
                $post_data['category_name'] = $context['lang'][$post_data['category']];
                $post_data['full_thumbnail'] = media($post_data['thumbnail']);
                $post_data['text_time'] = time2str($post_data['created_at']);
                $html  .= $pixelphoto->PX_LoadPage('blog/templates/blog/includes/list');
            }
            $data['status'] = 200;
            $data['html']   = $html;
        }
    }
}
else if($action == 'like' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id = (int)$_POST['id'];
		$posts   = new Blogs();
		$data    = array('status' => 304);

		$posts->setBlogId($post_id);
		$code    = $posts->likeBlog();

		if ($code == 1 || $code == -1) {
			$data['code'] = $code;
			$data['status'] = 200;
		}
	}
}
else if($action == 'add-comment' && IS_LOGGED) {
	
	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && !empty($_POST['text'])) {
		$posts   = new Blogs();
		$notif   = new Notifications();
		$post_id = $_POST['post_id'];
		$text    = Generic::cropText($_POST['text'],$config['comment_len']);
		$text    = Generic::secure($text);
		$data['status'] = 304;

		$posts->setBlogId($post_id);
		//$posts->setUserById($me['user_id']);

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
				$data['html']    = $pixelphoto->PX_LoadPage('article/templates/article/includes/comments');
				$data['status'] = 200;
			}
		}
	}
}
else if($action == 'delete-comment' && IS_LOGGED) {
	
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$posts   = new Blogs();
		$id      = $_POST['id'];
		$data['status'] = 304;
		$posts->setUserById($me['user_id']);
		if ($posts->isCommentOwner($id)) {
			$delete = $posts->deletePostComment($id);
			$data['status'] = 200;
		}
	}
}
else if($action == 'delete-article' && IS_LOGGED) {
	$data['status'] = 304;
    if (!empty($_POST['post_id']) && is_numeric($_POST['post_id'])) {
        $delete = DeleteArticle($_POST['post_id'], $_POST['thumbnail']);
        if ($delete) {
            $data = array(
                'status' => 200
            );
        }
    }
}