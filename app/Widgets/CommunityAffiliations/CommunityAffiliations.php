<?php

namespace App\Widgets\CommunityAffiliations;

use App\Affiliation;
use App\Post;
use App\Widgets\CommunityHeader\CommunityHeader;
use App\Widgets\Dialog\Dialog;
use Movim\Widget\Base;

use Moxl\Xec\Action\Pubsub\Delete;
use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\Pubsub\SetAffiliations;
use Moxl\Xec\Action\Pubsub\GetSubscriptions;
use Moxl\Xec\Payload\Packet;
use Respect\Validation\Validator;

class CommunityAffiliations extends Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations');
        $this->registerEvent('pubsub_getaffiliations_error', 'onAffiliations');
        $this->registerEvent('pubsub_setaffiliations_handle', 'onAffiliationsSet');
        $this->registerEvent('pubsub_delete_handle', 'onDelete');
        $this->registerEvent('pubsub_delete_error', 'onDeleteError');
        $this->registerEvent('pubsub_getsubscriptions_handle', 'onSubscriptions');

        $this->addjs('communityaffiliations.js');
    }

    public function onAffiliations(Packet $packet)
    {
        list($server, $node) = array_values($packet->content);

        $affiliations = Affiliation::where('server', $server)
            ->where('node', $node)
            ->get();

        $infoServer = \App\Info::where('server', $server)->where('node', '')->first();

        $view = $this->tpl();
        $view->assign('myaffiliation', $affiliations->where('jid', $this->me->id)->first());
        $view->assign('info', \App\Info::where('server', $server)
            ->where('node', $node)
            ->first());
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('affiliations', $affiliations);
        $view->assign('rostersubscriptions', \App\Subscription::where('server', $server)
            ->where('node', $node)
            ->where('public', true)
            ->whereIn('jid', function ($query) {
                $query->from('rosters')
                    ->select('jid')
                    ->where('session_id', SESSION_ID);
            })
            ->get());
        $view->assign('allsubscriptionscount', \App\Subscription::where('server', $server)
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

        $view->assign('subscriptions', \App\Subscription::where('server', $server)
            ->where('node', $node)
            ->get());
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('affiliations', $affiliations);
        $view->assign('me', $this->me->id);
        $view->assign('roles', ($infoServer) ? $infoServer->getPubsubRoles() : []);

        $this->rpc(
            'MovimTpl.fill',
            '#community_affiliations_config',
            $view->draw('_communityaffiliations_config_content')
        );
    }

    public function onAffiliationsSet(Packet $packet)
    {
        $this->toast($this->__('communityaffiliation.role_set'));
    }

    public function onSubscriptions(Packet $packet)
    {
        list($subscriptions, $server, $node) = array_values($packet->content);

        $view = $this->tpl();

        $view->assign('subscriptions', \App\Subscription::where('server', $server)
            ->where('node', $node)
            ->get());
        $view->assign('server', $server);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_communityaffiliations_subscriptions'), true);
    }

    private function deleted(Packet $packet)
    {
        if (
            $packet->content['server'] != $this->me->id
            && str_starts_with($packet->content['node'], Post::COMMENTS_NODE)
        ) {
            $this->rpc(
                'MovimUtils.redirect',
                $this->route(
                    'community',
                    [$packet->content['server']]
                )
            );
        }
    }

    public function onDelete(Packet $packet)
    {
        $this->toast($this->__('communityaffiliation.deleted'));

        $this->deleted($packet);
    }

    public function onDeleteError(Packet $packet)
    {
        $this->toast($this->__('communityaffiliation.delete_error'));

        $c = new CommunityHeader;
        $c->ajaxUnsubscribe($packet->content['server'], $packet->content['node']);

        $this->deleted($packet);
    }

    public function getContact($jid)
    {
        return \App\Contact::firstOrNew(['id' => $jid]);
    }

    public function ajaxShowFullPublicSubscriptionsList(string $server, string $node)
    {
        $view = $this->tpl();
        $view->assign('subscriptions', \App\Subscription::where('server', $server)
            ->where('node', $node)
            ->where('public', true)
            ->get());

        Dialog::fill($view->draw('_communityaffiliations_public_subscriptions_dialog'), true);
    }

    public function ajaxGetAffiliations(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $r = new GetAffiliations;
        $r->setTo($server)->setNode($node)
            ->request();
    }

    public function ajaxGetSubscriptions(string $server, string $node, $notify = true)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $r = new GetSubscriptions;
        $r->setTo($server)
            ->setNode($node)
            ->setNotify($notify)
            ->request();
    }

    public function ajaxDelete(string $server, string $node, $clean = false)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('clean', $clean);

        Dialog::fill($view->draw('_communityaffiliations_delete'));
    }

    public function ajaxDeleteConfirm(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        (new CommunityHeader)->ajaxUnsubscribe($server, $node);

        $d = new Delete;
        $d->setTo($server)->setNode($node)
            ->request();
    }

    public function ajaxAffiliations(string $server, string $node)
    {
        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_communityaffiliations_config'));

        $this->ajaxGetAffiliations($server, $node);
    }

    public function ajaxChangeAffiliation(string $server, string $node, $form)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $caps = \App\Info::where('server', $server)->where('node', '')->first();

        if (
            Validator::in($caps ? array_keys($caps->getPubsubRoles()) : [])->isValid($form->role->value)
            && Validator::stringType()->length(2, 100)->isValid($form->jid->value)
        ) {
            $sa = new SetAffiliations;
            $sa->setTo($server)
                ->setNode($node)
                ->setData([$form->jid->value => $form->role->value])
                ->request();
        }
    }

    public function preparePublicSubscriptionsList($subscriptions)
    {
        $view = $this->tpl();

        $sortedSubscriptions = collect();

        if ($this->me && $this->me->session) {
            $rosterJids = $this->me->session->contacts->pluck('jid')->toArray();

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
