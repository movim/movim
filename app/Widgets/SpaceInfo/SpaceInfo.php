<?php

namespace App\Widgets\SpaceInfo;

use App\Affiliation;
use App\Conference;
use App\Subscription;
use App\Widgets\SpacesMenu\SpacesMenu;
use Movim\Image;
use Movim\Librairies\XMPPtoForm;
use Movim\Widget\Base;
use Moxl\Xec\Action\Avatar\Set as AvatarSet;
use Moxl\Xec\Action\Muc\ChangeAffiliation;
use Moxl\Xec\Action\Muc\Destroy;
use Moxl\Xec\Action\Pubsub\GetAffiliations;
use Moxl\Xec\Action\PubsubSubscription\Add;
use Moxl\Xec\Action\Space\Destroy as SpaceDestroy;
use Moxl\Xec\Action\Space\GetConfig;
use Moxl\Xec\Action\Space\GetPendingSubscriptions;
use Moxl\Xec\Action\Space\SetAffiliations;
use Moxl\Xec\Action\Space\SetConfig;
use Moxl\Xec\Payload\Packet;

class SpaceInfo extends Base
{
    public function load()
    {
        $this->registerEvent('pubsubsubscription_add_handle', 'onPubsubSubscriptionAdd', 'space*');
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations', 'space*');
        $this->registerEvent('pubsub_getaffiliations_errorforbidden', 'onAffiliationsForbidden', ['chat', 'space*']);
        $this->registerEvent('space_getconfig_handle', 'onConfig');
        $this->registerEvent('space_setconfig_handle', 'onConfigSaved');
        $this->registerEvent('space_setconfig_error', 'onConfigError');
        $this->registerEvent('space_destroy_handle', 'onDestroy');
        $this->registerEvent('space_getpendings_handle', 'onPendings');
        $this->registerEvent('space_setaffiliations_handle', 'onSetAffiliations');
        $this->addjs('spaceinfo.js');
        $this->addcss('spaceinfo.css');
    }

    public function onPubsubSubscriptionAdd(Packet $packet)
    {
        $this->toast(__('spaceinfo.config_saved'));
    }

    public function onDestroy(Packet $packet)
    {
        (new SpacesMenu(user: $this->me, sessionId: $this->sessionId))->ajaxRemoveSubscription(
            $packet->content['server'],
            $packet->content['node']
        );

        // Check if we get the events to cleanup the DB, for now redirect
        $this->rpc('MovimUtils.redirect', $this->route('chat'));
    }

    public function onAffiliations(Packet $packet)
    {
        list($server, $node) = array_values($packet->content);

        $affiliation = Affiliation::where('server', $server)
            ->where('node', $node)
            ->where('jid', $this->me->id)
            ->first();

        if ($affiliation && $affiliation->affiliation == 'owner') {
            $this->ajaxHttpGet($server, $node, edit: true);
            $this->ajaxGetPendings($server, $node);

            $this->rpc('MovimTpl.fill', '#spaceinfo_affiliations', $this->view('_spaceinfo_affiliations', [
                'affiliations' => Affiliation::where('server', $server)
                    ->where('node', $node)
                    ->orderBy('affiliation', 'desc')
                    ->with('contact')
                    ->get()
            ]));
        }
    }

