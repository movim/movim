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
        if (!$this->validateServer($origin)) {
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

    /**
     * @brief Validate the server
     *
     * @param string $origin
     */
    private function validateServer($origin)
    {
        $validateServer = Validator::noWhitespace()->alnum('.-_')->length(6, 40);
        return ($validateServer->validate($origin));
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
