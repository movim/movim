<?php

use Respect\Validation\Validator;

class CommunityAffiliations extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations');
    }

    function onAffiliations($packet)
    {
        list($affiliations, $server, $node) = array_values($packet->content);

        $role = null;

        foreach($affiliations as $r) {
            if($r[0] == $this->user->getLogin())
                $role = (string)$r[1];
        }

        $id = new \Modl\ItemDAO;
        $item = $id->getItem($server, $node);

        $view = $this->tpl();
        $view->assign('role', $role);
        $view->assign('item', $item);

        RPC::call('MovimTpl.fill', '#community_affiliation', $view->draw('_communityaffiliations', true));
    }

    public function display()
    {
    }
}
