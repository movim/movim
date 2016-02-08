<?php


function createEmailPic($jid, $email) {
    $cachefile = DOCUMENT_ROOT.'/cache/'.$jid.'_email.png';

    if(file_exists(DOCUMENT_ROOT.'/cache/'.$jid.'_email.png'))
        unlink(DOCUMENT_ROOT.'/cache/'.$jid.'_email.png');

    $draw = new ImagickDraw();
    try {
        $draw->setFontSize(13);
        $draw->setGravity(Imagick::GRAVITY_CENTER);

        $canvas = new Imagick();

        $metrics = $canvas->queryFontMetrics($draw, $email);

        $canvas->newImage($metrics['textWidth'], $metrics['textHeight'], "transparent", "png");
        $canvas->annotateImage($draw, 0, 0, 0, $email);

        $canvas->setImageFormat('PNG');
        $canvas->writeImage($cachefile);

        $canvas->clear();
    } catch (ImagickException $e) {
        error_log($e->getMessage());
    }
}
