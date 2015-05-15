<?php

use Respect\Validation\Validator;

class Share extends WidgetBase
{
    function load()
    {
    }

    function display()
    {
        $validate_url = Validator::url();
        $url = $this->get('url');
        if($validate_url->validate($url)) {
            $this->view->assign('url', $url);
        }
    }
}
