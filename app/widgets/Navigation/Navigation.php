<?php

class Navigation extends WidgetCommon
{
    function load()
    {
    }

    function display()
    {
        $this->view->assign('page', $this->_view);
    }
}
