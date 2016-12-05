<?php

use Moxl\Xec\Action\Pubsub\Delete;

use Respect\Validation\Validator;

class CommunityAffiliations extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations');
        $this->registerEvent('pubsub_delete_handle', 'onDelete');
        $this->registerEvent('pubsub_delete_error', 'onDeleteError');
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

        $this->rpc('MovimTpl.fill', '#community_affiliation', $view->draw('_communityaffiliations', true));
    }

    private function deleted($packet)
    {
        if($packet->content['server'] != $this->user->getLogin()
        && substr($packet->content['node'], 0, 29) != 'urn:xmpp:microblog:0:comments') {
            Notification::append(null, $this->__('groups.deleted'));

            $this->rpc('MovimUtils.redirect',
                $this->route('community',
                    [$packet->content['server']]
                )
            );
        }
    }

    function onDelete($packet)
    {
        Notification::append(null, $this->__('groups.deleted'));

        $this->deleted($packet);
    }

    function onDeleteError($packet)
    {
        $m = new Rooms;
        $m->setBookmark();

        $this->deleted($packet);
    }

    function ajaxDelete($server, $node, $clean = false)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('clean', $clean);

        Dialog::fill($view->draw('_communityaffiliations_delete', true));
    }

    function ajaxDeleteConfirm($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $d = new Delete;
        $d->setTo($server)->setNode($node)
          ->request();
    }

    private function validateServerNode($server, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        if(!$validate_server->validate($server)
        || !$validate_node->validate($node)
        ) return false;
        else return true;
    }

    public function display()
    {
    }
}
