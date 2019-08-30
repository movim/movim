<?php

use Moxl\Xec\Action\Jingle\SessionInitiate;
use Moxl\Xec\Action\Jingle\SessionTerminate;

use Movim\Widget\Base;
use Movim\Session;

class Visio extends Base
{
    public function load()
    {
        $this->addcss('visio.css');
        $this->addjs('visio.js');

        $this->registerEvent('jingle_sessioninitiate', 'onSDP');
        $this->registerEvent('jingle_transportinfo', 'onCandidate');
        $this->registerEvent('jingle_sessionaccept', 'onAccept');
        $this->registerEvent('jingle_sessionterminate', 'onTerminate');
    }

    public function onSDP($data)
    {
        list($stanza, $from) = $data;
        $jts = new JingletoSDP($stanza);

        $s = Session::start();
        $s->set('sdp', $jts->generate());

        $contact = \App\Contact::firstOrNew(['id' => cleanJid($from)]);

        $view = $this->tpl();
        $view->assign('contact', $contact);
        $view->assign('from', $from);

        Dialog::fill($view->draw('_visio_dialog'));

        Notification::append(
            'call',
            $contact->truename,
            $this->__('visio.calling'),
            $contact->getPhoto(),
            5,
            null,
            null,
            'VisioLink.openVisio(\''.$from.'\'); Dialog_ajaxClear()'
        );
    }

    public function ajaxAskInit()
    {
        $s = Session::start();
        if ($s->get('sdp')) {
            $this->rpc('Visio.init', $s->get('sdp'), 'offer');
            $s->remove('sdp');
        } else {
            $this->rpc('Visio.init');
        }
    }

    public function onAccept($stanza)
    {
        $jts = new JingletoSDP($stanza);
        $this->rpc('Visio.onSDP', $jts->generate(), 'answer');
    }

    public function onCandidate($stanza)
    {
        $jts = new JingletoSDP($stanza);
        $sdp = $jts->generate();

        $s = Session::start();
        $candidates = $s->get('candidates');

        if (!$candidates) {
            $candidates = [];
        }

        array_push($candidates, [$sdp, $jts->name, substr($jts->name, -1, 1)]);

        $s->set('candidates', $candidates);

        $this->rpc('Visio.onCandidate', $sdp, $jts->name, substr($jts->name, -1, 1));
    }

    public function ajaxGetCandidates()
    {
        $s = Session::start();
        $candidates = $s->get('candidates');

        if (is_array($candidates)) {
            $this->rpc('Visio.onCandidates', $candidates);
        }

        $s->remove('candidates');
    }

    public function onTerminate($stanza)
    {
        $this->clearCandidates();
        $this->rpc('Visio.onTerminate');
    }

    public function ajaxInitiate($sdp, $to)
    {
        $this->clearCandidates();

        $stj = new SDPtoJingle(
            $sdp->sdp,
            $this->user->id,
            $to,
            'session-initiate'
        );

        $si = new SessionInitiate;
        $si->setTo($to)
           ->setOffer($stj->generate())
           ->request();
    }

    public function ajaxAccept($sdp, $to)
    {
        $stj = new SDPtoJingle(
            $sdp->sdp,
            $this->user->id,
            $to,
            'session-accept'
        );

        $si = new SessionInitiate;
        $si->setTo($to)
           ->setOffer($stj->generate())
           ->request();
    }

    public function ajaxCandidate($sdp, $to)
    {
        $stj = new SDPtoJingle(
            'a='.$sdp->candidate,
            $this->user->id,
            $to,
            'transport-info',
            $sdp->sdpMid,
            $sdp->sdpMLineIndex
        );

        $si = new SessionInitiate;
        $si->setTo($to)
           ->setOffer($stj->generate())
           ->request();
    }

    public function ajaxTerminate($to, $reason = 'success')
    {
        $this->clearCandidates();

        $s = Session::start();

        $st = new SessionTerminate;
        $st->setTo($to)
           ->setJingleSid($s->get('jingleSid'))
           ->setReason($reason)
           ->request();
    }

    private function clearCandidates()
    {
        $s = Session::start();
        $s->remove('candidates');
    }

    public function display()
    {
        $this->view->assign('contact', \App\Contact::firstOrNew(['id' => $this->get('f')]));
    }
}
