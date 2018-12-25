<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Disco\Items;
use Respect\Validation\Validator;

class CommunitiesServers extends Base
{
    public function load()
    {
        $this->registerEvent('disco_items_handle', 'onDisco', 'community');
        $this->addjs('communitiesservers.js');
    }

    public function onDisco($packet)
    {
        $this->ajaxGet();
    }

    public function ajaxDisco($origin)
    {
        if (!$this->validateServer($origin)) {
            Notification::append(null, $this->__('communities.disco_error'));
            return;
        }

        $r = new Items;
        $r->setTo($origin)->request();
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
        $servers = \App\Info::where('category', 'pubsub')
                            ->where('type', 'service')
                            ->orderBy('occupants', 'desc')
                            ->get();

        $view = $this->tpl();
        $view->assign('servers', $servers);

        return $view->draw('_communitiesservers');
    }
}
