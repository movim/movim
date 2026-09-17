<?php

namespace App\Widgets\SpaceRooms;

use App\Affiliation;
use App\Conference;
use App\Widgets\Rooms\Rooms;
use App\Widgets\SpacesMenu\SpacesMenu;
use Movim\Widget\Base;
use Moxl\Xec\Action\Muc\ChangeAffiliation;
use Moxl\Xec\Action\Muc\CreateGroupChat;
use Moxl\Xec\Action\Muc\Destroy;
use Moxl\Xec\Action\Muc\SetConfig;
use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Space\AddRoom;
use Moxl\Xec\Action\Space\DeleteRoom;
use Moxl\Xec\Payload\Packet;

class SpaceRooms extends Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations');
        $this->registerEvent('space_addroom_handle', 'onAffiliations');
        $this->registerEvent('space_deleteroom_handle', 'onAffiliations');
        $this->registerEvent('space_getrooms_handle', 'onRooms');
        $this->registerEvent('space_getrooms_erroritemnotfound', 'onNotFound');
        $this->registerEvent('space_addedroom', 'onEditedRooms');
        $this->registerEvent('space_deletedroom', 'onEditedRooms');
        $this->registerEvent('presence_muc_errorregistrationrequired', 'onRoomRegistrationRequired');

        // We have a memory filter optimisation in onMujiOrSFUPresence
        $this->registerEvent('presence', 'onMujiOrSFUPresence');
        $this->registerEvent('presence_muji', 'onMujiOrSFUPresence', 'space*');
        $this->registerEvent('presence_sfu', 'onMujiOrSFUPresence', 'space*');
        $this->registerEvent('presence_muc_muji_leaving', 'onMujiLeaving');

        $this->addcss('spacerooms.css');
        $this->addjs('spacerooms.js');
    }

    public function onAffiliations(Packet $packet)
    {
        list($server, $node) = array_values($packet->content);

        $this->ajaxHttpGet($server, $node);
    }

    public function onRoomRegistrationRequired(Packet $packet)
    {
        $this->rpc('MovimUtils.addClass', '#space' . cleanupId($packet->content), 'disabled');
    }

    public function onMujiLeaving(Packet $packet)
    {
        $this->rpc('MovimTpl.hidePanel');
    }

    public function onMujiOrSFUPresence(Packet $packet)
    {
        $sessionKey = 'muji_' . $packet->content->jid;
        $isMuji = linker($this->sessionId)->session->get($sessionKey);

        if ($isMuji === false) return;

        if (is_array($isMuji)) {
            $this->ajaxHttpGet($isMuji['server'], $isMuji['node']);
            return;
        }

        $conference = $this->me->session->conferences()
            ->fromSpace()
            ->where('conference', $packet->content->jid)
            ->first();

        if ($conference) {
            if ($conference->call) {
                /**
                 * We keep in memory the conference JID and filter on it to prevent DB queries each time we have a presence
                 */
                linker($this->sessionId)->session->set($sessionKey, [
                    'server' => $conference->space_server,
                    'node' => $conference->space_node
                ]);

                $this->ajaxHttpGet($conference->space_server, $conference->space_node);
            } else {
                linker($this->sessionId)->session->set($sessionKey, false);
            }
        }
    }

    public function onEditedRooms(Packet $packet)
    {
        $this->ajaxHttpGet($packet->content['server'], $packet->content['node']);
        $this->onRooms($packet);
    }

    public function onRooms(Packet $packet)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $packet->content['server'])
            ->where('node', $packet->content['node'])
            ->first();

        if ($subscription) {
            $roomWidget = new Rooms(user: $this->me, sessionId: $this->sessionId);

            foreach ($subscription->spaceRooms as $room) {
                if ($room->autojoin && !$room->connected) {
                    $roomWidget->ajaxJoin($room->conference, $room->nick);
                }
            }
        }
    }

    /**
     * The Space node is gone we remove it from the subscriptions
     */
    public function onNotFound(Packet $packet)
    {
        (new SpacesMenu(user: $this->me, sessionId: $this->sessionId))->ajaxRemoveSubscription(
            $packet->content['server'],
            $packet->content['node']
        );
    }

    public function ajaxHttpGetChat(string $server, string $node, ?string $id = null)
    {
        if ($id) {
            $this->rpc('Chat.getRoom', $id);
            return;
        }

        $subscription = $this->me->subscriptions()->space($server, $node)->first();

        if ($subscription && $firstRoom = $subscription->spaceRooms()->first()) {
            $this->rpc('Chat.getRoom', $firstRoom->conference);
            return;
        }

        $this->rpc('MovimTpl.fill', '#chat_widget', $this->view('_spacerooms_empty', [
            'subscription' => $subscription
        ]));
    }

    public function ajaxHttpGet(string $server, string $node)
    {
        $subscription = $this->me->subscriptions()->space($server, $node)->first();

        if (!$subscription) return;

        $affiliation = Affiliation::where('server', $server)
            ->where('node', $node)
            ->where('jid', $this->me->id)
            ->first();

        $this->rpc('MovimTpl.fill', '#spacerooms_widget', $this->view('_spacerooms', [
            'subscription' => $subscription,
            'edit' => ($affiliation && $affiliation->affiliation == 'owner'),
            'addplaceholder' => __('chatrooms.first_room_placeholder', '<i class="material-symbols">rule</i>')
        ]));
        $this->rpc('SpaceRooms.init');
    }

    public function ajaxAdd(string $server, string $node)
    {
        $affiliation = Affiliation::where('server', $server)
            ->where('node', $node)
            ->where('jid', $this->me->id)
            ->first();

        if ($affiliation->affiliation == 'owner') {
            $this->dialog($this->view('_spacerooms_add', [
                'server' => $server,
                'node' => $node,
            ]));
        }
    }

    public function ajaxAskEdit(string $conferenceId)
    {
        $conference = $this->me->session
            ->conferences()
            ->where('conference', $conferenceId)
            ->first();

        if ($conference && $conference->isFromSpace()) {
            $affiliation = Affiliation::where('server', $conference->space_server)
                ->where('node', $conference->space_node)
                ->where('jid', $this->me->id)
                ->first();

            if ($affiliation->affiliation == 'owner') {
                $this->dialog($this->view('_spacerooms_edit', [
                    'conference' => $conference
                ]));
            }
        }
    }

    public function ajaxEdit(\stdClass $form)
    {
        $subscription = $this->me->subscriptions()
            ->spaces()
            ->where('server', $form->server->value)
            ->where('node', $form->node->value)
            ->first();

        if ($subscription && $conference = $subscription->spaceRooms()->where('conference', $form->conference->value)->first()) {
            $conference->name = $form->name->value;
            $conference->pinned = (bool)$form->pinned->value;
            $conference->autojoin = true;

            $sc = $this->xmpp(new SetConfig);
            $sc->setTo($form->conference->value)
                ->setData([
                    'muc#roomconfig_roomname' => $form->name->value,
                    'muc#roomconfig_pubsub' => $subscription->uri,

                    // Just in case, we resend Group Chat configuration
                    'muc#roomconfig_persistentroom' => 'true',
                    'muc#roomconfig_changesubject' => 'false',
                    'muc#roomconfig_membersonly' => 'true',
                    'muc#roomconfig_whois' => 'anyone',
                    'muc#roomconfig_publicroom' => 'false',
                ])
                ->request();

            $b = $this->xmpp(new AddRoom);
            $b->setConference($conference)
                ->request();
        }
    }

    public function ajaxAddCreate(\stdClass $form)
    {
        if (empty($form->name->value)) {
            $this->toast($this->__('chatrooms.empty_name'));
            return;
        }

        $this->rpc('Dialog.clear');

        $id = generateUUID() . '@' . $this->me->session->getChatroomsServices()->first()->server;

        // Send the presence
        $m = $this->xmpp(new Muc);
        $m->noNotify()
            ->setTo($id)
            ->setNickname($this->me->username)
            ->request();

        // Configure the MUC
        $cgc = $this->xmpp(new CreateGroupChat);
        $cgc->setTo($id)
            ->setName($form->name->value)
            ->setPinned($form->pinned->value)
            ->setNick($this->me->username)
            ->setNotify(false)
            ->setPubsubnode('xmpp:' . $form->server->value . '?;node=' . $form->node->value)
            ->request();

        // Publish the item in the Space
        $conference = new Conference;
        $conference->space_server = $form->server->value;
        $conference->space_node = $form->node->value;
        $conference->conference = $id;
        $conference->name = $form->name->value;
        $conference->pinned = (bool)$form->pinned->value;
        $conference->autojoin = true;
        $conference->call = (bool)$form->call->value;

        $b = $this->xmpp(new AddRoom);
        $b->setConference($conference)
            ->request();

        // Map all the affiliations from Pubsub to MUC
        $affiliations = Affiliation::where('server', $form->server->value)
            ->where('node', $form->node->value)
            ->get();

        foreach ($affiliations as $affiliation) {
            $changeAffiliation = $this->xmpp(new ChangeAffiliation);
            $changeAffiliation->setTo($id)
                ->setJid($affiliation->jid)
                ->setAffiliation($affiliation->affiliation)
                ->request();
        }
    }

    public function ajaxAskDestroy(string $server, string $node, string $id)
    {
        $subscription = $this->me->subscriptions()->space($server, $node)->first();

        if ($subscription && $conference = $subscription->spaceRooms()->where('conference', $id)->first()) {
            $this->dialog($this->view('_spacerooms_destroy', [
                'conference' => $conference,
                'server' => $server,
                'node' => $node,
            ]));
        }
    }

    public function ajaxDestroy(string $server, string $node, string $id)
    {
        $destroy = $this->xmpp(new Destroy);
        $destroy->setTo($id)
            ->request();

        $roomDelete = $this->xmpp(new DeleteRoom);
        $roomDelete->setTo($server)
            ->setNode($node)
            ->setId($id)
            ->request();
    }

    public function prepareRoomCounter(Conference $conference)
    {
        return (new Rooms(user: $this->me, sessionId: $this->sessionId))
            ->prepareRoomCounter($conference, withAvatar: true);
    }
}
