<?php

namespace App\Widgets\SpaceRooms;

use App\Affiliation;
use App\Conference;
use App\Widgets\Rooms\Rooms;
use App\Widgets\SpacesMenu\SpacesMenu;
use Movim\Jid;
use Movim\Widget\Base;
use Moxl\Xec\Action\Muc\CreateGroupChat;
use Moxl\Xec\Action\Muc\Destroy;
use Moxl\Xec\Action\Muc\SetAffiliations;
use Moxl\Xec\Action\Muc\SetConfig;
use Moxl\Xec\Action\Muc\SetSubject;
use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Space\DeleteRoom;
use Moxl\Xec\Action\Space\SetDirectories;
use Moxl\Xec\Action\Space\SetRoom;
use Moxl\Xec\Payload\Packet;

class SpaceRooms extends Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getaffiliations_handle', 'onAffiliations');
        $this->registerEvent('space_setroom_handle', 'onSetRoom');
        $this->registerEvent('space_deleteroom_handle', 'onAffiliations');
        $this->registerEvent('space_getrooms_handle', 'onRooms');
        $this->registerEvent('space_getrooms_erroritemnotfound', 'onNotFound');
        $this->registerEvent('space_addedroom', 'onEditedRooms');
        $this->registerEvent('space_deletedroom', 'onEditedRooms');
        $this->registerEvent('space_setdirectories_handle', 'onDirectories');
        $this->registerEvent('space_directories', 'onDirectories');
        $this->registerEvent('presence_muc_errorregistrationrequired', 'onRoomRegistrationRequired');

        // We have a memory filter optimisation in onMujiOrSFUPresence
        $this->registerEvent('presence', 'onMujiOrSFUPresence');
        $this->registerEvent('presence_muji', 'onMujiOrSFUPresence', 'space*');
        $this->registerEvent('presence_sfu', 'onMujiOrSFUPresence', 'space*');
        $this->registerEvent('presence_muc_muji_leaving', 'onMujiLeaving');

        $this->addcss('spacerooms.css');
        $this->addjs('spacerooms.js');
    }

    public function onSetRoom(Packet $packet)
    {
        $this->toast($this->__('spaceinfo.edited_room'));
        $this->onAffiliations(packet: $packet);
    }

    public function onAffiliations(Packet $packet)
    {
        [$server, $node] = array_values($packet->content);

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

        if ($isMuji === false) {
            return;
        }

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
                    'node' => $conference->space_node,
                ]);

                $this->ajaxHttpGet($conference->space_server, $conference->space_node);
            } else {
                linker($this->sessionId)->session->set($sessionKey, false);
            }
        }
    }

    public function onDirectories(Packet $packet)
    {
        $this->toast($this->__('spaceinfo.edited_directories'));
        $this->ajaxHttpGet($packet->content['server'], $packet->content['node']);
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
                if ($room->autojoin && ! $room->connected) {
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
            'subscription' => $subscription,
        ]));
    }

    public function ajaxHttpGet(string $server, string $node)
    {
        $subscription = $this->me->subscriptions()->space($server, $node)->first();

        if (! $subscription) {
            return;
        }

        $affiliation = Affiliation::where('server', $server)
            ->where('node', $node)
            ->where('jid', $this->me->id)
            ->first();

        $directories = $this->me->session->bookmarksDirectories()
            ->where('server', $server)
            ->where('node', $node)
            ->orderBy('order')
            ->get()
            ->keyBy('id');

        $this->rpc('MovimTpl.fill', '#spacerooms_widget', $this->view('_spacerooms', [
            'space_rooms' => $subscription->spaceRooms->sortBy(function ($conference) use ($directories) {
                $directoriesKeys = $directories->keys();
                $directoriesKeys->prepend('null');

                return $directoriesKeys->flip()->get($conference->directory_id ?? 'null', $directories->count());
            }),
            'directories' => $directories,
            'edit' => ($affiliation && $affiliation->affiliation == 'owner'),
            'addplaceholder' => __('chatrooms.first_room_placeholder', '<i class="material-symbols">rule</i>'),
        ]));
        $this->rpc('SpaceRooms.init');

        if ($affiliation && $affiliation->affiliation == 'owner') {
            $this->rpc('SpaceRooms.editable');
        }
    }

    public function ajaxAskAdd(string $server, string $node, ?string $directoryId = null)
    {
        $affiliation = Affiliation::where('server', $server)
            ->where('node', $node)
            ->where('jid', $this->me->id)
            ->first();

        if ($affiliation->affiliation == 'owner') {
            $this->dialog($this->view('_spacerooms_add', [
                'server' => $server,
                'node' => $node,
                'directory' => $directoryId
                    ? $this->me->session->bookmarksDirectories()
                        ->where('server', $server)
                        ->where('node', $node)
                        ->where('id', $directoryId)
                        ->first()
                    : null,
            ]));
        }
    }

    public function ajaxAdd(\stdClass $form)
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

        $config = [
            'muc#roomconfig_pubsub' => 'xmpp:' . $form->server->value . '?;node=' . $form->node->value,
        ];

        if ($info = resolveServiceServerInfo((new Jid($id))->domain)) {
            match ($info->name) {
                'ejabberd' => $config += ['mam' => 'true'],
                'Prosody' => $config += ['muc#roomconfig_enablearchiving' => 'true'],
            };
        }

        // Configure the MUC
        $cgc = $this->xmpp(new CreateGroupChat);
        $cgc->setTo($id)
            ->setName($form->name->value)
            ->setPinned($form->pinned->value)
            ->setNick($this->me->username)
            ->setNotify(false)
            ->setExtraConfig($config)
            ->request();

        // Publish the item in the Space
        $conference = new Conference;
        $conference->space_server = $form->server->value;
        $conference->space_node = $form->node->value;
        $conference->conference = $id;
        $conference->name = $form->name->value;
        $conference->pinned = (bool) $form->pinned->value;
        $conference->autojoin = true;
        $conference->call = (bool) $form->call->value;

        if (
            $form->directory_id
            && $directory = $this->me->session->bookmarksDirectories()
                ->where('server', $form->server->value)
                ->where('node', $form->node->value)
                ->where('id', $form->directory_id->value)->first()
        ) {
            $conference->weight = (float) $directory->conferences()->count();
            $conference->directory_id = $directory->id;
        }

        $b = $this->xmpp(new SetRoom);
        $b->setConference($conference)
            ->request();

        // Map all the affiliations from Pubsub to MUC
        $affiliations = Affiliation::where('server', $form->server->value)
            ->where('node', $form->node->value)
            ->pluck('affiliation', 'jid')
            ->toArray();

        $changeAffiliation = $this->xmpp(new SetAffiliations);
        $changeAffiliation->setTo($id)
            ->setAffiliations($affiliations)
            ->request();
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
                    'conference' => $conference,
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
            $conference->pinned = (bool) $form->pinned->value;
            $conference->autojoin = true;

            $config = [
                'muc#roomconfig_roomname' => $form->name->value,
                'muc#roomconfig_pubsub' => $subscription->uri,

                // Just in case, we resend Group Chat configuration
                'muc#roomconfig_persistentroom' => 'true',
                'muc#roomconfig_changesubject' => 'false',
                'muc#roomconfig_membersonly' => 'true',
                'muc#roomconfig_whois' => 'anyone',
                'muc#roomconfig_publicroom' => 'false',
            ];

            if ($info = resolveServiceServerInfo((new Jid($form->conference->value))->domain)) {
                match ($info->name) {
                    'ejabberd' => $config += ['mam' => 'true'],
                    'Prosody' => $config += ['muc#roomconfig_enablearchiving' => 'true'],
                };
            }

            $sc = $this->xmpp(new SetConfig);
            $sc->setTo($form->conference->value)
                ->setData($config)
                ->request();

            $b = $this->xmpp(new SetRoom);
            $b->setConference($conference)
                ->request();

            $p = $this->xmpp(new SetSubject);
            $p->setTo($form->conference->value)
                ->setSubject($form->subject->value)
                ->request();
        }
    }

    public function ajaxSetHierarchy(string $server, string $node, string $id, float $weight, ?string $directoryId = null)
    {
        $subscription = $this->me->subscriptions()->space($server, $node)->first();

        if ($subscription && $conference = $subscription->spaceRooms()->where('conference', $id)->first()) {
            $conference->weight = $weight;

            if ($directoryId == null || $this->me->session->bookmarksDirectories()
                ->where('server', $server)
                ->where('node', $node)
                ->where('id', $directoryId)
                ->exists()
            ) {
                $conference->directory_id = $directoryId;
            }

            $b = $this->xmpp(new SetRoom);
            $b->setConference($conference)
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

    public function ajaxAskAddDirectory(string $server, string $node)
    {
        $affiliation = Affiliation::where('server', $server)
            ->where('node', $node)
            ->where('jid', $this->me->id)
            ->first();

        if ($affiliation->affiliation == 'owner') {
            $this->dialog($this->view('_spacerooms_add_directory', [
                'server' => $server,
                'node' => $node,
            ]));
        }
    }

    public function ajaxAddDirectory(\stdClass $form)
    {
        if (empty($form->title->value)) {
            $this->toast($this->__('chatrooms.empty_name'));

            return;
        }

        $this->rpc('Dialog.clear');

        $directories = $this->me->session->bookmarksDirectories()
            ->where('server', $form->server->value)
            ->where('node', $form->node->value)
            ->pluck('title', 'id');

        $directories->put(generateUUID(), $form->title->value);

        $setDirectories = $this->xmpp(new SetDirectories);
        $setDirectories->setTo($form->server->value)
            ->setNode($form->node->value)
            ->setDirectories($directories)
            ->request();
    }

    public function ajaxAskEditDirectory(string $server, string $node, string $directoryId)
    {
        $affiliation = Affiliation::where('server', $server)
            ->where('node', $node)
            ->where('jid', $this->me->id)
            ->first();

        if (
            $affiliation->affiliation == 'owner'
            && $directory = $this->me->session->bookmarksDirectories()
                ->where('server', $server)
                ->where('node', $node)
                ->where('id', $directoryId)
                ->first()
        ) {
            $this->dialog($this->view('_spacerooms_edit_directory', [
                'directory' => $directory,
            ]));
        }
    }

    public function ajaxEditDirectory(\stdClass $form)
    {
        $directories = $this->me->session->bookmarksDirectories()
            ->where('server', $form->server->value)
            ->where('node', $form->node->value)
            ->pluck('title', 'id');

        if ($directories->has($form->directory_id->value)) {
            $directories->put($form->directory_id->value, $form->title->value);

            $setDirectories = $this->xmpp(new SetDirectories);
            $setDirectories->setTo($form->server->value)
                ->setNode($form->node->value)
                ->setDirectories($directories)
                ->request();
        }
    }

    public function ajaxAskDestroyDirectory(string $server, string $node, string $directoryId)
    {
        $affiliation = Affiliation::where('server', $server)
            ->where('node', $node)
            ->where('jid', $this->me->id)
            ->first();

        if (
            $affiliation->affiliation == 'owner'
            && $directory = $this->me->session->bookmarksDirectories()
                ->where('server', $server)
                ->where('node', $node)
                ->where('id', $directoryId)
                ->withCount('conferences')
                ->first()
        ) {
            $this->dialog($this->view('_spacerooms_destroy_directory', [
                'directory' => $directory,
            ]));
        }
    }

    public function ajaxDestroyDirectory(\stdClass $form)
    {
        $directories = $this->me->session->bookmarksDirectories()
            ->where('server', $form->server->value)
            ->where('node', $form->node->value)
            ->get();

        if ($directories->contains('id', $form->directory_id->value)) {
            if ($directory = $directories->firstWhere('id', $form->directory_id->value)) {
                foreach ($directory->conferences as $conference) {
                    $conference->directory_id = null;

                    $b = $this->xmpp(new SetRoom);
                    $b->setConference($conference)
                        ->request();
                }

                $directories = $directories->whereNotIn('id', [$form->directory_id->value]);

                $setDirectories = $this->xmpp(new SetDirectories);
                $setDirectories->setTo($form->server->value)
                    ->setNode($form->node->value)
                    ->setDirectories($directories->pluck('title', 'id'))
                    ->request();
            }
        }
    }

    public function ajaxSortDirectories(string $server, string $node, array $ids)
    {
        $directories = $this->me->session->bookmarksDirectories()
            ->where('server', $server)
            ->where('node', $node)
            ->pluck('title', 'id');

        if (collect($ids)->diff($directories->keys())->isEmpty()) {
            $directories = $directories->sortBy(function ($value, $key) use ($ids) {
                return array_search($key, $ids);
            });

            $setDirectories = $this->xmpp(new SetDirectories);
            $setDirectories->setTo($server)
                ->setNode($node)
                ->setDirectories($directories)
                ->request();
        }
    }

    public function prepareRoomCounter(Conference $conference)
    {
        return (new Rooms(user: $this->me, sessionId: $this->sessionId))
            ->prepareRoomCounter($conference, withAvatar: true);
    }
}
