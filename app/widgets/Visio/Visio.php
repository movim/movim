<?php

use Moxl\Xec\Action\Jingle\SessionPropose;
use Moxl\Xec\Action\Jingle\SessionAccept;
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
        $this->addjs('visio_utils.js');

        $this->registerEvent('jinglepropose', 'onPropose');
        $this->registerEvent('jingleaccept', 'onAccept');
        $this->registerEvent('jingle_sessioninitiate', 'onInitiateSDP');
        $this->registerEvent('jingle_sessionaccept', 'onAcceptSDP');
        $this->registerEvent('jingle_transportinfo', 'onCandidate');
        $this->registerEvent('jingle_sessionterminate', 'onTerminate');
    }

    public function onPropose($packet)
    {
        $data = $packet->content;

        $contact = \App\Contact::firstOrNew(['id' => cleanJid($data['from'])]);

        $view = $this->tpl();
        $view->assign('contact', $contact);
        $view->assign('from', $data['from']);
        $view->assign('id', $data['id']);

        Dialog::fill($view->draw('_visio_dialog'));

        Notification::append(
            'call',
            $contact->truename,
            $this->__('visio.calling'),
            $contact->getPhoto(),
            5,
            null,
            null,
            'VisioLink.openVisio(\''.$data['from'].'\', \''.$data['id'].'\'); Dialog_ajaxClear()'
        );
    }

    public function onInitiateSDP($data)
    {
        list($stanza, $from) = $data;

        $jts = new JingletoSDP($stanza);

        $this->rpc('Visio.onInitiateSDP', $jts->generate());
    }

    public function onAccept($packet)
    {
        $data = $packet->content;
        $this->rpc('Visio.onAccept', $data['from'], $data['id']);
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

    public function onTerminate($reason)
    {
        $this->rpc('Visio.onTerminate', $reason);
    }

    public function ajaxPropose($to, $id)
    {
        $p = new SessionPropose;
        $p->setTo($to)
          ->setId($id)
          ->request();
    }

    public function ajaxAccept($to, $id)
    {
        $p = new SessionAccept;
        $p->setTo($to)
          ->setId($id)
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

    public function ajaxSessionAccept($sdp, $to, $id)
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

    public function ajaxCandidate($sdp, $to)
    {
        $stj = new SDPtoJingle(
            'a='.$sdp->candidate,
            $this->user->id,
            $to,
            'transport-info',
            $sdp->sdpMid
        );

        $si = new SessionInitiate;
        $si->setTo($to)
           ->setOffer($stj->generate())
           ->request();
    }

    public function ajaxTerminate($to, $reason = 'success')
    {
        $s = Session::start();

        $st = new SessionTerminate;
        $st->setTo($to)
           ->setJingleSid($s->get('jingleSid'))
           ->setReason($reason)
           ->request();
    }

    public function display()
    {
        $this->view->assign('contact', \App\Contact::firstOrNew(['id' => $this->get('f')]));
    }
}
