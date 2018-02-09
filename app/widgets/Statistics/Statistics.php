<?php

use Modl\SessionxDAO;

class Statistics extends \Movim\Widget\Base
{
    function load()
    {
    }

    public function getContact($username, $host)
    {
        $jid = $username.'@'.$host;
        $cd = new modl\ContactDAO;
        return $cd->get($jid);
    }

    function getTime($date)
    {
        return prepareDate(strtotime($date));
    }

    function display()
    {
        $sd = new SessionxDAO;
        $this->view->assign('sessions', $sd->getAll());
    }
}