    public function onSetAffiliations(Packet $packet)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $packet->content['server'])
            ->where('node', $packet->content['node'])
            ->first();

        if ($subscription) {
            foreach ($subscription->spaceRooms as $conference) {
                foreach ($packet->content['data'] as $jid => $affiliation) {
                    $changeAffiliation = $this->xmpp(new ChangeAffiliation);
                    $changeAffiliation->setTo($conference->conference)
                        ->setJid($jid)
                        ->setAffiliation($affiliation)
                        ->request();
                }
            }
        }
    }

    public function onAffiliationsForbidden(Packet $packet)
    {
        $this->ajaxEditMember($packet->content['server'], $packet->content['node']);
    }

    public function onPendings(Packet $packet)
    {
        $subscriptions = $packet->content['subscriptions'];
        $pendings = $subscriptions->filter(function ($value) {
            return $value['subscription'] == 'pending';
        });

        $this->rpc('MovimUtils.replace', '#spaceinfo_pendings', $this->view('_spaceinfo_pendings', [
            'pendings' => $pendings
        ]));
    }

    public function onConfigSaved(Packet $packet)
    {
        $this->toast($this->__('spaceinfo.config_saved'));
        $this->ajaxHttpGet($packet->content['server'], $packet->content['node'], edit: true);

        (new SpacesMenu(user: $this->me, sessionId: $this->sessionId))
            ->ajaxGetSpaceInfo($packet->content['server'], $packet->content['node']);
    }

    public function onConfigError(Packet $packet)
    {
        $this->toast(
            $packet->content ??
                $this->__('spaceinfo.config_error')
        );
    }

    public function onConfig(Packet $packet)
    {
        list($server, $node, $config) = array_values($packet->content);

        $view = $this->tpl();

        $xml = new XMPPtoForm($this->me);
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('config', $xml->getArray($config->x));
        $view->assign('attributes', $config->attributes());

        $this->drawer('spaceinfo_config', $view->draw('_spaceinfo_config'), tiny: true);
        $this->rpc('MovimUtils.applyAutoheight');

        $this->ajaxGetAffiliations($server, $node);
    }

    public function ajaxEditMember(string $server, string $node)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $server)
            ->where('node', $node)
            ->first();

        if ($subscription) {
            $this->dialog($this->view('_spaceinfo_member', [
                'affiliation' => Affiliation::where('server', $server)
                    ->where('node', $node)
                    ->where('jid', $this->me->id)
                    ->first(),
                'subscription' => $subscription
            ]));
        }
    }

    public function ajaxHttpGet(string $server, string $node, ?bool $edit = false)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $server)
            ->where('node', $node)
            ->first();

        if ($subscription && $subscription->info) {
            $this->rpc('MovimTpl.fill', '#spaceinfo_widget', $this->view('_spaceinfo', [
                'subscription' => $subscription,
                'edit' => $edit
            ]));
        }
    }

    public function ajaxChangeConfiguration(string $server, string $node, \stdClass $data)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $server)
            ->where('node', $node)
            ->first();

        if (
            $subscription
            && in_array($data->notify->value, Conference::NOTIFICATIONS)
            && is_bool($data->pinned->value)
        ) {
            $setConfiguration = $this->xmpp(new Add);
            $setConfiguration->setServer($subscription->server)
                ->setNode($subscription->node)
                ->setFrom($this->me->id)
                ->setPEPNode(Subscription::SPACE_NODE)
                ->setExtensionsXML($subscription->extensions)
                ->setNotify(array_search($data->notify->value, Conference::NOTIFICATIONS))
                ->setPinned($data->pinned->value)
                ->request();
        }
    }

    public function ajaxGetPendings(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $r = $this->xmpp(new GetPendingSubscriptions);
        $r->setTo($server)
            ->setNode($node)
            ->request();
    }

    public function ajaxGetAffiliations(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $r = $this->xmpp(new GetAffiliations);
        $r->setTo($server)->setNode($node)
            ->request();
    }

    public function ajaxGetConfig(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $r = $this->xmpp(new GetConfig);
        $r->setTo($server)
            ->setNode($node)
            ->request();
    }

    public function ajaxSetConfig(string $server, string $node, \stdClass $data)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $r = $this->xmpp(new SetConfig);
        $r->setTo($server)
            ->setNode($node)
            ->setData(formToArray($data))
            ->request();
    }

    public function ajaxGetAvatar(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $this->dialog($this->view('_spaceinfo_avatar', [
            'info' => \App\Info::where('server', $server)
                ->where('node', $node)
                ->first()
        ]));
    }

    public function ajaxSetAvatar(string $server, string $node, \stdClass $form)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $key = $server . $node . 'avatar';

        $p = new Image;
        $p->fromBase64($form->photobin->value);
        $p->setKey($key);
        $p->save(false, false, 'jpeg', 60);

        // Reload the freshly compressed picture
        $p->load('jpeg');

        $r = $this->xmpp(new AvatarSet);
        $r->setTo($server)
            ->setNode($node)
            ->setUrl(Image::getOrCreate($key, false, false, 'jpeg', true))
            ->setData($p->toBase())
            ->request();
    }

    public function ajaxAskDestroy(string $server, string $node)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $server)
            ->where('node', $node)
            ->first();

        if ($subscription) {
            $this->dialog($this->view('_spaceinfo_destroy', [
                'subscription' => $subscription,
                'server' => $server,
                'node' => $node,
            ]));
        }
    }

    public function ajaxDestroy(string $server, string $node)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $server)
            ->where('node', $node)
            ->first();

        if ($subscription) {
            foreach ($subscription->spaceRooms as $conference) {
                $destroy = $this->xmpp(new Destroy);
                $destroy->setTo($conference->conference)
                    ->request();
            }

            $destroySpace = $this->xmpp(new SpaceDestroy);
            $destroySpace->setTo($server)
                ->setNode($node)
                ->request();
        }
    }

    public function ajaxSetAffiliations(string $server, string $node, \stdClass $data)
    {
        $currentAffiliations = Affiliation::where('server', $server)
            ->where('node', $node)
            ->get()
            ->pluck('affiliation', 'jid');

        $affiliations = [];
        foreach ($data as $key => $input) {
            if (
                $currentAffiliations->has($key)
                && $currentAffiliations->get($key) != $input->value
            ) {
                $affiliations[$key] = $input->value;
            }
        }

        $setAffiliation = $this->xmpp(new SetAffiliations);
        $setAffiliation->setTo($server)
            ->setNode($node)
            ->setData($affiliations)
            ->request();
    }

    public function ajaxInvite(string $server, string $node)
    {
        if (!validateServerNode($server, $node)) {
            return;
        }

        $this->dialog($this->view('_spaceinfo_invite', [
            'subscription' => $this->me->subscriptions()
                ->spaces()
                ->where('server', $server)
                ->where('node', $node)
                ->first()
        ]));
    }
}
