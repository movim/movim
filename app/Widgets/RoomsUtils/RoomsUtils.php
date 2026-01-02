<?php

namespace App\Widgets\RoomsUtils;

use Moxl\Xec\Action\Vcard\Set as VcardSet;
use Moxl\Xec\Action\Vcard4\Get as VcardGet;
use Moxl\Xec\Action\Message\Invite;
use Moxl\Xec\Action\Muc\SetSubject;
use Moxl\Xec\Action\Muc\Destroy;
use Moxl\Xec\Action\Muc\CreateChannel;
use Moxl\Xec\Action\Muc\CreateGroupChat;
use Moxl\Xec\Action\Disco\Items;
use Moxl\Xec\Action\Bookmark2\Set;
use Moxl\Xec\Action\Bookmark2\Delete;
use Moxl\Xec\Payload\Packet;

use Movim\Widget\Base;
use Movim\Image;

use App\Conference;
use App\Contact;
use App\Info;
use App\Message;
use App\Widgets\AdHoc\AdHoc;
use App\Widgets\Chat\Chat;
use App\Widgets\Chats\Chats;
use App\Widgets\ContactActions\ContactActions;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Drawer\Drawer;
use Respect\Validation\Validator;
use Movim\Session;
use Moxl\Xec\Action\Muc\ChangeAffiliation;
use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Presence\Unavailable;

use Illuminate\Database\Capsule\Manager as DB;
use Moxl\Xec\Action\Muc\SetRole;
use Moxl\Xec\Action\Register\Remove;

class RoomsUtils extends Base
{
    private $_picturesPagination = 20;
    private $_linksPagination = 12;

    public function load()
    {
        $this->registerEvent('vcard_set_handle', 'onAvatarSet', 'chat');
        $this->registerEvent('vcard4_get_handle', 'onVcard', 'chat');
        $this->registerEvent('disco_items_nosave_handle', 'onDiscoGateway');
        $this->registerEvent('disco_items_nosave_error', 'onDiscoGatewayError');
        $this->registerEvent('disco_items_errorregistrationrequired', 'onDiscoRegistrationRequired');
        $this->registerEvent('muc_creategroupchat_handle', 'onChatroomCreated');
        $this->registerEvent('muc_createchannel_handle', 'onChatroomCreated');
        $this->registerEvent('muc_creategroupchat_error', 'onChatroomCreatedError');
        $this->registerEvent('muc_createchannel_error', 'onChatroomCreatedError');
        $this->registerEvent('muc_changeaffiliation_handle', 'onAffiliationChanged');
        $this->registerEvent('muc_changeaffiliation_errornotallowed', 'onAffiliationChangeUnauthorized');
        $this->registerEvent('muc_setrole_handle', 'onSetRole');
        $this->registerEvent('message_invite_error', 'onInviteError');

        $this->registerEvent('presence_muc_create_handle', 'onMucCreated');
        $this->registerEvent('presence_muc_errornotallowed', 'onPresenceMucNotAllowed');
        $this->registerEvent('presence_muc_errorgone', 'onPresenceMucNotAllowed');

        $this->addjs('roomsutils.js');
        $this->addcss('roomsutils.css');
    }

    public function ajaxGetDrawer($room = false)
    {
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $picturesCount = $conference->pictures()->count();
        $linksCount = $conference->links()->count();

        $presences = $conference->presences()->with('capability')->get();

        $view = $this->tpl();
        $view->assign('conference', $conference);
        $view->assign('room', $room);
        $view->assign('picturesCount', $picturesCount);
        $view->assign('linksCount', $linksCount);
        $view->assign('presences', $presences);

        if ($conference->isGroupChat()) {
            $view->assign('members', $conference->activeMembers()
                ->whereNotIn('jid', $presences->pluck('mucjid'))
                ->with('contact')
                ->get());
        }

        $view->assign('banned', $conference->members()
            ->with('contact')
            ->where('affiliation', '=', 'outcast')
            ->get());

        $view->assign('me', $this->me->id);

        Drawer::fill('room_drawer', $view->draw('_rooms_drawer'));
        $this->rpc('Tabs.create');

        $this->rpc('RoomsUtils_ajaxAppendPresences', $room, !$conference->isGroupChat(), 0);

        if ($picturesCount > 0) {
            $this->rpc('RoomsUtils_ajaxHttpGetPictures', $room);
        }

        if ($linksCount > 0) {
            $this->rpc('RoomsUtils_ajaxHttpGetLinks', $room);
        }

        if ($this->me->hasOMEMO() && $conference->isGroupChat()) {
            $this->rpc(
                'RoomsUtils.getDrawerFingerprints',
                $room,
                $conference->members()->whereNot('jid', $this->me->id)->get()->pluck('jid')->toArray()
            );
        } else {
            $this->rpc('MovimTpl.fill', '#room_omemo_fingerprints', $this->view('_rooms_drawer_fingerprints_placeholder'));
        }

        (new AdHoc)->ajaxGet($room);
    }

