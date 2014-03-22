<?php


function createEmailPic($jid, $email) {
    if(file_exists(DOCUMENT_ROOT.'/cache/'.$jid.'_email.jpg'))  
        unlink(DOCUMENT_ROOT.'/cache/'.$jid.'_email.jpg');
    
    $thumb = imagecreatetruecolor(250, 20);
    $white = imagecolorallocate($thumb, 255, 255, 255);
    imagefill($thumb, 0, 0, $white);

    $text_color = imagecolorallocate ($thumb, 0, 0,0);//black text
    imagestring ($thumb, 4, 0, 0,  $email, $text_color);
    
    imagejpeg($thumb, DOCUMENT_ROOT.'/cache/'.$jid.'_email.jpg', 95);
}
