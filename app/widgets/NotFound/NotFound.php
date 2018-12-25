<?php

use Movim\Widget\Base;

class NotFound extends Base
{
    public function load()
    {
        $this->addcss('notfound.css');
    }

    public function display()
    {
        $this->view->assign('base_uri', BASE_URI);
    }
}
