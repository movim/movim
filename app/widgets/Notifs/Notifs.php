<?php

class Notifs extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('post', 'onNotifs');
    }

    function onNotifs()
    {
        $this->rpc('MovimTpl.fill', '#notifs', $this->prepareNotifs());
    }

    function ajaxClear()
    {
        \Movim\Cache::c('notifs_since', date(\Modl\SQL::SQL_DATE));
        $this->onNotifs();
    }

    function prepareNotifs()
    {
        $view = $this->tpl();

        $pd = new \Modl\PostnDAO;
        $since = \Movim\Cache::c('notifs_since');

        if(!$since) $since = date(\Modl\SQL::SQL_DATE, 0);

        $emoji = \MovimEmoji::getInstance();

        $view->assign('hearth',  $emoji->replace('♥'));
        $view->assign('notifs', $pd->getNotifsSince(
            $since,
            0, 6)
        );

        return $view->draw('_notifs', true);
    }

    function display()
    {

    }
}
