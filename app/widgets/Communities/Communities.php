<?php

use Moxl\Xec\Action\Disco\Items;
use Respect\Validation\Validator;

class Communities extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('disco_items_handle', 'onDisco', 'community');
        $this->addjs('communities.js');
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
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities());
    }

    function prepareCommunities()
    {
        $id = new \Modl\InfoDAO;

        $view = $this->tpl();

        $view->assign('communities', $id->getItems(false, 0, 10));
        $view->assign('servers', $id->getGroupServers());

        return $view->draw('_communities', true);
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

    public function display()
    {
    }
}