    public function ajaxAppendPresences(string $room, bool $havePagination = true, int $page = 0)
    {
        $this->rpc('MovimTpl.remove', '#room_presences_more');
        $this->rpc('MovimTpl.append', '#room_presences_list', $this->preparePresences($room, $havePagination, $page));
    }

    public function preparePresences(string $room, bool $havePagination = true, int $page = 0, ?bool $compact = false)
    {
        $pagination = 20;

        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $presences = $conference->presences()
            ->with('capability');

        if ($page > 0) {
            $presences = $presences->skip($page * $pagination);
        }

        if ($havePagination) {
            $presences = $presences->take($pagination + 1);
        }

        $presences = $presences->get();

        if ($havePagination && $presences->count() == $pagination + 1) {
            $presences->pop();
        }

        if (!$havePagination) {
            $ownerFilter = fn($p) => $p->mucaffiliation == 'owner';
            $owners = $presences->reject($ownerFilter);
            $presences = $presences->filter($ownerFilter)->union($owners);
        }

        return $this->view('_rooms_presences_list', [
            'more' => $havePagination,
            'conference' => $conference,
            'presences' => $presences,
            'page' => $page + 1,
            'compact' => $compact
        ]);
    }

    public function ajaxGetDrawerFingerprints($room, $contactsFingerprints)
    {
        $resolvedFingerprints = collect();

        foreach ($contactsFingerprints as $contactFingerprints) {
            if (!empty($contactFingerprints)) {
                foreach ($contactFingerprints as $fingerprint) {
                    $fingerprint->fingerprint = base64ToFingerPrint($fingerprint->fingerprint);
                }

                $resolvedFingerprints->put($contactFingerprints[0]->jid, $contactFingerprints);
            }
        }

        $this->rpc('MovimTpl.fill', '#room_omemo_fingerprints', $this->view('_rooms_drawer_fingerprints', [
            'fingerprints' => $resolvedFingerprints,
            'clienttype' => getClientTypes(),
            'contacts' => Contact::whereIn('id', $resolvedFingerprints->keys())->get()->keyBy('id'),
        ]));

        foreach ($resolvedFingerprints as $jid => $value) {
            $this->rpc('ContactActions.resolveSessionsStates', $jid, true);
        }
        $this->rpc('RoomsUtils.resolveRoomEncryptionState', $room);
    }

    public function onVcard(Packet $packet)
    {
        $this->rpc(
            'MovimTpl.fill',
            '#' . cleanupId($packet->content) . '-vcard',
            $this->prepareVcard(\App\Contact::firstOrNew(['id' => $packet->content]))
        );
    }

    public function ajaxGetParticipant(string $room, string $mucjid)
    {
        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $r = new VcardGet;
        $r->setTo($mucjid)
            ->request();

        Dialog::fill($this->view('_rooms_participant', [
            'contact' => \App\Contact::firstOrNew(['id' => $mucjid]),
            'conference' => $conference,
            'clienttype' => getClientTypes(),
            'presence' => $conference->presences()
                ->with('capability')->where('mucjid', $mucjid)->first()
        ]));
    }

    public function prepareVcard(\App\Contact $contact)
    {
        return (new ContactActions)->prepareVcard($contact);
    }

    /**
     * @brief Get the avatar form
     */
    public function ajaxGetAvatar($room)
    {
        if (!validateRoom($room)) {
            return;
        }

        Dialog::fill($this->view('_rooms_avatar', [
            'room' => $this->me->session->conferences()
                ->where('conference', $room)
                ->first()
        ]));
    }

    /**
     * @brief Set the avatar
     */
    public function ajaxSetAvatar($room, $form)
    {
        if (!validateRoom($room)) {
            return;
        }

        $tempKey = \generateKey(6);

        $p = new Image;
        $p->fromBase64($form->photobin->value);
        $p->setKey($tempKey);
        $p->save(false, false, 'jpeg', 60);

        // Reload
        $p->load('jpeg');

        $vcard = new \stdClass;
        $vcard->photobin = new \stdClass;
        $vcard->phototype = new \stdClass;
        $vcard->photobin->value = $p->toBase();
        $vcard->phototype->value = 'image/jpeg';

        $r = new VcardSet;
        $r->setData($vcard)->setTo($room)->request();
    }

