<?php
define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

$bootstrap = new Bootstrap();
$booted = $bootstrap->boot();

$sFileName = $_FILES['image_file']['name'];
$sFileType = $_FILES['image_file']['type'];
$error = $_FILES['image_file']['error'];

$user = new User();

if ($error == UPLOAD_ERR_OK && $user->dirSize() < $user->sizelimit) {
    $tmp_name = $_FILES["image_file"]["tmp_name"];
    if(getimagesize($tmp_name) != 0) {
        $name = stringToUri($_FILES["image_file"]["name"]);
        move_uploaded_file($tmp_name, $user->userdir.$name);

        $p = new \Picture;
        $p->fromPath($user->userdir.$name);
        $p->set($user->userdir.$name);
    } else {
        unlink($tmp_name);
        echo '<div class="message error">'.t('Not a picture').'</div>';
    }
} else {
    echo '<div class="message error">'.t('Folder size limit exceeded').'</div>';
}
