<?php

use Moxl\Xec\Action\Pubsub\Delete;

use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\Pubsub\SetAffiliations;
use Moxl\Xec\Action\Pubsub\GetSubscriptions;

use Respect\Validation\Validator;

class CommunityAffiliations extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations');
        $this->registerEvent('disco_request_affiliations', 'onAffiliations');
        $this->registerEvent('pubsub_setaffiliations_handle', 'onAffiliationsSet');
        $this->registerEvent('pubsub_delete_handle', 'onDelete');
        $this->registerEvent('pubsub_delete_error', 'onDeleteError');

        $this->registerEvent('pubsub_getsubscriptions_handle', 'onSubscriptions');

        $this->addjs('communityaffiliations.js');
    }

    function onAffiliations($packet)
    {
        list($affiliations, $origin, $node) = array_values($packet->content);

        $role = null;

        foreach($affiliations['owner'] as $r) {
            if ($r['jid'] == $this->user->id) {
                $role = 'owner';
            }
        }

        $view = $this->tpl();
        $view->assign('role', $role);
        $view->assign('info', \App\Info::where('server', $origin)
                                       ->where('node', $node)
                                       ->first());
        $view->assign('affiliations', $affiliations);
        $view->assign('subscriptions', \App\Subscription::where('server', $origin)
                ->where('node', $node)
                ->where('public', true)
                ->get());

        $this->rpc(
            'MovimTpl.fill',
            '#community_affiliation',
            $view->draw('_communityaffiliations', true)
        );

        // If the configuration is open, we fill it
        $view = $this->tpl();

        $caps = App\Capability::find($origin);

        $view->assign('subscriptions', \App\Subscription::where('server', $origin)
                ->where('node', $node)
                ->get());
        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('affiliations', $affiliations);
        $view->assign('me', $this->user->id);
        $view->assign('roles', ($caps) ? $caps->getPubsubRoles() : []);

        $this->rpc(
            'MovimTpl.fill',
            '#community_affiliations_config',
            $view->draw('_communityaffiliations_config_content', true)
        );
    }

    function onAffiliationsSet($packet)
    {
        Notification::append(null, $this->__('communityaffiliation.role_set'));
    }

    function onSubscriptions($packet)
    {
        list($subscriptions, $origin, $node) = array_values($packet->content);

        $view = $this->tpl();

        $view->assign('subscriptions', \App\Subscription::where('server', $origin)
                ->where('node', $node)
                ->get());
        $view->assign('server', $origin);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_communityaffiliations_subscriptions', true), true);
    }

    private function deleted($packet)
    {
        if ($packet->content['server'] != $this->user->id
        && substr($packet->content['node'], 0, 29) != 'urn:xmpp:microblog:0:comments') {
            Notification::append(null, $this->__('communityaffiliation.deleted'));

            $this->rpc('MovimUtils.redirect',
                $this->route('community',
                    [$packet->content['server']]
                )
            );
        }
    }

    function onDelete($packet)
    {
        Notification::append(null, $this->__('communityaffiliation.deleted'));

        $this->deleted($packet);
    }

    function onDeleteError($packet)
    {
        $m = new Rooms;
        $m->setBookmark();

        $this->deleted($packet);
    }

    function getContact($jid)
    {
        return \App\Contact::firstOrNew(['id' => $jid]);
    }

    function ajaxGetAffiliations($origin, $node)
    {
        if (!$this->validateServerNode($origin, $node)) return;

        $r = new GetAffiliations;
        $r->setTo($origin)->setNode($node)
          ->request();
    }

    function ajaxGetSubscriptions($origin, $node, $notify = true)
    {
        if (!$this->validateServerNode($origin, $node)) return;

        $r = new GetSubscriptions;
        $r->setTo($origin)
          ->setNode($node)
          ->setNotify($notify)
          ->request();
    }

    function ajaxDelete($origin, $node, $clean = false)
    {
        if (!$this->validateServerNode($origin, $node)) return;

        $view = $this->tpl();
        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('clean', $clean);

        Dialog::fill($view->draw('_communityaffiliations_delete', true));
    }

    function ajaxDeleteConfirm($origin, $node)
    {
        if (!$this->validateServerNode($origin, $node)) return;

        (new CommunityHeader)->ajaxUnsubscribe($origin, $node);

        $d = new Delete;
        $d->setTo($origin)->setNode($node)
          ->request();
    }

    function ajaxAffiliations($origin, $node)
    {
        $view = $this->tpl();
        $view->assign('server', $origin);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_communityaffiliations_config', true));

        $this->ajaxGetAffiliations($origin, $node);
    }

    function ajaxChangeAffiliation($origin, $node, $form)
    {
        if (!$this->validateServerNode($origin, $node)) return;

        if (Validator::in(\App\Capability::find($origin)->getPubsubRoles())->validate($form->role->value)
        && Validator::stringType()->length(3, 100)->validate($form->jid->value)) {
            $sa = new SetAffiliations;
            $sa->setTo($origin)
               ->setNode($node)
               ->setData([$form->jid->value => $form->role->value])
               ->request();
        }
    }

    private function validateServerNode($origin, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        return ($validate_server->validate($origin)
             && $validate_node->validate($node));
    }
}