    public function onAvatarSet(Packet $packet)
    {
        $this->rpc('Dialog_ajaxClear');
        $this->toast($this->__('avatar.updated'));
    }

    public function onMucCreated(Packet $packet)
    {
        $this->rpc('RoomsUtils.configureCreatedRoom');
    }

    public function onPresenceMucNotAllowed(Packet $packet)
    {
        $this->toast($this->__('chatrooms.notallowed'));
    }

    public function onDiscoRegistrationRequired(Packet $packet)
    {
        $this->toast($this->__('rooms.disco_registration_required'));
    }

    public function onSetRole(Packet $packet)
    {
        $this->toast($this->__('room.role_changed'));
    }

    /**
     * @brief Affiliation changed for a user
     */
    public function onAffiliationChanged(Packet $packet)
    {
        $affiliation = $packet->content;

        switch ($affiliation) {
            case 'owner':
                $this->toast($this->__('room.affiliation_owner_changed'));
                break;

            case 'admin':
                $this->toast($this->__('room.affiliation_admin_changed'));
                break;

            case 'member':
                $this->toast($this->__('room.affiliation_member_changed'));
                break;

            case 'outcast':
                $this->toast($this->__('room.affiliation_outcast_changed'));
                break;

            case 'none':
                $this->toast($this->__('room.affiliation_none_changed'));
                break;
        }
    }

    /**
     * @brief When the affiliation change is unauthorized
     */
    public function onAffiliationChangeUnauthorized(Packet $packet)
    {
        $this->toast($this->__('room.change_affiliation_unauthorized'));
    }

    /**
     * @brief If a chatroom is successfuly created
     */
    public function onChatroomCreated(Packet $packet)
    {
        $this->toast($this->__('chatrooms.created'));

        $values = $packet->content;

        $conference = $this->me->session->conferences()
            ->where('conference', $values['jid'])
            ->firstOrNew();

        $conference->conference = $values['jid'];
        $conference->name = $values['name'];
        $conference->autojoin = $values['autojoin'];
        $conference->pinned = $values['pinned'];
        $conference->nick = $values['nick'];
        $conference->notify = $values['notify'];

        $b = new Set;
        $b->setConference($conference)
            ->request();

        // Disconnect properly
        $nick = $values['nick'] ?? $this->me->username;
        $session = Session::instance();
        $session->delete($values['jid'] . '/' . $nick);

        $pu = new Unavailable;
        $pu->setTo($values['jid'])
            ->setResource($nick)
            ->request();

        $this->me->session->presences()->where('jid', $values['jid'])->delete();
        //$this->rpc('RoomsUtils.configureDisconnect', $values['jid']);

        $this->rpc('Dialog_ajaxClear');
    }

    /**
     * @brief If a chatroom creation is failing
     */
    public function onChatroomCreatedError(Packet $packet)
    {
        $this->toast($packet->content);
    }

    /**
     * @brief Get the subject form of a chatroom
     */
    public function ajaxGetSubject($room)
    {
        if (!validateRoom($room)) {
            return;
        }

        Dialog::fill($this->view('_rooms_subject', [
            'room' => $this->me->session->conferences()
                ->where('conference', $room)
                ->first()
        ]));
        $this->rpc('MovimUtils.applyAutoheight');
    }

    /**
     * @brief Change the subject of a chatroom
     */
    public function ajaxSetSubject($room, $form)
    {
        if (
            !validateRoom($room)
            || !Validator::stringType()->length(0, 200)->isValid($form->subject->value)
        ) {
            return;
        }

        $p = new SetSubject;
        $p->setTo($room)
            ->setSubject($form->subject->value)
            ->request();
    }

    /**
     * @brief Display the add room form
     */
    public function ajaxAskInvite($room = false)
    {
        Dialog::fill($this->view('_rooms_invite', [
            'contacts' => $this->me->session->contacts()->pluck('jid'),
            'room' => $room,
            'invite' => \App\Invite::set($this->me->id, $room),
        ]));
    }

