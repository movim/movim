<?php

use Moxl\Xec\Action\Disco\Items;
use Respect\Validation\Validator;

class Communities extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('disco_items_handle', 'onDisco');
        $this->addjs('communities.js');
    }

    function onDisco($packet)
    {
        $this->ajaxGet();
    }

    function ajaxDisco($server)
    {
        if(!$this->validateServer($server)) {
            Notification::append(null, $this->__('communities.disco_error'));
            return;
        }

        $r = new Items;
        $r->setTo($server)->request();
    }

    function ajaxGet()
    {
        RPC::call('MovimTpl.fill', '#communities', $this->prepareCommunities());
    }

    function prepareCommunities()
    {
        $id = new \Modl\ItemDAO;
        $pd = new \Modl\PostnDAO;

        $view = $this->tpl();

        $view->assign('communities', $id->getItems(false, 0, 10));
        $view->assign('servers', $id->getGroupServers());

        return $view->draw('_communities', true);
    }

    /**
     * @brief Validate the server
     *
     * @param string $server
     */
    private function validateServer($server)
    {
        $validate_server = Validator::noWhitespace()->alnum('.-_')->length(6, 40);
        return ($validate_server->validate($server));
    }

    public function display()
    {
    }
}
