<?php

class Discover extends WidgetCommon {
    function load()
    {

    }

    function display()
    {
        $cd = new \modl\ContactDAO();
        $users = $cd->getAllPublic();
        $this->view->assign('users', array_reverse($users));
    }
}
