<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Disco\Items;
use Respect\Validation\Validator;

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
        Notification::toast($this->__('communities.disco'));
        $this->ajaxGet();
    }

    public function onDiscoInfo($packet)
    {
        $info = \App\Info::whereCategory('pubsub')
                 ->whereType('service')
                 ->where('server', $packet->content[0])
                 ->first();

        if ($info) {
            $this->ajaxGet();
        }
    }

    public function onDiscoError($packet)
    {
        Notification::toast($this->__('communities.disco_error'));
    }

    public function ajaxDisco($origin)
    {
        if (!$this->validateServer($origin)) {
            Notification::toast($this->__('communities.disco_error'));
            return;
        }

        $r = new Request;
        $r->setTo($origin)->request();

        $r = new Items;
        $r->enableManual()
          ->setTo($origin)
          ->request();
    }

    public function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#communities_servers', $this->prepareCommunities());
    }

    /**
     * @brief Validate the server
     *
     * @param string $origin
     */
    private function validateServer($origin)
    {
        $validate_server = Validator::noWhitespace()->alnum('.-_')->length(6, 40);
        return ($validate_server->validate($origin));
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
