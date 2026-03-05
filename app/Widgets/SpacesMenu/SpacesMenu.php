<?php

namespace App\Widgets\SpacesMenu;

use App\Contact;
use App\Info;
use App\Subscription;
use Movim\Widget\Base;
use Movim\XMPPUri;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Muc\ChangeAffiliation;
use Moxl\Xec\Action\Pubsub\SetAffiliations;
use Moxl\Xec\Action\Pubsub\Unsubscribe;
use Moxl\Xec\Action\PubsubSubscription\Add;
use Moxl\Xec\Action\PubsubSubscription\Get as GetPubsubSubscriptions;
use Moxl\Xec\Action\PubsubSubscription\Remove;
use Moxl\Xec\Action\Space\Create;
use Moxl\Xec\Action\Space\GetRooms;
use Moxl\Xec\Action\Space\SetSubscription;
use Moxl\Xec\Action\Space\Subscribe;
use Moxl\Xec\Payload\Packet;

class SpacesMenu extends Base
{
    public function load()
    {
        $this->registerEvent('space_create_handle', 'onSpaceCreated');
        $this->registerEvent('space_subscribe_handle', 'onSubscribed');
        $this->registerEvent('pubsubsubscription_add_handle', 'onPubsubSubscription');
        $this->registerEvent('pubsubsubscription_remove_handle', 'onPubsubSubscription');
        $this->registerEvent('pubsubsubscription_get_handle', 'onPubsubSubscription');
        $this->registerEvent('pubsub_getitem_avatar', 'onPubsubAvatar');
        $this->registerEvent('space_getrooms_handle', 'onRooms');
        $this->registerEvent('space_subscribe_errorclosednode', 'onClosedNode');
        $this->registerEvent('space_subscribe_erroritemnotfound', 'onItemNotFound');
        $this->registerEvent('space_subscribe_errorpendingsubscription', 'onPendingSubscription');
        $this->registerEvent('space_setsubscription_handle', 'onSetSubscription');
        $this->registerEvent('message_pubsub_subscribed', 'onMessageSubscribed');

        $this->addjs('spacesmenu.js');
        $this->addcss('spacesmenu.css');
    }

    public function onClosedNode(Packet $packet)
    {
        $this->toast(__('spacesmenu.locked_text'));
    }

    public function onPendingSubscription(Packet $packet)
    {
        $this->rpc('MovimTpl.replace', '#locked_try_again', $this->view('_spacesmenu_pending'));
    }

    public function onItemNotFound(Packet $packet)
    {
        $this->ajaxLeave($packet->content['server'], $packet->content['node']);
    }

    public function onSetSubscription(Packet $packet)
    {
        if ($packet->content['subscription'] == 'subscribed') {
            $this->toast($this->__('spacesmenu.contact_added'));
        } elseif ($packet->content['subscription'] == 'subscribed') {
            $this->toast($this->__('spacesmenu.contact_denied'));
        }
    }

    public function onPubsubAvatar(Packet $packet)
    {
        if ($info = Info::where('server', $packet->content['server'])
            ->where('node', $packet->content['node'])
            ->first()
        ) {
            if ($info->type == 'urn:xmpp:spaces:0') {
                $this->ajaxHttpGet();
            }
        }
    }

    public function onMessageSubscribed(Packet $packet)
    {
        $this->onRooms($packet);

        $roomsGet = $this->xmpp(new GetRooms);
        $roomsGet->setTo($packet->content['server'])
            ->setNode($packet->content['node'])
            ->request();
    }

