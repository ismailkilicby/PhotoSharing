<?php
require_once('./sys/init.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);

set_time_limit(0);
error_reporting(0);

if (empty($_GET['id'])) {
    exit('Empty ID, which track though?');
}
if (empty($_GET['hash'])) {
    exit('Empty ID, which track though?');
}
if ($_GET['hash'] !== $_SESSION['csrf']) {
    exit('Invaled csrf token.');
}

$getImage = $db->where('id', Generic::secure($_GET['id']))->getOne(T_STORE);

if (empty($getImage)) {
    exit('File not found.');
}

if (!empty($_SESSION['csrf']) && !empty($_GET['hash'])) {
    if ($_SESSION['csrf'] == $_GET['hash']) {
        if (IS_LOGGED) {
            $can_download = false;
            //$isPurchased = is_store_item_purchased(Generic::secure($_GET['id']));
            //if( !$isPurchased ) {
                update_store_image_downloads(Generic::secure($_GET['id']));
            //}
            $basename = basename($getImage->full_file);
            $file_name = __DIR__ . DIRECTORY_SEPARATOR . $getImage->full_file;
            $mime = ($mime = getimagesize($file_name)) ? $mime['mime'] : $mime;
            $size = filesize($file_name);
            $fp   = fopen($file_name, "rb");
            if (!($mime && $size && $fp)) {
                exit('Error while downloading image.');
            }
            header("Content-type: " . $mime);
            header("Content-Length: " . $size);
            header("Content-Disposition: attachment; filename=" . $basename);
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            fpassthru($fp);
            exit();
        }
    } else {
        exit('Please click on download link again, this link is expired');
    }
}
?>
<script language="JavaScript">
    window.close();
</script>
