<?php

class BottomNavigation extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('bottomnavigation.css');
    }

    function display()
    {
        $this->view->assign('page', $this->_view);
    }
}
