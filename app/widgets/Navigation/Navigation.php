<?php

class Navigation extends \Movim\Widget\Base
{
    function load()
    {
    }

    function display()
    {
        $this->view->assign('page', $this->_view);
    }
}
