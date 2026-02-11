<?php

namespace App\Widgets\Visio;

use App\Message;
use App\Widgets\Rooms\Rooms;
use App\Widgets\Dialog\Dialog;

use Movim\ImageSize;
use Movim\Librairies\JingletoSDP;
use Movim\Librairies\SDPtoJingle;
use Movim\Widget\Base;
use Movim\Widget\Wrapper;

use Moxl\Xec\Action\Jingle\ContentAdd;
use Moxl\Xec\Action\Jingle\ContentModify;
use Moxl\Xec\Action\Jingle\ContentRemove;
use Moxl\Xec\Action\Jingle\MessageFinish;
use Moxl\Xec\Action\Jingle\MessageProceed;
use Moxl\Xec\Action\Jingle\MessagePropose;
use Moxl\Xec\Action\Jingle\MessageReject;
use Moxl\Xec\Action\Jingle\MessageRetract;
use Moxl\Xec\Action\Jingle\MessageRinging;
use Moxl\Xec\Action\Jingle\SessionInitiate;
use Moxl\Xec\Action\Jingle\SessionMute;
use Moxl\Xec\Action\Jingle\SessionTerminate;
use Moxl\Xec\Action\Jingle\SessionUnmute;
use Moxl\Xec\Action\JingleCallInvite\Accept;
use Moxl\Xec\Action\JingleCallInvite\Invite;
use Moxl\Xec\Action\JingleCallInvite\Retract;
use Moxl\Xec\Action\Muc\CreateMujiRoom;
use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Presence\Unavailable;
use Moxl\Xec\Payload\Packet;

class Visio extends Base
{
    public function load()
    {
        $this->addcss('visio.css');
        $this->addcss('visio_lobby.css');
        $this->addjs('visio_utils.js');
        $this->addjs('visio_dtmf.js');

        $this->registerEvent('jinglepropose', 'onPropose');
        $this->registerEvent('jingleproceed', 'onProceed');
        $this->registerEvent('jingleaccept', 'onAccept');
        $this->registerEvent('jingleretract', 'onRetract');
        $this->registerEvent('jinglereject', 'onReject');
        $this->registerEvent('jinglefinish', 'onFinish');

        $this->registerEvent('jingle_sessioninitiate', 'onInitiateSDP');
        //$this->registerEvent('jingle_sessioninitiate_erroritemnotfound', 'onNotFound');
        $this->registerEvent('jingle_sessionaccept', 'onAcceptSDP');
        $this->registerEvent('jingle_transportinfo', 'onCandidate');
        $this->registerEvent('jingle_sessionterminate', 'onTerminate');
        $this->registerEvent('jingle_sessionmute', 'onMute');
        $this->registerEvent('jingle_sessionunmute', 'onUnmute');

        $this->registerEvent('jingle_contentadd', 'onContentAdd');
        $this->registerEvent('jingle_contentmodify', 'onContentModify');
        $this->registerEvent('jingle_contentremove', 'onContentRemove');

        $this->registerEvent('externalservices_get_handle', 'onExternalServices');
        $this->registerEvent('externalservices_get_error', 'onExternalServicesError');

        $this->registerEvent('session_down', 'onSessionDown');
        $this->registerEvent('presence_muji', 'onMujiPresence');
        $this->registerEvent('presence_muc_muji_preparing', 'onMucMujiPreparing');
        $this->registerEvent('presence_muc_create_muji_handle', 'onMucMujiCreated');

        $this->registerEvent('callinviteretract', 'onCallInviteRetract');

        $this->registerEvent('currentcall_stopped', 'onCallStopped');
    }

    public function onCallStopped(Packet $packet)
    {
        $this->rpc('MovimVisio.callStop', $packet->content, $packet->from);
    }

    public function onSessionDown()
    {
        $currentCall = $this->currentCall();

        if ($currentCall && $currentCall->isStarted()) {
            $st = $this->xmpp(new SessionTerminate);
            $st->setTo($currentCall->jid)
                ->setJingleSid($currentCall->id)
                ->setReason('failed-application')
                ->request();

            $currentCall->stop($currentCall->jid, $currentCall->id);
        }
    }

    public function onMucMujiCreated(Packet $packet)
    {
        $presence = $packet->content;

        $createMujiRoom = $this->xmpp(new CreateMujiRoom);
        $createMujiRoom->setTo($presence->jid)
            ->request();
    }

