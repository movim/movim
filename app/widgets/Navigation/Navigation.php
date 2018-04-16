<?php

class Navigation extends \Movim\Widget\Base
{
    function display()
    {
        $this->view->assign('page', $this->_view);
    }
}
