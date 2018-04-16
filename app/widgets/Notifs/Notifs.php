<?php

class Notifs extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('post', 'onNotifs', 'news');
    }

    function onNotifs()
    {
        $this->rpc('MovimTpl.fill', '#notifs', $this->prepareNotifs());
    }

    function ajaxClear()
    {
        \App\Cache::c('notifs_since', date(SQL_DATE));
        $this->onNotifs();
    }

    function prepareNotifs()
    {
        $view = $this->tpl();

        $since = \App\Cache::c('notifs_since');

        if (!$since) $since = date(SQL_DATE, 0);

        $emoji = \MovimEmoji::getInstance();

        $notifs = \App\Post::whereIn('parent_id', function ($query) use ($since) {
            $query->select('id')
                  ->from('posts')
                  ->where('aid', $this->user->id)
                  ->where('published', '>', $since);
        })
        ->orderBy('published', 'desc')
        ->limit(10)
        ->get();

        $view->assign('hearth',  $emoji->replace('♥'));
        $view->assign('notifs', $notifs);

        return $view->draw('_notifs', true);
    }
}
