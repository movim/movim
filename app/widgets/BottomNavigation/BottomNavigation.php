<?php

use Movim\Widget\Base;

class BottomNavigation extends Base
{
    public function load()
    {
        $this->addcss('bottomnavigation.css');
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
    }
}
