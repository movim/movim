<?php

namespace App\Widgets\Visio;

use App\MujiCall;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Notif\Notif;
use Movim\CurrentCalls;
use Movim\Librairies\JingletoSDP;
use Movim\Librairies\SDPtoJingle;
use Moxl\Xec\Action\Jingle\SessionPropose;
use Moxl\Xec\Action\Jingle\SessionAccept;
use Moxl\Xec\Action\Jingle\SessionInitiate;
use Moxl\Xec\Action\Jingle\SessionTerminate;
use Moxl\Xec\Action\Jingle\SessionMute;
use Moxl\Xec\Action\Jingle\SessionUnmute;

use Movim\Widget\Base;
use Moxl\Xec\Action\Jingle\SessionReject;
use Moxl\Xec\Action\Jingle\SessionRetract;
use Moxl\Xec\Action\JingleCallInvite\Accept;
use Moxl\Xec\Action\JingleCallInvite\Invite;
use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Presence\Unavailable;
use Moxl\Xec\Payload\Packet;

class Visio extends Base
{
    public function load()
    {
        $this->addcss('visio.css');
        $this->addcss('visio_lobby.css');
        $this->addjs('visio.js');
        $this->addjs('visio_utils.js');
        $this->addjs('visio_dtmf.js');

        $this->registerEvent('jinglepropose', 'onPropose');
        $this->registerEvent('jingleproceed', 'onProceed');
        $this->registerEvent('jingleaccept', 'onAccept');
        $this->registerEvent('jingleretract', 'onTerminateRetract');
        $this->registerEvent('jinglereject', 'onTerminateReject');

        $this->registerEvent('jingle_sessioninitiate', 'onInitiateSDP');
        $this->registerEvent('jingle_sessioninitiate_erroritemnotfound', 'onTerminateNotFound');
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
    }

    public function onSessionDown()
    {
        $currentCall = CurrentCalls::getInstance();

        if ($currentCall->isStarted()) {
            $st = new SessionTerminate;
            $st->setTo($currentCall->jid)
                ->setJingleSid($currentCall->id)
                ->setReason('failed-application')
                ->request();

            $currentCall->stop();
        }
    }

