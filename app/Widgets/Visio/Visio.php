<?php

namespace App\Widgets\Visio;

use App\Message;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Notif\Notif;
use App\Widgets\Rooms\Rooms;
use App\Widgets\Toast\Toast;

use Movim\CurrentCall;
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
    }

    public function onSessionDown()
    {
        $currentCall = CurrentCall::getInstance();

        if ($currentCall->isStarted()) {
            $st = new SessionTerminate;
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

        $createMujiRoom = new CreateMujiRoom;
        $createMujiRoom->setTo($presence->jid)
            ->request();
    }

    public function onMucMujiPreparing(Packet $packet)
    {
        $this->ajaxMujiTrigger();
    }

    public function onCallInviteRetract(Packet $packet)
    {
        $this->rpc('MovimVisio.clear');
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
            $contact = \App\Contact::firstOrNew(['id' => \baseJid($packet->from)]);

            $this->rpc(
                'MovimJingles.initSession',
                \baseJid($packet->from),
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

        //$this->rpc('MovimJingles.onInitiateSDP', \baseJid($packet->from), $jts->generate());
    }

    /**
     * Session events
     */

    public function onPropose(Packet $packet)
    {
        $message = Message::eventMessageFactory(
            'jingle',
            baseJid($packet->from),
            $packet->content['id']
        );
        $message->type = 'jingle_incoming';
        $message->save();

        Wrapper::getInstance()->iterate('jingle_message', (new Packet)->pack($message));

        $this->ajaxGetLobby($packet->from, false, $packet->content['withVideo'], $packet->content['id']);
    }

    public function onProceed(Packet $packet)
    {
        CurrentCall::getInstance()->start(\baseJid($packet->from), $packet->content);
        $this->rpc('MovimJingles.onProceed', \baseJid($packet->from), $packet->from, $packet->content /* id */);
    }

    // Deprecated
    public function onAccept(Packet $packet)
    {
        $this->rpc('Notif.incomingCallAnswer');
        CurrentCall::getInstance()->start(\baseJid($packet->from), $packet->content);

        (new Dialog)->ajaxClear();
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
        (CurrentCall::getInstance())->stop(\baseJid($packet->from), $packet->content);
        $this->onTerminate('notfound');
    }*/

    public function onTerminate(Packet $packet)
    {
        (CurrentCall::getInstance())->stop(\baseJid($packet->from), $packet->content);

        // Stop calling sound and clear the Dialog if there
        $this->rpc('Notif.incomingCallAnswer');
        (new Dialog)->ajaxClear();

        Toast::send($this->__('visio.ended'));

        $this->rpc('MovimJingles.onTerminate', \baseJid($packet->from));
    }

    /**
     * Jingle events
     */

    public function onInitiateSDP(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc('MovimJingles.onInitiateSDP', \baseJid($packet->from), $jts->generate(), $jts->sid);
    }

    public function onContentAdd(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc(
            'MovimJingles.onContentAdd',
            \baseJid($packet->from),
            $jts->generate(),
            (string)$packet->content->content->attributes()->name
        );
    }

    public function onContentModify(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc(
            'MovimJingles.onContentModify',
            \baseJid($packet->from),
            $jts->generate(),
            //(string)$packet->content->attributes()->name
        );
    }

    public function onContentRemove(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc(
            'MovimJingles.onContentRemove',
            \baseJid($packet->from),
            $jts->generate(),
            (string)$packet->content->attributes()->name
        );
    }

    public function onAcceptSDP(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);
        $this->rpc('MovimJingles.onAcceptSDP', \baseJid($packet->from), $jts->generate());
    }

    public function onCandidate(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);
        $sdp = $jts->generate();

        $this->rpc('MovimJingles.onCandidate', \baseJid($packet->from), $sdp, (string)$jts->name, $jts->name);
    }

    public function onMute(Packet $packet)
    {
        $this->rpc('MovimJingles.onMute', \baseJid($packet->from), $packet->content);
    }

    public function onUnmute(Packet $packet)
    {
        $this->rpc('MovimJingles.onUnmute', \baseJid($packet->from), $packet->content);
    }

    public function ajaxPropose(string $to, string $id, ?bool $withVideo = false)
    {
        $message = Message::eventMessageFactory(
            'jingle',
            baseJid($to),
            $id
        );
        $message->type = 'jingle_outgoing';
        $message->save();

        Wrapper::getInstance()->iterate('jingle_message', (new Packet)->pack($message));

        $p = new MessagePropose;
        $p->setTo($to)
            ->setId($id)
            ->setWithVideo($withVideo)
            ->request();
    }

    public function ajaxProceed(string $to, string $id)
    {
        CurrentCall::getInstance()->start(\baseJid($to), $id);

        $this->rpc('Notif.incomingCallAnswer');

        /*$p = new MessageAccept;
        $p->setId($id)
          ->request();*/

        $p = new MessageProceed;
        $p->setTo($to)
            ->setId($id)
            ->request();
    }

    public function ajaxReject(string $to, string $id)
    {
        (CurrentCall::getInstance())->stop($to, $id);

        $this->rpc('Notif.incomingCallAnswer');

        $reject = new MessageReject;
        $reject->setTo($to)
            ->setId($id)
            ->request();
    }

    public function ajaxMute(string $to, string $id, $name)
    {
        $p = new SessionMute;
        $p->setTo($to)
            ->setId($id)
            ->setName($name)
            ->request();
    }

    public function ajaxUnmute(string $to, string $id, $name)
    {
        $p = new SessionUnmute;
        $p->setTo($to)
            ->setId($id)
            ->setName($name)
            ->request();
    }

    /** Content */

    public function ajaxContentAdd(string $to, string $sdp, string $id, string $mediaId)
    {
        $stj = new SDPtoJingle($this->filterSDPMedia($sdp, $mediaId), sid: $id, action: 'content-add');

        $si = new ContentAdd;
        $si->setTo($to)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxContentRemove(string $to, string $sdp, string $id, string $mediaId)
    {
        $stj = new SDPtoJingle($this->filterSDPMedia($sdp, $mediaId), sid: $id, action: 'content-remove');

        $si = new ContentRemove;
        $si->setTo($to)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxContentModify(string $to, string $sdp, string $id)
    {
        $stj = new SDPtoJingle($sdp, sid: $id, action: 'content-modify');

        $si = new ContentModify;
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

        Dialog::fill($view->draw('_visio_choose_muji'), false, true);
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
            CurrentCall::getInstance()->stop($muji->jidfrom, $muji->id);

            $resource = $muji->presence?->resource;

            if ($resource) {
                $pu = new Unavailable;
                $pu->setTo($muji->muc)
                    ->setResource($resource)
                    ->request();

                $this->me->session->mujiCalls()->where('id', $mujiId)->delete();

                (new Rooms)->onPresence($muji->jidfrom);

                // If we were the inviter, we also retract the call
                $participant = $muji->participants->firstWhere('jid', $muji->jidfrom . '/' . $resource);
                if ($participant && $participant->inviter) {
                    $retract = new Retract;
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

        Dialog::fill($view->draw('_visio_lobby'), false, true);
        $this->rpc('MovimVisio.getUserMedia', $withVideo);
    }

    public function ajaxGetLobby(string $jid, bool $calling = false, ?bool $withVideo = false, ?string $id = null)
    {
        $contact = \App\Contact::firstOrNew(['id' => \baseJid($jid)]);

        $view = $this->tpl();
        $view->assign('contact', $contact);
        $view->assign('calling', $calling);
        $view->assign('withvideo', $withVideo);
        $view->assign('id', $id);
        $view->assign('fullJid', $jid);

        Dialog::fill($view->draw('_visio_lobby'), false, true);
        $this->rpc('MovimVisio.getUserMedia', $withVideo);

        if ($calling == false) {
            $this->rpc('Notif.incomingCall');

            Notif::append(
                'call',
                '📞 ' . $contact->truename,
                $this->__('visio.calling'),
                $contact->getPicture(),
                time: 5
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
            $accept = new Accept;
            $accept->setTo($muji->jidfrom)
                ->setId($muji->id)
                ->request();

            CurrentCall::getInstance()->start($muji->jidfrom, $muji->id, mujiRoom: $muji->muc);

            $muc = new Muc;
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
            $stj = new SDPtoJingle($sdp->sdp, sid: $mujiId, muji: true);

            $muc = new Muc;
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
            Toast::send($this->__('muji.cannot_create'));
            return;
        }

        if ($conference) {
            $mujiId = generateUUID();
            $mujiConference = generateKey(withCapitals: false);
            $mujiConferenceJid = $mujiConference . '@' . $mujiService->server;

            CurrentCall::getInstance()->start($to, $mujiId, mujiRoom: $mujiConferenceJid);

            $muc = new Muc;
            $muc->setTo($mujiConferenceJid)
                ->setNickname($conference->nickname)
                ->enableCreate()
                ->enableMujiPreparing()
                ->noNotify()
                ->request();

            $invite = new Invite;
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
            sdp: $sdp->sdp,
            sid: $id,
            responder: $jid,
            action: 'session-initiate'
        );

        if ($mujiRoom) {
            $stj->setMujiRoom($mujiRoom);
        }

        $si = new SessionInitiate;
        $si->setTo($jid)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxResolveServices()
    {
        if (!me()->session) return;

        $info = \App\Info::where('server', $this->me->session->host)
            ->where('node', '')
            ->first();
        if ($info && $info->hasExternalServices()) {
            $c = new \Moxl\Xec\Action\ExternalServices\Get;
            $c->setTo($this->me->session->host)
                ->request();
        } else {
            $this->setDefaultServices();
        }
    }

    public function ajaxPrepare(string $jid)
    {
        $bareJid = \baseJid($jid);
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
            sdp: $sdp->sdp,
            sid: $id,
            responder: $to,
            action: 'session-accept'
        );

        $si = new SessionInitiate;
        $si->setTo($to)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxCandidate(string $to, string $id, $sdp)
    {
        // Firefox is passing the ufrag as an argument, Chrome as a parameter in the candidate
        $ufrag = $sdp->usernameFragment ?? null;

        $stj = new SDPtoJingle(
            sdp: 'a=' . $sdp->candidate,
            sid: $id,
            responder: $to,
            action: 'transport-info',
            mid: $sdp->sdpMid,
            ufrag: $ufrag
        );

        $si = new SessionInitiate;
        $si->setTo($to)
            ->setJingle($stj->generate())
            ->request();
    }

    public function ajaxTerminate(string $to, string $sid, ?string $reason = 'success')
    {
        $st = new SessionTerminate;
        $st->setTo($to)
            ->setJingleSid($sid)
            ->setReason($reason ?? 'success')
            ->request();
    }

    /**
     * @desc Close a one-to-one call
     */
    public function ajaxGoodbye(string $to, string $sid, ?string $reason = 'success')
    {
        if (CurrentCall::getInstance()->isStarted()) {
            CurrentCall::getInstance()->stop($to, $sid);
            $st = new MessageFinish;
            $st->setTo($to)
                ->setId($sid)
                ->setReason($reason ?? 'success')
                ->request();
        } else {
            $sr = new MessageRetract;
            $sr->setTo($to)
                ->setId($sid)
                ->request();

            $message = Message::eventMessageFactory(
                'jingle',
                baseJid($to),
                $sid
            );
            $message->type = 'jingle_retract';
            $message->save();

            Wrapper::getInstance()->iterate('jingle_message', (new Packet)->pack($message));
        }

        Toast::send($this->__('visio.ended'));
        $this->rpc('MovimJingles.terminateAll', $reason);
    }

    /**
     * @desc Force stop the current call, when a page is reloaded for example
     */
    public function ajaxTryForceStop()
    {
        $currentCall = CurrentCall::getInstance();

        if ($currentCall->isStarted()) {
            $message = Message::eventMessageFactory(
                'jingle',
                baseJid($currentCall->jid),
                $currentCall->id
            );
            $message->type = 'jingle_finish';
            $message->save();

            Wrapper::getInstance()->iterate('jingle_message', (new Packet)->pack($message));

            $this->ajaxGoodbye($currentCall->jid, $currentCall->id, 'gone');
        }
    }

    private function filterSDPMedia(string $sdp, string $mediaId)
    {
        // Ugly but simple
        $exp = explode('m=', $sdp);
        $selected = [];

        foreach ($exp as $media) {
            if (str_contains($media, 'a=mid:' . $mediaId)) {
                array_push($selected, $media);
            }
        }

        return $exp[0] . 'm=' . implode('m=', $selected);
    }
}