    /**
     * @brief Display the add room form
     */
    public function ajaxAdd($room = false, $name = null, $create = false)
    {
        $view = $this->tpl();

        $view->assign('info', \App\Info::where('server', $room)
            ->where('node', '')
            ->whereCategory('conference')
            ->first());
        $view->assign('mucservice', \App\Info::where('parent', $this->me->session->host)
            ->whereDoesntHave('identities', function ($query) {
                $query->where('category', 'gateway');
            })
            ->whereCategory('conference')
            ->whereType('text')
            ->first());
        $view->assign('id', $room);
        $view->assign('create', $create);
        $view->assign(
            'conference',
            $this->me->session->conferences()
                ->where('conference', $room)->first()
        );
        $view->assign('name', $name);
        $view->assign('username', $this->me->username);

        $gateways = \App\Info::select('name', 'server', 'parent')
            ->whereCategory('gateway')
            ->whereNotNull('parent')
            ->groupBy('name', 'server', 'parent')
            ->orderBy('parent')
            ->orderBy('server')
            ->get();

        $gateways = $gateways->filter(fn($gateway) => $gateway->parent === $this->me->session->host)
            ->concat($gateways->reject(fn($gateway) => $gateway->parent === $this->me->session->host));

        $view->assign('gateways', $gateways);

        $this->rpc('Rooms.setDefaultServices', $this->me->session->getChatroomsServices());

        Dialog::fill($view->draw('_rooms_add'));
    }

    /**
     * Resolve the room slug from the name
     */
    public function ajaxResolveSlug($name)
    {
        $service = Info::where('parent', $this->me->session->host)
            ->whereCategory('conference')
            ->whereType('text')
            ->first();

        $slugified = slugify($name);

        if ($service && !empty($slugified)) {
            $this->rpc('Rooms.setJid', $slugified . '@' . $service->server);
        }
    }

    /**
     * @brief Create a chatroom
     */
    public function ajaxAddCreate($form)
    {
        if (!validateRoom($form->jid->value)) {
            $this->toast($this->__('chatrooms.bad_id'));
        } elseif (trim($form->name->value) == '') {
            $this->toast($this->__('chatrooms.empty_name'));
        } else {
            $m = new Muc;
            $m->enableCreate()
                ->noNotify()
                ->setTo(strtolower($form->jid->value))
                ->setNickname($form->nick->value ?? $this->me->username)
                ->request();
        }
    }

    public function ajaxConfigureCreated($form)
    {
        if (!validateRoom($form->jid->value)) {
            $this->toast($this->__('chatrooms.bad_id'));
        } else {
            if ($form->type->value == 'groupchat') {
                $cgc = new CreateGroupChat;
                $cgc->setTo(strtolower($form->jid->value))
                    ->setName($form->name->value)
                    ->setAutoJoin($form->autojoin->value)
                    ->setPinned($form->pinned->value)
                    ->setNick($form->nick->value ?? $this->me->username)
                    ->setNotify((int)array_flip(Conference::$notifications)[$form->notify->value])
                    ->request();
            } elseif ($form->type->value == 'channel') {
                $cgc = new CreateChannel;
                $cgc->setTo(strtolower($form->jid->value))
                    ->setName($form->name->value)
                    ->setAutoJoin($form->autojoin->value)
                    ->setPinned($form->pinned->value)
                    ->setNick($form->nick->value)
                    ->setNotify((int)array_flip(Conference::$notifications)[$form->notify->value])
                    ->request();
            }
        }
    }

    /**
     * @brief Confirm the room add
     */
    public function ajaxAddConfirm($form)
    {
        if (!validateRoom($form->jid->value)) {
            $this->toast($this->__('chatrooms.bad_id'));
        } elseif (trim($form->name->value) == '') {
            $this->toast($this->__('chatrooms.empty_name'));
        } else {
            $this->rpc('Rooms_ajaxExit', $form->jid->value);

            $packet = new Packet;
            $packet->content = [
                'jid' => $form->jid->value,
                'name' => $form->name->value,
                'nick' => $form->nick->value,
                'autojoin' => $form->autojoin->value,
                'pinned' => $form->pinned->value,
                'notify' => (int)array_flip(Conference::$notifications)[$form->notify->value],
            ];

            $this->onChatroomCreated($packet);
        }
    }

    /**
     * @brief Display the remove room confirmation
     */
    public function ajaxRemove($room)
    {
        if (!validateRoom($room)) return;
        Dialog::fill($this->view('_rooms_remove', ['room' => $room]));
    }

