<?php

require('init.php');

function bytesToSize1024($bytes, $precision = 2) {
    $unit = array('B','KB','MB');
    return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision).' '.$unit[$i];
}

$sFileName = $_FILES['image_file']['name'];
$sFileType = $_FILES['image_file']['type'];
$sFileSize = bytesToSize1024($_FILES['image_file']['size'], 1);

$user = new User();

define('USER_PATH', BASE_PATH . 'users/'.$user->getLogin().'/');

$error = $_FILES['image_file']['error'];

if ($error == UPLOAD_ERR_OK) {
    $tmp_name = $_FILES["image_file"]["tmp_name"];
    $name = $_FILES["image_file"]["name"];
    move_uploaded_file($tmp_name, USER_PATH.$name);
    
    createThumbnailPicture(USER_PATH, $name);
}
