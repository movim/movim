<?php
class SDPtoJingle {
    private $sdp;
    private $jingle;
    private $jid;

    private $iceufrag;
    private $icepwd;

    function __construct($sdp, $jid = '') {
        $this->sdp = $sdp;
        $this->jingle = new SimpleXMLElement('<jingle></jingle>');
        $this->jingle->addAttribute('xmlns', 'urn:xmpp:jingle:1');
        $this->jingle->addAttribute('action','session-initiate');
        $this->jingle->addAttribute('initiator',$jid);
    }

    function generate() {
        $arr = explode("\r", str_replace("\n", "", $this->sdp));

        $m = false;

        foreach($arr as $l) {
            list($key, $line) = explode('=', $l);
            switch($key) {
                case 'm':
                    $expl = explode(' ', $line);
                    $content = $this->jingle->addChild('content');
                    $content->addAttribute('creator', 'initiator');
                    $content->addAttribute('name', $expl[0]);

                    // The description node
                    $description = $content->addChild('description');
                    $description->addAttribute('xmlns', "urn:xmpp:jingle:apps:rtp:11");

                    // The transport node
                    $transport = $content->addChild('transport');
                    $transport->addAttribute('xmlns', "xmlns='urn:xmpp:jingle:transports:ice-udp:1");
                    $transport->addAttribute('pwd', $this->icepwd);
                    $transport->addAttribute('ufrag', $this->iceufrag);
                    $m = true;
                    break;
                case 'a':
                    if($m) {
                        $expl = explode(' ', $line);
                        // We have a new candidate !
                        if(count($expl) > 5) {
                            // We explode the candidate:0 or :1
                            $candidexpl = explode(':', $expl[0]);
                            
                            $candidate = $transport->addChild('candidate');
                            
                            $candidate->addAttribute('componenent', $candidexpl[1]);
                            $candidate->addAttribute('foundation', $expl[1]);
                            $candidate->addAttribute('generation', 0);
                            $candidate->addAttribute('protocol', $expl[2]);
                            $candidate->addAttribute('priority', $expl[3]);
                            $candidate->addAttribute('ip', $expl[4]);
                            $candidate->addAttribute('port', $expl[5]);
                            $candidate->addAttribute('type', $expl[7]);
                            $candidate->addAttribute('id', \generateKey(10));

                            if(isset($expl[9]))
                                $candidate->addAttribute('rel-addr', $expl[9]);
                            if(isset($expl[11]))
                                $candidate->addAttribute('rel-port', $expl[11]);
                        }

                        $expl = explode(':', $exp[1]);
                        switch($expl[0]) {
                            // We have a new codec !
                            case 'rtpmap':
                                $rtpmap = explode(' ', $expl[1]);
                                list($codec, $freq) = explode('/',$rtpmap[1]);

                                $payloadtype = $description->addChild('payload-type');
                                $payloadtype->addAttribute('id', $rtpmap[0]);
                                $payloadtype->addAttribute('name', $codec);
                                $payloadtype->addAttribute('clockrate', $freq);
                                break;
                        }
                    } else {
                        $expl = explode(':', $line);
                        // Some ICE ids
                        switch($expl[0]) {
                            case 'ice-pwd':
                                $this->icepwd = $expl[1];
                                break;
                            case 'ice-ufrag':
                                $this->iceufrag = $expl[1];
                                break;
                           
                        }
                    }
                    break;
            }

        }

        return $this->jingle->asXML();
    }
}