    public function onMucMujiPreparing(Packet $packet)
    {
        $this->ajaxMujiTrigger();
    }

    public function onCallInviteRetract(Packet $packet)
    {
        $this->ajaxClear();
    }

    public function onExternalServices(Packet $packet)
    {
        $externalServices = [];
        if ($packet->content) {
            $turn = $stun = false;
            foreach ($packet->content as $service) {
                // One STUN/TURN server max
                if ($service['type'] == 'stun' && $stun) continue;
                if ($service['type'] == 'stun') $stun = true;
                if ($service['type'] == 'turn' && $turn) continue;
                if ($service['type'] == 'turn') $turn = true;

                $url = $service['type'] . ':' . $service['host'];
                $url .= !empty($service['port']) ? ':' . $service['port'] : '';
                $item = ['urls' => $url];

                if (isset($service['username']) && isset($service['password'])) {
                    $item['username'] = $service['username'];
                    $item['credential'] = $service['password'];
                }

                array_push($externalServices, $item);
            }
        }

        if (!empty($externalServices)) {
            $this->rpc('MovimVisio.setServices', $externalServices);
        } else {
            $this->setDefaultServices();
        }
    }

    public function onMujiPresence(Packet $packet)
    {
        list($stanza, $presence) = $packet->content;
        if (
            $stanza && $stanza->muji && $stanza->muji->attributes()->xmlns == 'urn:xmpp:jingle:muji:0'
            && $stanza->muji->content && $stanza->muji->content->attributes()->xmlns == 'urn:xmpp:jingle:1'
        ) {
            $contact = \App\Contact::firstOrNew(['id' => \bareJid($packet->from)]);

            $this->rpc(
                'MovimJingles.initSession',
                \bareJid($packet->from),
                $packet->from,
                null,
                $contact->truename,
                $contact->getPicture(ImageSize::L)
            );
        }
    }

    public function onExternalServicesError(Packet $packet)
    {
        $this->setDefaultServices();

        //$this->rpc('MovimJingles.onInitiateSDP', \bareJid($packet->from), $jts->generate());
    }

    /**
     * Session events
     */

    public function onPropose(Packet $packet)
    {
        $message = Message::eventMessageFactory(
            $this->me,
            'jingle',
            bareJid($packet->from),
            $packet->content['id']
        );
        $message->type = 'jingle_incoming';
        $message->save();

        Wrapper::getInstance()->iterate('jingle_message', (new Packet)->pack($message), user: $this->me, sessionId: $this->sessionId);

        $ringing = $this->xmpp(new MessageRinging);
        $ringing->setTo($packet->from)
            ->setId($packet->content['id'])
            ->request();

        $this->ajaxGetLobby($packet->from, false, $packet->content['withVideo'], $packet->content['id']);
    }

    public function onProceed(Packet $packet)
    {
        $this->currentCall()->start($packet->from, $packet->content);
        $this->rpc('MovimJingles.onProceed', \bareJid($packet->from), $packet->from, $packet->content /* id */);
    }

    // Deprecated
    public function onAccept(Packet $packet)
    {
        $this->currentCall()->start($packet->from, $packet->content);
        $this->rpc('Notif.incomingCallAnswer');

        (new Dialog($this->me, sessionId: $this->sessionId))->ajaxClear();
    }

    public function onRetract(Packet $packet)
    {
        $this->onTerminate($packet);
    }

    public function onReject(Packet $packet)
    {
        $this->onTerminate($packet);
    }

    public function onFinish(Packet $packet)
    {
        $this->onTerminate($packet);
    }

    /*public function onNotFound(Packet $packet)
    {
        ($this->currentCall())->stop(\bareJid($packet->from), $packet->content);
        $this->onTerminate('notfound');
    }*/

    public function onTerminate(Packet $packet)
    {
        ($this->currentCall())->stop(\bareJid($packet->from), $packet->content);

        // Stop calling sound and clear the Dialog if there
        $this->rpc('Notif.incomingCallAnswer');
        (new Dialog($this->me, sessionId: $this->sessionId))->ajaxClear();

        $this->toast($this->__('visio.ended'));

        $this->ajaxClear();
        $this->rpc('MovimJingles.onTerminate', \bareJid($packet->from));
    }

