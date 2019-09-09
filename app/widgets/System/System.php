<?php

use Movim\Widget\Base;

class System extends Base
{
    public function display()
    {
        header('Cache-Control:public, max-age=31536000');
        header('Content-Type: application/javascript');

        $this->view->assign('base_host', BASE_HOST);
        $this->view->assign('base_uri', BASE_URI);
        $this->view->assign('small_picture_limit', SMALL_PICTURE_LIMIT);
        $this->view->assign('error_uri', $this->route('disconnect'));
    }
}
