<?php

use Moxl\Xec\Action\Disco\Items;
use Respect\Validation\Validator;

class CommunitiesServers extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('disco_items_handle', 'onDisco', 'community');
        $this->addjs('communitiesservers.js');
    }

    function onDisco($packet)
    {
        $this->ajaxGet();
    }

    function ajaxDisco($origin)
    {
        if(!$this->validateServer($origin)) {
            Notification::append(null, $this->__('communities.disco_error'));
            return;
        }

        $r = new Items;
        $r->setTo($origin)->request();
    }

    function ajaxGet()
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

    function prepareCommunities()
    {
        $id = new \Modl\InfoDAO;

        $view = $this->tpl();
        $view->assign('servers', $id->getGroupServers());

        return $view->draw('_communitiesservers', true);
    }
}
