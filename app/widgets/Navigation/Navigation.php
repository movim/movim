<?php

use Movim\Widget\Base;

class Navigation extends Base
{
    public function display()
    {
        $this->view->assign('page', $this->_view);
    }
}
