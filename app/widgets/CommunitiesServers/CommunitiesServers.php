<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Disco\Items;

class CommunitiesServers extends Base
{
    public function load()
    {
        $this->registerEvent('disco_items_manual', 'onDisco', 'community');
        $this->registerEvent('disco_items_manual_error', 'onDiscoError', 'community');
        $this->registerEvent('disco_request_handle', 'onDiscoInfo', 'community');
        $this->addjs('communitiesservers.js');
    }

    public function onDisco($packet)
    {
        Toast::send($this->__('communities.disco'));
        $this->ajaxHttpGet();
    }

    public function onDiscoInfo($packet)
    {
        $info = \App\Info::whereCategory('pubsub')
                 ->whereType('service')
                 ->where('server', $packet->content[0])
                 ->first();

        if ($info) {
            $this->ajaxHttpGet();
        }
    }

    public function onDiscoError($packet)
    {
        Toast::send($this->__('communities.disco_error'));
    }

    public function ajaxDisco($origin)
    {
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
        $configuration = \App\Configuration::get();

        $view = $this->tpl();
        $view->assign('servers', $servers);
        $view->assign('restrict', $configuration->restrictsuggestions);

        return $view->draw('_communitiesservers');
    }
}
