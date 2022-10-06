<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Pubsub\Delete;
use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\Pubsub\SetAffiliations;
use Moxl\Xec\Action\Pubsub\GetSubscriptions;

use Respect\Validation\Validator;

class CommunityAffiliations extends Base
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

    public function onAffiliations($packet)
    {
        list($affiliations, $origin, $node) = array_values($packet->content);

        $role = null;

        if (array_key_exists('owner', $affiliations)) {
            foreach ($affiliations['owner'] as $r) {
                if ($r['jid'] == $this->user->id) {
                    $role = 'owner';
                }
            }
        }

        $view = $this->tpl();
        $view->assign('role', $role);
        $view->assign('info', \App\Info::where('server', $origin)
                                       ->where('node', $node)
                                       ->first());
        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('affiliations', $affiliations);
        $view->assign('rostersubscriptions', \App\Subscription::where('server', $origin)
                ->where('node', $node)
                ->where('public', true)
                ->whereIn('jid', function ($query) {
                    $query->from('rosters')
                          ->select('jid')
                          ->where('session_id', SESSION_ID);
                })
                ->get());
        $view->assign('allsubscriptionscount', \App\Subscription::where('server', $origin)
                ->where('node', $node)
                ->where('public', true)
                ->count());

        $this->rpc(
            'MovimTpl.fill',
            '#community_affiliation',
            $view->draw('_communityaffiliations')
        );

        // If the configuration is open, we fill it
        $view = $this->tpl();

        $caps = \App\Info::where('server', $origin)->first();

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
            $view->draw('_communityaffiliations_config_content')
        );
    }

    public function onAffiliationsSet($packet)
    {
        Toast::send($this->__('communityaffiliation.role_set'));
    }

    public function onSubscriptions($packet)
    {
        list($subscriptions, $origin, $node) = array_values($packet->content);

        $view = $this->tpl();

        $view->assign('subscriptions', \App\Subscription::where('server', $origin)
                ->where('node', $node)
                ->get());
        $view->assign('server', $origin);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_communityaffiliations_subscriptions'), true);
    }

    private function deleted($packet)
    {
        if ($packet->content['server'] != $this->user->id
        && substr($packet->content['node'], 0, 29) != 'urn:xmpp:microblog:0:comments') {
            Toast::send($this->__('communityaffiliation.deleted'));

            $this->rpc(
                'MovimUtils.redirect',
                $this->route(
                    'community',
                    [$packet->content['server']]
                )
            );
        }
    }

    public function onDelete($packet)
    {
        Toast::send($this->__('communityaffiliation.deleted'));

        $this->deleted($packet);
    }

    public function onDeleteError($packet)
    {
        $c = new CommunityHeader;
        $c->ajaxUnsubscribe($packet->content['server'], $packet->content['node']);

        $this->deleted($packet);
    }

    public function getContact($jid)
    {
        return \App\Contact::firstOrNew(['id' => $jid]);
    }

    public function ajaxShowFullPublicSubscriptionsList($origin, $node)
    {
        $view = $this->tpl();
        $view->assign('subscriptions', \App\Subscription::where('server', $origin)
                ->where('node', $node)
                ->where('public', true)
                ->get());

        Dialog::fill($view->draw('_communityaffiliations_public_subscriptions_dialog'), true);
    }

    public function ajaxGetAffiliations($origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $r = new GetAffiliations;
        $r->setTo($origin)->setNode($node)
          ->request();
    }

    public function ajaxGetSubscriptions($origin, $node, $notify = true)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $r = new GetSubscriptions;
        $r->setTo($origin)
          ->setNode($node)
          ->setNotify($notify)
          ->request();
    }

    public function ajaxDelete($origin, $node, $clean = false)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('clean', $clean);

        Dialog::fill($view->draw('_communityaffiliations_delete'));
    }

    public function ajaxDeleteConfirm($origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        (new CommunityHeader)->ajaxUnsubscribe($origin, $node);

        $d = new Delete;
        $d->setTo($origin)->setNode($node)
          ->request();
    }

    public function ajaxAffiliations($origin, $node)
    {
        $view = $this->tpl();
        $view->assign('server', $origin);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_communityaffiliations_config'));

        $this->ajaxGetAffiliations($origin, $node);
    }

    public function ajaxChangeAffiliation($origin, $node, $form)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $caps = \App\Info::where('server', $origin)->first();

        if (Validator::in($caps ? array_keys($caps->getPubsubRoles()) : [])->validate($form->role->value)
        && Validator::stringType()->length(2, 100)->validate($form->jid->value)) {
            $sa = new SetAffiliations;
            $sa->setTo($origin)
               ->setNode($node)
               ->setData([$form->jid->value => $form->role->value])
               ->request();
        }
    }

    public function preparePublicSubscriptionsList($subscriptions)
    {
        $view = $this->tpl();

        $sortedSubscriptions = collect();

        if ($this->user && $this->user->session) {
            $rosterJids = $this->user->session->contacts->pluck('jid')->toArray();

            foreach ($subscriptions as $subscription) {
                $subscription->setAttribute('in_roster', in_array($subscription->jid, $rosterJids));

                if ($subscription->in_roster) {
                    $sortedSubscriptions->prepend($subscription);
                } else {
                    $sortedSubscriptions->push($subscription);
                }
            }
        } else {
            $sortedSubscriptions = $subscriptions;
        }

        $view->assign('subscriptions', $sortedSubscriptions);

        return $view->draw('_communityaffiliations_public_subscriptions_list');
    }
}
