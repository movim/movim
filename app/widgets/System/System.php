<?php

use Movim\Controller\Ajax;

class System extends \Movim\Widget\Base
{
    function load()
    {
    }

    function display()
    {
        header('Cache-Control:public, max-age=31536000');
        header('Content-Type: application/javascript');

        $this->view->assign('base_host',    BASE_HOST);
        $this->view->assign('small_picture_limit', SMALL_PICTURE_LIMIT);
        $this->view->assign('error_uri',    $this->route('disconnect'));
        $this->view->assign('secure_websocket',    file_get_contents(CACHE_PATH.'websocket'));
    }
}
