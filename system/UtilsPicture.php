<?php

function createThumbnails($jid, $photobin) {
    unlink(BASE_PATH.'cache/'.$jid.'_l.jpg');
    unlink(BASE_PATH.'cache/'.$jid.'_m.jpg');
    unlink(BASE_PATH.'cache/'.$jid.'_s.jpg');
    unlink(BASE_PATH.'cache/'.$jid.'_xs.jpg');
    createThumbnailSize($photobin, 150, BASE_PATH.'cache/'.$jid.'_l.jpg');
    createThumbnailSize($photobin, 120, BASE_PATH.'cache/'.$jid.'_m.jpg');
    createThumbnailSize($photobin, 50, BASE_PATH.'cache/'.$jid.'_s.jpg');
    createThumbnailSize($photobin, 24, BASE_PATH.'cache/'.$jid.'_xs.jpg');
}

function createThumbnailSize($photobin, $size, $path) {
    $thumb = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($thumb, 255, 255, 255);
    imagefill($thumb, 0, 0, $white);
    
    $source = imagecreatefromstring(base64_decode($photobin));
    
    $width = imagesx($source);
    $height = imagesy($source);
    
    if($width >= $height) {
        // For landscape images
        $x_offset = ($width - $height) / 2;
        $y_offset = 0;
        $square_size = $width - ($x_offset * 2);
    } else {
        // For portrait and square images
        $x_offset = 0;
        $y_offset = ($height - $width) / 2;
        $square_size = $height - ($y_offset * 2);
    }
    
    if($source) {
        imagecopyresampled($thumb, $source, 0, 0, $x_offset, $y_offset, $size, $size, $square_size, $square_size);
        
        //display_image($hash, "image/jpeg");
        imagejpeg($thumb, $path, 95);
    }
}
