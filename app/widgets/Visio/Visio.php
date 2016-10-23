<?php

use Moxl\Xec\Action\Jingle\SessionInitiate;
use Moxl\Xec\Action\Jingle\SessionTerminate;

class Visio extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('visio.css');
        $this->addjs('visio.js');

        $this->registerEvent('jingle_sessioninitiate', 'onSDP');
        $this->registerEvent('jingle_transportinfo', 'onCandidate');
        $this->registerEvent('jingle_sessionaccept', 'onAccept');
        $this->registerEvent('jingle_sessionterminate', 'onTerminate');
    }

    function onSDP($data)
    {
        list($stanza, $from) = $data;
        $jts = new JingletoSDP($stanza);

        $s = Session::start();
        $s->set('sdp', $jts->generate());

        RPC::call('VisioLink.openVisio', $from);
    }

    function ajaxGetSDP()
    {
        $s = Session::start();
        if($s->get('sdp')) {
            RPC::call('Visio.onSDP', $s->get('sdp'), 'offer');
            $s->remove('sdp');
        }
    }

    function onAccept($stanza)
    {
        $jts = new JingletoSDP($stanza);

        RPC::call('Visio.onSDP', $jts->generate(), 'answer');
    }

    function onCandidate($stanza)
    {
        $jts = new JingletoSDP($stanza);
        $sdp = $jts->generate();

        $s = Session::start();
        $candidates = $s->get('candidates');

        if(!$candidates) $candidates = [];

        array_push($candidates, [$sdp, $jts->name, substr($jts->name, -1, 1)]);

        $s->set('candidates', $candidates);
    }

    function ajaxGetCandidates()
    {
        $s = Session::start();
        $candidates = $s->get('candidates');

        if(is_array($candidates)) {
            foreach($candidates as $candidate) {
                RPC::call('Visio.onCandidate', $candidate[0], $candidate[1], $candidate[2]);
            }
        }

        $s->remove('candidates');
    }

    function onTerminate($stanza)
    {
        RPC::call('Visio.onTerminate');
    }

    function ajaxInitiate($sdp, $to)
    {
        $stj = new SDPtoJingle(
            $sdp->sdp,
            $this->user->getLogin(),
            $to,
            'session-initiate');

        $si = new SessionInitiate;
        $si->setTo($to)
           ->setOffer($stj->generate())
           ->request();
    }

    function ajaxAccept($sdp, $to)
    {
        $stj = new SDPtoJingle(
            $sdp->sdp,
            $this->user->getLogin(),
            $to,
            'session-accept');

        $si = new SessionInitiate;
        $si->setTo($to)
           ->setOffer($stj->generate())
           ->request();
    }

    function ajaxCandidate($sdp, $to)
    {
        $stj = new SDPtoJingle(
            'a='.$sdp->candidate,
            $this->user->getLogin(),
            $to,
            'transport-info',
            $sdp->sdpMid,
            $sdp->sdpMLineIndex);

        $si = new SessionInitiate;
        $si->setTo($to)
           ->setOffer($stj->generate())
           ->request();
    }

    function ajaxTerminate($to)
    {
        $s = Session::start();

        $st = new SessionTerminate;
        $st->setTo($to)
           ->setJingleSid($s->get('jingleSid'))
           ->request();
    }

    function display()
    {
    }
}
