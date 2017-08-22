<?php

use Respect\Validation\Validator;

class Communities extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('communities.js');
    }

    function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities());
    }

    function prepareCommunities()
    {
        $id = new \Modl\InfoDAO;

        $view = $this->tpl();

        $view->assign('communities', $id->getItems(false, 0, 30));
        $view->assign('servers', $id->getGroupServers());

        return $view->draw('_communities', true);
    }
}