    /**
     * Jingle events
     */

    public function onInitiateSDP(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc('MovimJingles.onInitiateSDP', \bareJid($packet->from), $jts->generate(), $jts->sid);
    }

    public function onContentAdd(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc(
            'MovimJingles.onContentAdd',
            \bareJid($packet->from),
            $jts->generate(),
            (string)$packet->content->content->attributes()->name
        );
    }

    public function onContentModify(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc(
            'MovimJingles.onContentModify',
            \bareJid($packet->from),
            $jts->generate(),
            //(string)$packet->content->attributes()->name
        );
    }

    public function onContentRemove(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc(
            'MovimJingles.onContentRemove',
            \bareJid($packet->from),
            $jts->generate(),
            (string)$packet->content->attributes()->name
        );
    }

    public function onAcceptSDP(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);
        $this->rpc('MovimJingles.onAcceptSDP', \bareJid($packet->from), $jts->generate());
    }

    public function onCandidate(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);
        $sdp = $jts->generate();

        $this->rpc('MovimJingles.onCandidate', \bareJid($packet->from), $sdp, (string)$jts->name, $jts->name);
    }

    public function onMute(Packet $packet)
    {
        $this->rpc('MovimJingles.onMute', \bareJid($packet->from), $packet->content);
    }

    public function onUnmute(Packet $packet)
    {
        $this->rpc('MovimJingles.onUnmute', \bareJid($packet->from), $packet->content);
    }

    public function ajaxClear()
    {
        $this->rpc('MovimVisio.clear');
    }

    public function ajaxPropose(string $to, string $id, ?bool $withVideo = false)
    {
        $message = Message::eventMessageFactory(
            $this->me,
            'jingle',
            bareJid($to),
            $id
        );
        $message->type = 'jingle_outgoing';
        $message->save();

        Wrapper::getInstance()->iterate('jingle_message', (new Packet)->pack($message), user: $this->me, sessionId: $this->sessionId);

        $p = $this->xmpp(new MessagePropose);
        $p->setTo($to)
            ->setId($id)
            ->setWithVideo($withVideo)
            ->request();
    }

    public function ajaxProceed(string $to, string $id)
    {
        $this->currentCall()->start($to, $id);
        $this->rpc('Notif.incomingCallAnswer');

        /*$p = $this->xmpp(new MessageAccept);
        $p->setId($id)
          ->request();*/

        $p = $this->xmpp(new MessageProceed);
        $p->setTo($to)
            ->setId($id)
            ->request();
    }

    public function ajaxReject(string $to, string $id)
    {
        ($this->currentCall())->stop($to, $id);

        $this->rpc('Notif.incomingCallAnswer');

        $reject = $this->xmpp(new MessageReject);
        $reject->setTo($to)
            ->setId($id)
            ->request();
    }

    public function ajaxMute(string $to, string $id, $name)
    {
        $p = $this->xmpp(new SessionMute);
        $p->setTo($to)
            ->setId($id)
            ->setName($name)
            ->request();
    }

    public function ajaxUnmute(string $to, string $id, $name)
    {
        $p = $this->xmpp(new SessionUnmute);
        $p->setTo($to)
            ->setId($id)
            ->setName($name)
            ->request();
    }

    /** Content */

    public function ajaxContentAdd(string $to, string $sdp, string $id, array $mediaIds)
    {
        $stj = new SDPtoJingle(
            user: $this->me,
            sdp: $this->filterSDPMedia($sdp, $mediaIds),
            sid: $id,
            action: 'content-add'
        );

        $si = $this->xmpp(new ContentAdd);
        $si->setTo($to)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxContentRemove(string $to, string $sdp, string $id, array $mediaIds)
    {
        $stj = new SDPtoJingle(
            user: $this->me,
            sdp: $this->filterSDPMedia($sdp, $mediaIds),
            sid: $id,
            action: 'content-remove'
        );

        $si = $this->xmpp(new ContentRemove);
        $si->setTo($to)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxContentModify(string $to, string $sdp, string $id)
    {
        $stj = new SDPtoJingle(
            user: $this->me,
            sdp: $sdp,
            sid: $id,
            action: 'content-modify'
        );

        $si = $this->xmpp(new ContentModify);
        $si->setTo($to)
            ->setJingle($stj->generate())
            ->request();
    }

    /** Muji */

    public function ajaxChooseMuji(string $muc)
    {
        $view = $this->tpl();
        $view->assign('conference', $this->me->session->conferences()
            ->where('conference', $muc)
            ->first());

        $this->dialog($view->draw('_visio_choose_muji'), false, true);
    }

    public function ajaxJoinMuji(string $mujiId, ?bool $withVideo = false)
    {
        $muji = $this->me->session->mujiCalls()
            ->where('id', $mujiId)
            ->with('conference')
            ->first();

        if ($muji) {
            $this->ajaxGetMujiLobby($muji->jidfrom, false, $withVideo, $muji->id);
        }
    }

    public function ajaxLeaveMuji(string $mujiId)
    {
        $muji = $this->me->session->mujiCalls()
            ->where('id', $mujiId)
            ->with('conference')
            ->first();

        if ($muji) {
            $this->currentCall()->stop($muji->jidfrom, $muji->id);

            $resource = $muji->presences()->where('mucjid', $this->me->id)->first()?->resource;

            if ($resource) {
                $pu = $this->xmpp(new Unavailable);
                $pu->setTo($muji->muc)
                    ->setResource($resource)
                    ->request();

                $this->me->session->mujiCalls()->where('id', $mujiId)->delete();

                (new Rooms($this->me, sessionId: $this->sessionId))->onPresence($muji->jidfrom);

                // If we were the inviter, we also retract the call
                $participant = $muji->participants->firstWhere('jid', $muji->jidfrom . '/' . $resource);
                if ($participant && $participant->inviter) {
                    $retract = $this->xmpp(new Retract);
                    $retract->setTo($muji->jidfrom)
                        ->setId($muji->id)
                        ->request();
                }
            }

            $this->rpc('MovimJingles.terminateAll');
        }
    }

    public function ajaxGetMujiLobby(string $jid, bool $calling = false, ?bool $withVideo = false, ?string $id = null)
    {
        $view = $this->tpl();
        $view->assign('conference', $this->me->session
            ->conferences()->where('conference', $jid)
            ->first());
        $view->assign('calling', $calling);
        $view->assign('withvideo', $withVideo);
        $view->assign('id', $id);

        $this->dialog($view->draw('_visio_lobby'), false, true);
        $this->rpc('MovimVisio.getUserMedia', $withVideo);
    }

    public function ajaxGetLobby(string $jid, bool $calling = false, ?bool $withVideo = false, ?string $id = null)
    {
        $contact = \App\Contact::firstOrNew(['id' => \bareJid($jid)]);

        $view = $this->tpl();
        $view->assign('contact', $contact);
        $view->assign('calling', $calling);
        $view->assign('withvideo', $withVideo);
        $view->assign('id', $id);
        $view->assign('fullJid', $jid);

        $this->dialog($view->draw('_visio_lobby'), false, true);
        $this->rpc('MovimVisio.getUserMedia', $withVideo);

        if ($calling == false) {
            $this->rpc('Notif.incomingCall');

            $this->notif(
                key: 'call',
                title: '📞 ' . $contact->truename,
                body: $this->__('visio.calling'),
                url: '',
                picture: $contact->getPicture(),
                time: 5,
                actions: [[
                    'title' => $this->__('button.reply'),
                    'action' => 'call',
                ], [
                    'title' => $this->__('button.refuse'),
                    'action' => 'call_reject',
                ]],
                data: [
                    'jid' => $contact->id,
                    'fullJid' => !$calling ? $jid : null,
                    'callId' => !$calling ? $id : null
                ]
            );
        }
    }

    public function ajaxMujiAccept(string $mujiId)
    {
        $muji = $this->me->session->mujiCalls()
            ->where('id', $mujiId)
            ->with('conference')
            ->first();

        if ($muji) {
            $accept = $this->xmpp(new Accept);
            $accept->setTo($muji->jidfrom)
                ->setId($muji->id)
                ->request();

            $this->currentCall()->start($muji->jidfrom, $muji->id, mujiRoom: $muji->muc);

            $muc = $this->xmpp(new Muc);
            $muc->setTo($muji->muc)
                ->setNickname($muji->conference ? $muji->conference->nickname : $this->me->nickname)
                ->enableMujiPreparing()
                ->noNotify()
                ->request();

            $this->rpc('Chat_ajaxGetHeader', $muji->jidfrom, true);
        }
    }

    public function ajaxMujiTrigger()
    {
        $muji = $this->me->session->mujiCalls()->first();

        $this->rpc('MovimVisio.init', $muji->jidfrom, $muji->jidfrom, $muji->id, $muji->video, true);
    }

    public function ajaxMujiInit(string $mujiId, $sdp)
    {
        $muji = $this->me->session->mujiCalls()
            ->where('id', $mujiId)
            ->with('conference')
            ->first();

        if ($muji) {
            $stj = new SDPtoJingle(
                user: $this->me,
                sdp: $sdp->sdp,
                sid: $mujiId,
                muji: true
            );

            $muc = $this->xmpp(new Muc);
            $muc->setTo($muji->muc)
                ->setNickname($muji->conference ? $muji->conference->nickname : $this->me->nickname)
                ->setMuji($stj->generate())
                ->noNotify()
                ->request();

            $this->rpc('MovimJingles.startCalls', $muji->muc);
        }
    }

    public function ajaxMujiCreate(string $to, bool $withVideo = false)
    {
        $conference = $this->me->session
            ->conferences()->where('conference', $to)
            ->first();

        $mujiService = $this->me->session->getMujiService();

        if (!$mujiService) {
            $this->toast($this->__('muji.cannot_create'));
            return;
        }

        if ($conference) {
            $mujiId = generateUUID();
            $mujiConference = generateKey(withCapitals: false);
            $mujiConferenceJid = $mujiConference . '@' . $mujiService->server;

            $this->currentCall()->start($to, $mujiId, mujiRoom: $mujiConferenceJid);

            $muc = $this->xmpp(new Muc);
            $muc->setTo($mujiConferenceJid)
                ->setNickname($conference->nick)
                ->enableCreate()
                ->enableMujiPreparing()
                ->noNotify()
                ->request();

            $invite = $this->xmpp(new Invite);
            $invite->setTo($to)
                ->setId($mujiId)
                ->setRoom($mujiConferenceJid);

            if ($withVideo) {
                $invite->enableVideo();
            }

            $invite->request();
        }
    }

    public function ajaxSessionInitiate(string $jid, $sdp, string $id, ?string $mujiRoom = null)
    {
        $stj = new SDPtoJingle(
            user: $this->me,
            sdp: $sdp->sdp,
            sid: $id,
            responder: $jid,
            action: 'session-initiate'
        );

        if ($mujiRoom) {
            $stj->setMujiRoom($mujiRoom);
        }

        $si = $this->xmpp(new SessionInitiate);
        $si->setTo($jid)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxResolveServices()
    {
        if (!$this->me?->session) return;

        $info = \App\Info::where('server', $this->me->session->host)
            ->where('node', '')
            ->first();
        if ($info && $info->hasExternalServices()) {
            $c = $this->xmpp(new \Moxl\Xec\Action\ExternalServices\Get);
            $c->setTo($this->me->session->host)
                ->request();
        } else {
            $this->setDefaultServices();
        }
    }

    public function ajaxPrepare(string $jid)
    {
        $bareJid = \bareJid($jid);
        $contact = \App\Contact::firstOrNew(['id' => $bareJid]);

        $view = $this->tpl();
        $view->assign('contact', $contact);

        $this->rpc('MovimVisio.moveToChat', $bareJid);
        $this->rpc('MovimTpl.fill', '#visio_contact', $view->draw('_visio_contact'));
    }

    public function setDefaultServices()
    {
        $servers = [
            'stun:stun.l.google.com:19305',
            'stun:stun1.l.google.com:19305',
            'stun:stun2.l.google.com:19305',
            'stun:stun3.l.google.com:19305',
            'stun:stun4.l.google.com:19305',
        ];

        shuffle($servers);
        $this->rpc('MovimVisio.setServices', [['urls' => array_slice($servers, 0, 2)]]);
    }

    public function ajaxHttpGetStates()
    {
        $this->rpc('MovimVisio.setStates', [
            'calling' => $this->__('visio.calling'),
            'ringing' => $this->__('visio.ringing'),
            'in_call' => $this->__('visio.in_call'),
            'failed' => $this->__('visio.failed'),
            'connecting' => $this->__('visio.connecting'),
            'ended' =>  $this->__('visio.ended'),
            'declined' => $this->__('visio.declined'),
            'no_participants_left' => $this->__('visio.no_participants_left'),
        ]);
    }

    public function ajaxSessionAccept(string $to, string $id, $sdp)
    {
        $stj = new SDPtoJingle(
            user: $this->me,
            sdp: $sdp->sdp,
            sid: $id,
            responder: $to,
            action: 'session-accept'
        );

        $si = $this->xmpp(new SessionInitiate);
        $si->setTo($to)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxCandidate(string $to, string $id, $sdp)
    {
        // Firefox is passing the ufrag as an argument, Chrome as a parameter in the candidate
        $ufrag = $sdp->usernameFragment ?? null;

        $stj = new SDPtoJingle(
            user: $this->me,
            sdp: 'a=' . $sdp->candidate,
            sid: $id,
            responder: $to,
            action: 'transport-info',
            mid: $sdp->sdpMid,
            ufrag: $ufrag
        );

        $si = $this->xmpp(new SessionInitiate);
        $si->setTo($to)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxTerminate(string $to, string $sid, ?string $reason = 'success')
    {
        $st = $this->xmpp(new SessionTerminate);
        $st->setTo($to)
            ->setJingleSid($sid)
            ->setReason($reason ?? 'success')
            ->request();
    }

    public function ajaxRemoteGoodbye()
    {
        $currentCall = $this->currentCall();

        if ($currentCall->isStarted()) {
            $this->dialog($this->view('_visio_remote_goodbye', [
                'contact' => \App\Contact::firstOrNew(['id' => $currentCall->getBareJid()]),
                'jid' => $currentCall->jid,
                'sid' => $currentCall->id
            ]));
        }
    }

    /**
     * @desc Close a one-to-one call
     */
    public function ajaxGoodbye(string $to, string $sid, ?string $reason = 'success')
    {
        if ($this->currentCall()->isStarted()) {
            $this->currentCall()->stop($to, $sid);
            $st = $this->xmpp(new MessageFinish);
            $st->setTo($to)
                ->setId($sid)
                ->setReason($reason ?? 'success')
                ->request();
        } else {
            $sr = $this->xmpp(new MessageRetract);
            $sr->setTo($to)
                ->setId($sid)
                ->request();

            $message = Message::eventMessageFactory(
                $this->me,
                'jingle',
                bareJid($to),
                $sid
            );
            $message->type = 'jingle_retract';
            $message->save();

            Wrapper::getInstance()->iterate('jingle_message', (new Packet)->pack($message), user: $this->me, sessionId: $this->sessionId);
        }

        $this->toast($this->__('visio.ended'));
        $this->rpc('MovimJingles.terminateAll', $reason);
    }

    /**
     * @desc Check the call status of the current browser
     */
    public function ajaxCheckStatus(?string $id = null, ?string $jid = null)
    {
        $currentCall = $this->currentCall();

        if (
            $currentCall && $currentCall->isStarted()
            && $id != null
            && $jid != null
            && $currentCall->hasId($id)
            && $currentCall->isJidInCall($jid)
        ) {
            $message = Message::eventMessageFactory(
                $this->me,
                'jingle',
                bareJid($currentCall->jid),
                $currentCall->id
            );
            $message->type = 'jingle_finish';
            $message->save();

            Wrapper::getInstance()->iterate('jingle_message', (new Packet)->pack($message), user: $this->me, sessionId: $this->sessionId);

            $this->ajaxTerminate($currentCall->jid, $currentCall->id, 'gone');
            $this->ajaxGoodbye($currentCall->jid, $currentCall->id, 'gone');
        }
    }

    private function filterSDPMedia(string $sdp, array $mediaIds)
    {
        // Ugly but simple
        $exp = explode('m=', $sdp);
        $selected = [];

        foreach ($exp as $media) {
            foreach ($mediaIds as $mediaId) {
                if (str_contains($media, 'a=mid:' . $mediaId)) {
                    array_push($selected, $media);
                }
            }
        }

        return $exp[0] . 'm=' . implode('m=', $selected);
    }
}