    public function onRooms(Packet $packet)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $packet->content['server'])
            ->where('node', $packet->content['node'])
            ->first();

        if ($subscription) {
            $subscription->space_in = true;
            $subscription->save();
            $this->ajaxHttpGet();
        }
    }

    public function onSubscribed(Packet $packet)
    {
        $this->toast($this->__('spacesmenu.subscribed'));
        $this->ajaxAddSubscription($packet->content['server'], $packet->content['node']);
    }

    public function onPubsubSubscription(Packet $packet)
    {
        if ($packet->content['type'] == Subscription::SPACE_NODE) {
            foreach ($this->me->subscriptions()->spaces()->get() as $space) {
                if (!$space->info) {
                    $this->ajaxGetSpaceInfo($space->server, $space->node);
                }

                $roomsGet = $this->xmpp(new GetRooms);
                $roomsGet->setTo($space->server)
                    ->setNode($space->node)
                    ->request();
            }

            $this->ajaxHttpGet();
        }
    }

    public function ajaxGetRoom(string $server, string $node)
    {
        $roomsGet = $this->xmpp(new GetRooms);
        $roomsGet->setTo($server)
            ->setNode($node)
            ->request();
    }

    public function onSpaceCreated(Packet $packet)
    {
        $this->toast($this->__('spacesmenu.space_created'));
        $this->ajaxGetSpaceInfo($packet->content['server'], $packet->content['node']);

        $setSubscription = $this->xmpp(new SetSubscription);
        $setSubscription->setTo($packet->content['server'])
            ->setNode($packet->content['node'])
            ->setJid($this->me->id)
            ->setSubscription('subscribed')
            ->request();

        $this->ajaxJoin($packet->content['server'], $packet->content['node']);
    }

    public function ajaxManageInvitation(string $server, string $node, string $jid)
    {
        $this->dialog($this->view('_spacesmenu_manage_invitation', [
            'server' => $server,
            'node' => $node,
            'info' => Info::space()->where('server', $server)->where('node', $node)->first(),
            'contact' => Contact::firstOrNew(['id' => $jid])
        ]));
    }

    public function ajaxAcceptInvitation(string $server, string $node, string $jid)
    {
        $setSubscription = $this->xmpp(new SetSubscription);
        $setSubscription->setTo($server)
            ->setNode($node)
            ->setJid($jid)
            ->setSubscription('subscribed')
            ->request();

        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $server)
            ->where('node', $node)
            ->first();

        if ($subscription) {
            $setNodeAffiliation = $this->xmpp(new SetAffiliations);
            $setNodeAffiliation->setTo($server)
                ->setNode($node)
                ->setData([$jid => 'member'])
                ->request();

            foreach ($subscription->spaceRooms as $conference) {
                $changeAffiliation = $this->xmpp(new ChangeAffiliation);
                $changeAffiliation->setTo($conference->conference)
                    ->setJid($jid)
                    ->setAffiliation('member')
                    ->request();
            }
        }
    }

    public function ajaxDenyInvitation(string $server, string $node, string $jid)
    {
        $setSubscription = $this->xmpp(new SetSubscription);
        $setSubscription->setTo($server)
            ->setNode($node)
            ->setJid($jid)
            ->setSubscription('none')
            ->request();
    }

    public function ajaxJoinFromUri(\stdClass $form)
    {
        if (empty($form->uri->value)) {
            $this->toast($this->__('spacesmenu.key_required'));
            return;
        }

        $uri = new XMPPUri($form->uri->value);
        if ($uri->getType() == 'community') {
            $this->ajaxAddSubscription($uri->getServer(), $uri->getNode());
            $this->ajaxJoin($uri->getServer(), $uri->getNode());
        }
    }

    public function ajaxJoin(string $server, string $node)
    {
        $subscribe = $this->xmpp(new Subscribe);
        $subscribe->setTo($server)
            ->setNode($node)
            ->setFrom($this->me->id)
            ->request();
    }

    public function ajaxLeave(string $server, string $node)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $server)
            ->where('node', $node)
            ->first();

        if ($subscription) {
            $subscribe = $this->xmpp(new Unsubscribe);
            $subscribe->setTo($server)
                ->setNode($node)
                ->setFrom($this->me->id)
                ->setSubid($subscription->subid)
                ->request();

            $this->ajaxRemoveSubscription($server, $node);
            $this->rpc('MovimUtils.redirect', $this->route('chat'));
        }
    }

    public function ajaxAddSubscription(string $server, string $node)
    {
        $sa = $this->xmpp(new Add);
        $sa->setServer($server)
            ->setNode($node)
            ->setFrom($this->me->id)
            ->setPEPNode(Subscription::SPACE_NODE)
            ->request();
    }

    public function ajaxRemoveSubscription(string $server, string $node)
    {
        $sa = $this->xmpp(new Remove);
        $sa->setServer($server)
            ->setNode($node)
            ->setFrom($this->me->id)
            ->setPEPNode(Subscription::SPACE_NODE)
            ->request();
    }

    /*
    public function ajaxGetSpacesSubscription()
    {
        $ps = $this->xmpp(new GetPubsubSubscriptions);
        $ps->setTo($this->me->id)
            ->setPEPNode(Subscription::SPACE_NODE)
            ->request();
    }*/

    public function ajaxGetSpaceInfo(string $server, string $node)
    {
        $request = $this->xmpp(new Request);
        $request->setTo($server)
            ->setNode($node)
            ->request();
    }

    public function ajaxHttpGet(?string $server = null, ?string $node = null, ?bool $isMobile = false)
    {
        $this->rpc('MovimTpl.fill', '#spacesmenu_widget', $this->prepareMenu($server, $node));
        $this->rpc('Notif_ajaxGet', false);

        if ($server && $node) {
            $this->rpc('MovimUtils.pushSoftState', $this->route('space', [$server, $node]));
            $this->rpc('SpaceRooms_ajaxHttpGet', $server, $node);
            $this->rpc('SpaceInfo_ajaxHttpGet', $server, $node);

            if (!$isMobile) {
                $this->rpc('SpaceRooms_ajaxHttpGetChat', $server, $node);
            }
        }

    }

    public function ajaxAdd()
    {
        $this->dialog($this->view('_spacesmenu_add'));
    }

    public function ajaxCreate()
    {
        $this->dialog($this->view('_spacesmenu_create'));
    }

    public function ajaxCreateConfirm(\stdClass $form)
    {
        if (empty($form->title->value)) {
            $this->toast($this->__('spacesmenu.space_title_empty'));
            return;
        }

        $spaceCreate = $this->xmpp(new Create);
        $spaceCreate->setTo($this->me->session->getSpacesService()->server)
            ->setNode(generateKey(8))
            ->setTitle($form->title->value)
            ->request();

        $this->rpc('Dialog_ajaxClear');
    }

    public function ajaxLockedMenu(string $server, string $node)
    {
        $this->dialog($this->view('_spacesmenu_locked', [
            'server' => $server,
            'node' => $node
        ]));
    }

    public function ajaxLeaveMenu(string $server, string $node)
    {
        $this->dialog($this->view('_spacesmenu_leave', [
            'server' => $server,
            'node' => $node
        ]));
    }

    public function prepareMenu(?string $server = null, ?string $node = null): string
    {
        return $this->view('_spacesmenu', [
            'string' => $server,
            'node' => $node,
            'spaces' => $this->me->subscriptions()->spaces()->orderBy('pinned', 'desc')->with('info')->get()
        ]);
    }
}
