<?php

namespace App\Widgets\CommunitiesServers;

use App\Widgets\Dialog\Dialog;
use App\Widgets\Toast\Toast;
use Movim\Widget\Base;

use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Disco\Items;
use Moxl\Xec\Payload\Packet;

class CommunitiesServers extends Base
{
    public function load()
    {
        $this->registerEvent('disco_items_manual', 'onDisco', 'explore');
        $this->registerEvent('disco_items_manual_error', 'onDiscoError', 'explore');
        $this->registerEvent('disco_items_errorremoteservernotfound', 'onDiscoNotFound', 'explore');
        $this->registerEvent('disco_request_handle', 'onDiscoInfo', 'explore');
        $this->addjs('communitiesservers.js');
    }

    public function onDisco(Packet $packet)
    {
        Toast::send($this->__('communities.disco'));
        $this->ajaxHttpGet();
        $this->rpc('Dialog_ajaxClear');
    }

    public function onDiscoInfo(Packet $packet)
    {
        if ($packet->content->isPubsubService()) {
            $this->ajaxHttpGet();
        }
    }

    public function onDiscoError(Packet $packet)
    {
        Toast::send($this->__('communities.disco_error'));
    }

    public function onDiscoNotFound(Packet $packet)
    {
        Toast::send($this->__('page.not_found'));
    }

    public function ajaxDiscoverServer()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_communitiesservers_discover_server'));
    }

    public function ajaxDisco($form)
    {
        $origin = $form->server->value;

        if (!validateServer($origin)) {
            Toast::send($this->__('communities.disco_error'));
            return;
        }

        $r = new Request;
        $r->setTo($origin)->request();

        $r = new Items;
        $r->enableManual()
            ->setTo($origin)
            ->request();
    }

    public function ajaxHttpGet()
    {
        $this->rpc('MovimTpl.fill', '#communities_servers', $this->prepareCommunities());
    }

    public function prepareCommunities()
    {
        $servers = \App\Info::whereCategory('pubsub')
            ->whereType('service')
            ->restrictUserHost()
            ->orderBy('occupants', 'desc')
            ->get();

        $view = $this->tpl();
        $view->assign('servers', $servers);

        return $view->draw('_communitiesservers');
    }
}
