<?php

use Movim\Widget\Base;

class VisioLink extends Base
{
    public function load()
    {
        $this->registerEvent('jingle_transportinfo', 'onCandidate');
        $this->registerEvent('jingle_sessioninitiate', 'onSDP');

        $this->addjs('visiolink.js');
        $this->addcss('visiolink.css');
    }

    public function onSDP($data)
    {
        list($stanza, $from) = $data;
        $jts = new JingletoSDP($stanza);

        $this->rpc('VisioLink.setSDP', $jts->generate());
    }

    public function onCandidate($stanza)
    {
        $jts = new JingletoSDP($stanza);
        $sdp = $jts->generate();

        $this->rpc('VisioLink.setCandidate', [$sdp, $jts->name, substr($jts->name, -1, 1)]);
    }

    public function ajaxDecline($to)
    {
        $visio = new Visio;
        $visio->ajaxTerminate($to, 'decline');
    }
}
