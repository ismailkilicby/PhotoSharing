<?php
require_once('./sys/init.php');

if (IS_LOGGED == false || $user->isAdmin() == false) {
	header("Location: $site_url");
    exit();
}

require_once('./admin-panel/index.php');