    /**
     * @brief Remove a room
     */
    public function ajaxRemoveConfirm($room)
    {
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
            ->where('conference', strtolower($room))
            ->first();

        $d = new Delete;
        $d->setId($room)
            ->setVersion($conference->bookmarkversion)
            ->request();

        $unregister = new Remove;
        $unregister->setTo($room)
            ->request();
    }

    /**
     * @brief Invite someone to a room
     */
    public function ajaxInvite($form)
    {
        if (!validateRoom($form->to->value)) {
            return;
        }

        if (!empty($form->invite->value)) {
            $id = generateUUID();
            $i = new Invite;
            $i->setTo($form->to->value)
                ->setId($id)
                ->setInvite($form->invite->value)
                ->request();

            // Create and save a message in the database to display the invitation
            $m = new Message;

            $m->user_id = $this->me->id;
            $m->id = $id;
            $m->type = 'invitation';
            $m->subject = $form->to->value;
            $m->jidfrom = $this->me->id;
            $m->jidto = $form->invite->value;
            $m->published = gmdate('Y-m-d H:i:s');
            $m->body = '';
            $m->markable = true;
            $m->seen = true;
            $m->resource = $this->me->session->resource;
            $m->save();

            $m = $m->fresh();

            $packet = new \Moxl\Xec\Payload\Packet;
            $packet->content = $m;

            (new Chats())->onMessage($packet);
            (new Chat())->onMessage($packet);

            // Notify
            $this->toast($this->__('room.invited'));
            $this->rpc('Dialog_ajaxClear');
        }
    }

    public function onInviteError(Packet $packet)
    {
        $this->toast($packet->content);
    }

    /**
     * @brief Destroy a room
     */
    public function ajaxAskDestroy($room)
    {
        if (!validateRoom($room)) return;
        Dialog::fill($this->view('_rooms_destroy', ['room' => $room]));
    }

    /**
     * @brief Destroy a room
     */
    public function ajaxDestroy($room)
    {
        if (!validateRoom($room)) {
            return;
        }

        $d = new Destroy;
        $d->setTo($room)
            ->request();
    }

    /**
     * @brief Get a page of pictures in the drawer
     */
    public function ajaxHttpGetPictures($room, $page = 0)
    {
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $more = false;
        $pictures = $conference->pictures()
            ->take($this->_picturesPagination + 1)
            ->skip($this->_picturesPagination * $page)
            ->get();

        if ($pictures->count() == $this->_picturesPagination + 1) {
            $pictures->pop();
            $more = true;
        }

        $this->rpc('MovimTpl.append', '#room_pictures', $this->view('_rooms_drawer_pictures', [
            'pictures' => $pictures,
            'more' => $more,
            'page' => $page,
            'room' => $room,
        ]));
    }

    /**
     * @brief Get a page of links in the drawer
     */
    public function ajaxHttpGetLinks($room, $page = 0)
    {
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->with('info')
            ->first();

        if (!$conference) return;

        $more = false;
        $links = $conference->links()
            ->take($this->_linksPagination + 1)
            ->skip($this->_linksPagination * $page)
            ->get();

        if ($links->count() == $this->_linksPagination + 1) {
            $links->pop();
            $more = true;
        }

        $this->rpc('MovimTpl.append', '#room_links', $this->view('_rooms_drawer_links', [
            'links' => $links,
            'more' => $more,
            'page' => $page,
            'room' => $room,
        ]));
    }

    /**
     * @brief Autocomplete users in MUC
     */
    public function ajaxMucUsersAutocomplete($room)
    {
        $this->rpc("Chat.onAutocomplete", $this->me->session->conferences()
            ->where('conference', $room)
            ->first()->presences
            ->pluck('resource'));
    }

    /**
     * @brief Discover rooms for a gateway
     */
    public function ajaxDiscoGateway(string $server)
    {
        $this->ajaxResetGatewayRooms();
        $this->rpc('Rooms.selectGatewayRoom', '', '');

        if (!empty($server)) {
            $r = new Items;
            $r->setTo($server)
                ->disableSave()
                ->request();
        }
    }

    /**
     * @brief Show the ban form
     */
    public function ajaxAddBanned(string $room)
    {
        if (!validateRoom($room)) {
            return;
        }

        Dialog::fill($this->view('_rooms_ban', ['room' => $this->me->session->conferences()
            ->where('conference', $room)
            ->first()]));
    }

    /**
     * @brief Ban someone
     */
    public function ajaxAddBannedConfirm(string $room, $form)
    {
        if (!validateRoom($room)) {
            return;
        }

        $p = new ChangeAffiliation;
        $p = $p->setTo($room)
            ->setJid($form->jid->value)
            ->setAffiliation('outcast');

        if (!empty($form->reason->value)) {
            $p = $p->setReason($form->reason->value);
        }

        $p->request();
    }

