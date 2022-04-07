<?php

use Movim\Widget\Base;

class System extends Base
{
    public function display()
    {
        header('Content-Type: application/javascript');

        $keys = json_decode(file_get_contents(CACHE_PATH . 'vapid_keys.json'));

        $this->view->assign('base_host', BASE_HOST);
        $this->view->assign('base_uri', BASE_URI);
        $this->view->assign('small_picture_limit', SMALL_PICTURE_LIMIT);
        $this->view->assign('error_uri', $this->route('disconnect'));
        $this->view->assign('user', $this->user);
        $this->view->assign('vapid_public_key', $keys->publicKey);
    }
}
