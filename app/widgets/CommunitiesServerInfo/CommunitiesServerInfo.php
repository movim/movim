<?php

class CommunitiesServerInfo extends \Movim\Widget\Base
{
    function load()
    {
    }

    function display()
    {
        $id = new \Modl\InfoDAO;
        $this->view->assign('info', $id->getJid($this->get('s')));
    }
}