    /**
     * @brief Show the unban form
     */
    public function ajaxRemoveBanned(string $room, string $jid)
    {
        if (!validateRoom($room)) {
            return;
        }

        Dialog::fill($this->view('_rooms_unban', [
            'room' => $this->me->session->conferences()
                ->where('conference', $room)
                ->first(),
            'jid' => $jid,
        ]));
    }

    /**
     * @brief Unban someone
     */
    public function ajaxRemoveBannedConfirm(string $room, string $jid)
    {
        if (!validateRoom($room)) {
            return;
        }

        $p = new ChangeAffiliation;
        $p->setTo($room)
            ->setJid($jid)
            ->setAffiliation('none')
            ->request();
    }

    /**
     * @brief Show the user configuration panel
     */
    public function ajaxConfigureUser(string $room, string $jid)
    {
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->first();

        Dialog::fill($this->view('_rooms_configure_user', [
            'room' => $conference,
            'presence' => $conference->presences()->where('mucjid', $jid)->first(),
            'member' => $conference->members()->where('jid', $jid)->first(),
            'contact' => Contact::firstOrNew(['id' => $jid]),
        ]));
    }

    /**
     * @brief Change a user voice
     */
    public function ajaxChangeVoice(string $room, string $mucjid, $form)
    {
        if (!validateRoom($room)) {
            return;
        }

        $conference = $this->me->session->conferences()
            ->where('conference', $room)
            ->first();

        if ($conference) {
            $presence = $conference->presences()->where('mucjid', $mucjid)->first();

            if ($presence) {
                $p = new SetRole;
                $p->setTo($room)
                    ->setNick($presence->resource)
                    ->setRole($form->voice->value ? 'participant' : 'visitor')
                    ->request();
            }
        }
    }

    /**
     * @brief Change the affiliation
     */
    public function ajaxChangeAffiliationConfirm(string $room, $form)
    {
        if (!validateRoom($room)) {
            return;
        }

        $p = new ChangeAffiliation;
        $p->setTo($room)
            ->setJid($form->jid->value)
            ->setAffiliation($form->affiliation->value)
            ->request();
    }

    /**
     * @brief Get MAM history
     */
    public function ajaxGetMAMHistory(string $jid)
    {
        $g = new \Moxl\Xec\Action\MAM\Get;

        $message = $this->me->messages()
            ->where('jidfrom', $jid)
            ->whereNull('subject');

        $message = (DB::getDriverName() == 'pgsql')
            ? $message->orderByRaw('published asc nulls last')
            : $message->orderBy('published', 'asc');
        $message = $message->first();

        $g->setTo($jid);

        if ($message && $message->published) {
            $g->setEnd(strtotime($message->published));
        }

        $g->setLimit(150);
        $g->setBefore('');
        $g->request();
    }

    public function onDiscoGateway(Packet $packet)
    {
        $groups = [];

        $rooms = collect($packet->content);
        $rooms = $rooms->map(function ($name, $key) use (&$groups) {
            $item = new \stdClass;
            $explodedName = explode('/', $name);

            if (count($explodedName) > 1) {
                $item->parent = $explodedName[0];
                array_shift($explodedName);
                $item->name = implode(' / ', $explodedName);

                if (!array_key_exists($item->parent, $groups)) {
                    $groups[$item->parent] = 0;
                }

                $groups[$item->parent]++;
            } else {
                $item->parent = null;
                $item->name = $name;
            }

            return $item;
        })->map(function ($item, $key) use ($groups) {
            if ($item->parent != null && array_key_exists($item->parent, $groups) && $groups[$item->parent] == 1) {
                $item->name = $item->parent . '/' . $item->name;
                $item->parent = null;
            }

            return $item;
        })->sortBy('parent');

        $this->rpc('MovimTpl.fill', '#gateway_rooms', $this->view('_rooms_gateway_rooms', [
            'rooms' => $rooms
        ]));
    }

    public function onDiscoGatewayError(Packet $packet)
    {
        $this->ajaxResetGatewayRooms();
    }

    public function ajaxResetGatewayRooms()
    {
        $this->rpc('MovimTpl.fill', '#gateway_rooms', '');
    }

    public function prepareEmbedUrl(Message $message)
    {
        return (new Chat())->prepareEmbed($message->resolvedUrl, $message);
    }
}
