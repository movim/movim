<?php

use Moxl\Xec\Action\Muclumbus\Search;
use Movim\Widget\Base;

class RoomsExplore extends Base
{
    public function load()
    {
        $this->addjs('roomsexplore.js');
        $this->registerEvent('muclumbus_search_handle', 'onGlobalSearch', 'chat');
    }

    public function onGlobalSearch($packet)
    {
        $view = $this->tpl();
        $view->assign('results', $packet->content);

        $this->rpc('MovimTpl.fill', '#roomsexplore_global', $view->draw('_roomsexplore_global'));
    }

    /**
     * @brief Display the explore panel
     */
    public function ajaxSearch()
    {
        $view = $this->tpl();
        Drawer::fill($view->draw('_roomsexplore'), true);
        $this->rpc('RoomsExplore.init');
        $this->rpc('RoomsExplore_ajaxSearchRooms');
    }

    /**
     * @brief search a keyword in the explore panel
     */
    public function ajaxSearchRooms($key = false)
    {
        $view = $this->tpl();
        $rooms = \App\Info::whereCategory('conference')
                         ->whereType('text')
                         ->where('mucpublic', true)
                         ->where('mucpersistent', true)
                         ->where('node', '')
                         ->orderBy('occupants', 'desc');

        if ($key) {
            $rooms = $rooms->where(function($query) use ($key) {
                $query->where('name', 'like', '%'.$key.'%')
                      ->orWhere('server', 'like', '%'.$key.'%')
                      ->orWhere('description', 'like', '%'.$key.'%');
            });
        }

        $view->assign('rooms', $rooms->take(5)->get());
        $this->rpc('MovimTpl.fill', '#roomsexplore_local', $view->draw('_roomsexplore_local'));

        $configuration = \App\Configuration::get();

        if (!$configuration->restrictsuggestions) {
            $s = new Search;
            $s->setKeyword($key)
              ->request();
        }
    }
}