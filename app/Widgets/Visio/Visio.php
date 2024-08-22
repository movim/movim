<?php

namespace App\Widgets\Visio;

use App\Widgets\Dialog\Dialog;
use App\Widgets\Notif\Notif;
use Movim\CurrentCall;
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

class Visio extends Base
{
    public function load()
    {
        $this->addcss('visio.css');
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

                $url = $service['type'].':'.$service['host'];
                $url .= !empty($service['port']) ? ':'.$service['port'] : '';
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

    public function onExternalServicesError($packet)
    {
        $this->setDefaultServices();
    }

    public function onPropose($packet)
    {
        $data = $packet->content;

        $contact = \App\Contact::firstOrNew(['id' => cleanJid($data['from'])]);

        $view = $this->tpl();
        $view->assign('contact', $contact);
        $view->assign('from', $data['from']);
        $view->assign('id', $data['id']);
        $view->assign('withvideo', $data['withVideo']);

        Dialog::fill($view->draw('_visio_dialog'), false, true);

        $this->rpc('Notif.incomingCall');

        Notif::append(
            'call',
            $contact->truename,
            $this->__('visio.calling'),
            $contact->getPicture(),
            5
        );
    }

    public function onInitiateSDP($data)
    {
        list($stanza, $from) = $data;

        $jts = new JingletoSDP($stanza);

        $this->rpc('Visio.onInitiateSDP', $jts->generate());
    }

    public function onContentAdd($stanza)
    {
        $jts = new JingletoSDP($stanza);

        $this->rpc('Visio.onContentAdd', $jts->generate());
    }

    public function onProceed($packet)
    {
        $data = $packet->content;
        $this->rpc('Visio.onProceed', $data['from'], $data['id']);
    }

    public function onAccept($packet)
    {
        $this->rpc('Notif.incomingAnswer');
        (new Dialog)->ajaxClear();
    }

    public function onAcceptSDP($stanza)
    {
        $jts = new JingletoSDP($stanza);
        $this->rpc('Visio.onAcceptSDP', $jts->generate());
    }

    public function onCandidate($stanza)
    {
        $jts = new JingletoSDP($stanza);
        $sdp = $jts->generate();

        $this->rpc('Visio.onCandidate', $sdp, (string)$jts->name, $jts->name);
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

        if (CurrentCall::getInstance()->isStarted()) {
            CurrentCall::getInstance()->stop();
        }

        $this->rpc('Visio.goodbye', $reason);
    }

    public function onMute($name)
    {
        $this->rpc('Visio.onMute', $name);
    }

    public function onUnmute($name)
    {
        $this->rpc('Visio.onUnmute', $name);
    }

    public function ajaxPropose($to, $id, $withVideo = false)
    {
        $p = new SessionPropose;
        $p->setTo($to)
          ->setId($id)
          ->setWithVideo($withVideo)
          ->request();
    }

    public function ajaxAccept($to, $id)
    {
        $p = new SessionAccept;
        $p->setTo($to)
          ->setId($id)
          ->request();
    }

    public function ajaxReject($to, $id)
    {
        $this->rpc('Notification.incomingAnswer');
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

    public function ajaxSessionInitiate($sdp, $to, $id)
    {
        $stj = new SDPtoJingle(
            $sdp->sdp,
            $this->user->id,
            $to,
            'session-initiate'
        );
        $stj->setSessionId($id);

        $si = new SessionInitiate;
        $si->setTo($to)
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
        $bareJid =\explodeJid($jid)['jid'];
        $contact = \App\Contact::firstOrNew(['id' => $bareJid]);

        $view = $this->tpl();
        $view->assign('contact', $contact);

        $this->rpc('MovimVisio.moveToChat', $bareJid);
        $this->rpc('MovimTpl.fill', '#visio_contact', $view->draw('_visio_contact'));
        $this->rpc('Visio.init', $bareJid);
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

    public function ajaxSessionAccept($sdp, string $to, string $id)
    {
        $stj = new SDPtoJingle(
            $sdp->sdp,
            $this->user->id,
            $to,
            'session-accept'
        );
        $stj->setSessionId($id);

        $si = new SessionInitiate;
        $si->setTo($to)
           ->setOffer($stj->generate())
           ->request();
    }

    public function ajaxCandidate($sdp, string $to, string $id)
    {
        // Firefox is passing the ufrag as an argument, Chrome as a parameter in the candidate
        $ufrag = $sdp->usernameFragment ?? null;

        $stj = new SDPtoJingle(
            'a='.$sdp->candidate,
            $this->user->id,
            $to,
            'transport-info',
            $sdp->sdpMid,
            $ufrag
        );
        $stj->setSessionId($id);

        $si = new SessionInitiate;
        $si->setTo($to)
           ->setOffer($stj->generate())
           ->request();
    }

    public function ajaxEnd(string $to, string $sid, $reason = 'success')
    {
        if (CurrentCall::getInstance()->isStarted()) {
            CurrentCall::getInstance()->stop();
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
