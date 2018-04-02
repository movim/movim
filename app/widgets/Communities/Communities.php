<?php

use Respect\Validation\Validator;
use App\Configuration;
use Movim\Widget\Base;

class Communities extends Base
{
    public function load()
    {
        $this->addjs('communities.js');
        $this->addcss('communities.css');
    }

    function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities());
    }

    function prepareCommunities()
    {
        /*

        $view = $this->tpl();
        $view->assign('communities', $id->getItems(
            false,
            0,
            40,
            true, (Configuration::findOrNew(1)->restrictsuggestions)
                ? $this->user->getServer()
                : false
        ));

        return $view->draw('_communities', true);*/
    }

    function getLastPublic($server, $node)
    {
        $pd = new \Modl\PostnDAO;
        return $pd->getPublic($server, $node, 0, 1)[0];
    }
}
