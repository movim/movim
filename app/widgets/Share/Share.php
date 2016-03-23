<?php

use Respect\Validation\Validator;

class Share extends \Movim\Widget\Base
{
    function load()
    {
    }

    function display()
    {
        $validate_url = Validator::url();
        $url = rawurldecode(urldecode($this->get('url')));
        if($validate_url->validate($url)) {
            $this->view->assign('url', $url);
        }
    }
}