    public function onExternalServices($packet)
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
            $this->rpc('Visio.setServices', $externalServices);
        } else {
            $this->setDefaultServices();
        }
    }

    public function onMujiPresence($packet)
    {
        list($stanza, $presence) = $packet->content;
        if ($stanza && $stanza->muji && $stanza->muji->attributes()->xmlns == 'urn:xmpp:jingle:muji:0'
        && $stanza->muji->content && $stanza->muji->content->attributes()->xmlns == 'urn:xmpp:jingle:1') {
            // Received twice per user ?!
            \logDebug($packet->from);

            $this->rpc('Visio.initMujiParticipant', $packet->from, \baseJid($packet->from), $presence->jid);
        }
    }

    public function onExternalServicesError($packet)
    {
        $this->setDefaultServices();

        $this->rpc('MovimJingles.onInitiateSDP', \baseJid($packet->from), $jts->generate());
    }

    public function onPropose($packet)
    {
        $data = $packet->content;

        $this->ajaxGetLobby($data['from'], false, $data['withVideo'], $data['id']);
    }

    public function onInitiateSDP(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc('MovimJingles.onInitiateSDP', \baseJid($packet->from), $jts->generate());
    }

    public function onContentAdd(Packet $packet)
    {
        $jts = new JingletoSDP($packet->content);

        $this->rpc('MovimJingles.onContentAdd', \baseJid($packet->from), $jts->generate());
    }

    public function onProceed(Packet $packet)
    {
        $this->rpc('MovimJingles.onProceed', \baseJid($packet->from), $packet->from, $packet->content /* id */);
    }

    public function onAccept($packet)
    {
        $this->rpc('Notif.incomingAnswer');
        (new Dialog)->ajaxClear();
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

        $this->rpc('MovimJingles.onCandidate', $packet->from, $sdp, (string)$jts->name, $jts->name);
    }

    public function onTerminateRetract()
    {
        $this->onTerminate('retract');
    }

    public function onTerminateReject()
    {
        $this->onTerminate('reject');
    }

    public function onTerminateNotFound()
    {
        $this->onTerminate('notfound');
    }

    public function onTerminate($reason)
    {
        // Stop calling sound and clear the Dialog if there
        $this->rpc('Notif.incomingAnswer');
        (new Dialog)->ajaxClear();

        if (CurrentCalls::getInstance()->isStarted()) {
            CurrentCalls::getInstance()->stop();
        }

        $this->rpc('Visio.goodbye', $reason);
    }

    public function onMute(Packet $packet)
    {
        $this->rpc('MovimJingles.onMute', $packet->from, $packet->content);
    }

    public function onUnmute(Packet $packet)
    {
        $this->rpc('MovimJingles.onUnmute', $packet->from, $packet->content);
    }

    public function ajaxPropose(string $to, string $id, $withVideo = false)
    {
        $p = new SessionPropose;
        $p->setTo($to)
            ->setId($id)
            ->setWithVideo($withVideo)
            ->request();
    }

    public function ajaxAccept(string $to, string $id)
    {
        $p = new SessionAccept;
        $p->setTo($to)
            ->setId($id)
            ->request();
    }

    public function ajaxReject($to, $id)
    {
        $this->rpc('Notifs.incomingAnswer');
        $reject = new SessionReject;
        $reject->setTo($to)
            ->setId($id)
            ->request();
    }

    public function ajaxMute($to, $id, $name)
    {
        $p = new SessionMute;
        $p->setTo($to)
            ->setId($id)
            ->setName($name)
            ->request();
    }

    public function ajaxUnmute($to, $id, $name)
    {
        $p = new SessionUnmute;
        $p->setTo($to)
            ->setId($id)
            ->setName($name)
            ->request();
    }

    public function ajaxJoinMuji(string $mujiId, ?bool $withVideo = false)
    {
        $muji = $this->user->session->mujiCalls()
            ->where('id', $mujiId)
            ->with('conference')
            ->first();

        if ($muji) {
            $muc = new Muc;
            $muc->setTo($muji->muc)
                ->setNickname($muji->conference ? $muji->conference->nickname : $this->user->nickname)
                //->enableMujiPreparing()
                ->noNotify()
                ->request();

            $this->ajaxGetMujiLobby($muji->jidfrom, false, $withVideo, $muji->id);
        }
    }

    public function ajaxLeaveMuji(string $mujiId)
    {
        $muji = $this->user->session->mujiCalls()
            ->where('id', $mujiId)
            ->with('conference')
            ->first();

        if ($muji && CurrentCalls::getInstance()->isJidInCall($muji->jidfrom)) {
            $resource = $muji->presence?->resource;

            if ($resource) {
                $pu = new Unavailable;
                $pu->setTo($muji->muc)
                   ->setResource($resource)
                   ->request();

                CurrentCalls::getInstance()->stop();

                $this->user->session->mujiCalls()->where('id', $mujiId)->delete();
            }

            $this->rpc('MovimVisio.clear');
        }
    }

    public function ajaxGetMujiLobby(string $jid, bool $calling = false, ?bool $withVideo = false, ?string $id = null)
    {
        $view = $this->tpl();
        $view->assign('conference', $this->user->session
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
                $contact->truename,
                $this->__('visio.calling'),
                $contact->getPicture(),
                5
            );
        }
    }

    public function ajaxMujiAccept(string $mujiId)
    {
        $muji = $this->user->session->mujiCalls()
            ->where('id', $mujiId)
            ->with('conference')
            ->first();

        if ($muji) {
            CurrentCalls::getInstance()->start($muji->jidfrom, $muji->id, mujiRoom: $muji->muc);

            $accept = new Accept;
            $accept->setTo($muji->jidfrom)
                   ->setId($muji->id)
                   ->request();

                   \logDebug('ACCEPT MUJI');
            $muc = new Muc;
            $muc->setTo($muji->jidfrom)
                ->setNickname($muji->conference->nickname)
                ->enableMujiPreparing()
                ->noNotify()
                ->request();

            $this->rpc('Chat_ajaxGetHeader', $muji->jidfrom, true);
        }
    }

    public function ajaxMujiTrigger()
    {
        $muji = $this->user->session->mujiCalls()->first();

        $this->rpc('Visio.init', $muji->jidfrom, $muji->jidfrom, $muji->id, $muji->video, true);
    }

    public function ajaxMujiInit(string $mujiId, $sdp)
    {
        $muji = $this->user->session->mujiCalls()
            ->where('id', $mujiId)
            ->with('conference')
            ->first();

        if ($muji) {
            $stj = new SDPtoJingle($sdp->sdp, $this->user->id, '', true);

            $muc = new Muc;
            $muc->setTo($muji->muc)
                ->setNickname($muji->conference ? $muji->conference->nickname : $this->user->nickname)
                ->setMuji($stj->generate())
                ->noNotify()
                ->request();
        }
    }

    public function ajaxMujiCreate(string $to, bool $withVideo = false)
    {
        $conference = $this->user->session
            ->conferences()->where('conference', $to)
            ->first();

        if ($conference) {
            $mujiId = generateUUID();
            $mujiConference = generateKey(withCapitals: false);
            $mujiConferenceJid = $mujiConference . '@conference.movim.eu';

            $muc = new Muc;
            $muc->setTo($mujiConferenceJid)
                ->setNickname($conference->nickname)
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

            CurrentCalls::getInstance()->start($to, $mujiId, mujiRoom: $mujiConferenceJid);
        }

    }

    public function ajaxSessionInitiate(string $jid, $sdp, string $id, ?string $mujiRoom = null)
    {
        $stj = new SDPtoJingle(
            $sdp->sdp,
            $this->user->id,
            $id,
            false,
            $jid,
            'session-initiate'
        );

        if ($mujiRoom) {
            $stj->setMujiRoom($mujiRoom);
            CurrentCalls::getInstance()->startMuji($mujiRoom, $jid, $id);
        }

        $si = new SessionInitiate;
        $si->setTo($jid)
            ->setOffer($stj->generate())
            ->request();
    }

    public function ajaxResolveServices()
    {
        if (!$this->user->session) return;

        $info = \App\Info::where('server', $this->user->session->host)
            ->where('node', '')
            ->first();
        if ($info && $info->hasExternalServices()) {
            $c = new \Moxl\Xec\Action\ExternalServices\Get;
            $c->setTo($this->user->session->host)
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
        //$this->rpc('Visio.init', $bareJid);
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
        $this->rpc('Visio.setServices', [['urls' => array_slice($servers, 0, 2)]]);
    }

    public function ajaxGetStates()
    {
        $this->rpc('Visio.setStates', [
            'calling' => $this->__('visio.calling'),
            'ringing' => $this->__('visio.ringing'),
            'in_call' => $this->__('visio.in_call'),
            'failed' => $this->__('visio.failed'),
            'connecting' => $this->__('visio.connecting'),
            'ended' =>  $this->__('visio.ended'),
            'declined' => $this->__('visio.declined')
        ]);
    }

    public function ajaxSessionAccept(string $to, string $id, $sdp)
    {
        $stj = new SDPtoJingle(
            $sdp->sdp,
            $this->user->id,
            $id,
            false,
            $to,
            'session-accept'
        );

        $si = new SessionInitiate;
        $si->setTo($to)
            ->setOffer($stj->generate())
            ->request();
    }

    public function ajaxCandidate(string $to, string $id, $sdp)
    {
        // Firefox is passing the ufrag as an argument, Chrome as a parameter in the candidate
        $ufrag = $sdp->usernameFragment ?? null;

        $stj = new SDPtoJingle(
            'a=' . $sdp->candidate,
            $this->user->id,
            $id,
            false,
            $to,
            'transport-info',
            $sdp->sdpMid,
            $ufrag
        );

        $si = new SessionInitiate;
        $si->setTo($to)
            ->setOffer($stj->generate())
            ->request();
    }

    public function ajaxEnd(string $to, string $sid, $reason = 'success')
    {
        if (CurrentCalls::getInstance()->isStarted()) {
            CurrentCalls::getInstance()->stop();
            $st = new SessionTerminate;
            $st->setTo($to)
                ->setJingleSid($sid)
                ->setReason($reason)
                ->request();
        } else {
            $sr = new SessionRetract;
            $sr->setTo($to)
                ->setId($sid)
                ->request();
        }
    }
}
