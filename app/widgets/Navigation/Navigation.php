<?php

class Navigation extends WidgetBase
{
    function load()
    {
    }

    function display()
    {
        $this->view->assign('page', $this->_view);
    }
}
