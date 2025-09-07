<?php

namespace App\Widgets\RoomsExplore;

use Moxl\Xec\Action\ExtendedChannelSearch\Search;
use Movim\Widget\Base;

use App\Contact;
use App\Widgets\Drawer\Drawer;

class RoomsExplore extends Base
{
    public function load()
    {
        $this->addjs('roomsexplore.js');
        $this->registerEvent('extendedchannelsearch_search_handle', 'onGlobalSearch', 'chat');
        $this->registerEvent('extendedchannelsearch_search_error', 'onGlobalSearchError', 'chat');
    }

    public function onGlobalSearch($packet)
    {
        $view = $this->tpl();

        $results = $packet->content['results'];
        $keys = [];

        foreach ($results as $result) {
            array_push($keys, $result['jid']);
        }

        $view->assign('vcards', Contact::whereIn('id', $keys)->get()->keyBy('id'));
        $view->assign(
            'bookmarks',
            $this->user->session
                ->conferences()
                ->whereIn('conference', $keys)
                ->get()
                ->keyBy('conference')
        );
        $view->assign('results', $results);
        $view->assign('global', $packet->content['global']);
        $view->assign('total', $packet->content['total']);
        $view->assign('keyword', $packet->content['keyword']);

        $this->rpc('MovimTpl.fill', '#roomsexplore_local', '');
        $this->rpc('MovimTpl.fill', '#roomsexplore_global', $view->draw('_roomsexplore_global'));
        $this->rpc('RoomsExplore.searchClear');
    }

    public function onGlobalSearchError($packet)
    {
        $this->rpc('MovimTpl.fill', '#roomsexplore_global', '');
        $this->searchLocally($packet->content);
    }

    /**
     * @brief Display the explore panel
     */
    public function ajaxSearch(?string $keyword = null)
    {
        $view = $this->tpl();
        Drawer::fill('search', $view->draw('_roomsexplore'), true);
        $this->rpc('RoomsExplore.init');
        $this->rpc('RoomsExplore_ajaxSearchRooms');

        if ($keyword) {
            $this->rpc('RoomsExplore.setKeyword', $keyword);
            $this->rpc('RoomsExplore.searchSomething', $keyword);
        }
    }

    /**
     * @brief search a keyword in the explore panel
     */
    public function ajaxSearchRooms(?string $keyword = null)
    {
        $configuration = \App\Configuration::get();

        if ($configuration->restrictsuggestions) {
            $this->searchLocally($keyword);
        } else {
            $s = new Search;
            $s->setKeyword($keyword)
                ->request();
        }
    }

    private function searchLocally($keyword = false)
    {
        $view = $this->tpl();
        $rooms = \App\Info::whereCategory('conference')
            ->restrictUserHost()
            ->restrictMucServices()
            ->whereType('text')
            ->where('mucpublic', true)
            ->where('mucpersistent', true)
            ->where('node', '')
            ->orderBy('occupants', 'desc');

        if ($keyword) {
            $rooms = $rooms->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('server', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        $rooms = $rooms->take(25)->get();

        $view->assign('vcards', Contact::whereIn('id', $rooms->pluck('server'))->get()->keyBy('id'));
        $view->assign('rooms', $rooms);
        $view->assign(
            'bookmarks',
            $this->user->session
                ->conferences()
                ->whereIn('conference', $rooms->pluck('server'))
                ->get()
                ->keyBy('conference')
        );

        $this->rpc('MovimTpl.fill', '#roomsexplore_local', $view->draw('_roomsexplore_local'));
        $this->rpc('RoomsExplore.searchClear');
    }
}
