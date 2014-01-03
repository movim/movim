<?php 

class JingletoSDP {
    private $sdp;
    private $jingle;
    private $jid;

    private $iceufrag = false;
    private $icepwd   = false;
    private $icefingerprint     = false;
    private $icefingerprinthash = false;
    
    private $valid    = false;

    function __construct($jingle) {
        $this->jingle = $jingle;
    }

    function generate() {

		$username = substr($this->jingle['initiator'], 0, strpos($this->jingle['initiator'], '@'));
		$sessid = $this->jingle['sid'];
        
        if($this->jingle->sdp)
            return $this->jingle->sdp;
        
        foreach($this->jingle->children() as $content) {
            $this->icepwd = $content->transport->attributes()->pwd;
            $this->iceufrag = $content->transport->attributes()->ufrag;
            
            if($content->transport->fingerprint) {
                $this->icefingerprint = $content->transport->fingerprint;
                $this->icefingerprinthash = $content->transport->fingerprint->attributes()->hash;
            }
            
            //payload and candidate
            $p = $c = '';
            $priority = '';
            $port = false;
            $ip = false;
            
            //$proto = "RTP/AVP ";
            $proto = "RTP/SAVPF ";
            
            foreach($content->description->children() as $payload) {
                //payloads without clockrate are striped out
                if($payload->attributes()->clockrate){
                    $p .= 
                        "\na=rtpmap".
                        ':'.$payload->attributes()->id.
                        ' '.$payload->attributes()->name.
                        '/'.$payload->attributes()->clockrate;
                        
                    $priority .= ' '.$payload->attributes()->id;
                    //if (!$priority) $priority = $payload->attributes()->id;
                }
                /*elseif($payload->attributes()->required){ //this is an encryption request, not a payload
                    $proto = "RTP/SAVP ";
                }*/
            }
                
            foreach($content->transport->children() as $candidate) {
                if($candidate->getName() == 'candidate'){
                    $c .= //http://tools.ietf.org/html/rfc5245#section-15
                        "\na=candidate:".$candidate->attributes()->foundation.
                        ' '.$candidate->attributes()->component.
                        ' '.strtoupper($candidate->attributes()->protocol).
                        ' '.$candidate->attributes()->priority.
                        ' '.$candidate->attributes()->ip.
                        ' '.$candidate->attributes()->port.
                        ' typ '.$candidate->attributes()->type;

                    if($port == false)
                        $port = $candidate->attributes()->port;
                    
                    if($ip == false)
                        $ip = $candidate->attributes()->ip;
                    
                    if($candidate->attributes()->type == 'srflx') {
                        $c .= 
                            ' raddr '.$candidate->attributes()->{'rel-addr'}.
                            ' rport '.$candidate->attributes()->{'rel-port'};
                    }
                    $c .= ' generation '.$candidate->attributes()->generation;
                    
                    $this->valid = true;
                }
            }
            
            $this->sdp .= //http://tools.ietf.org/html/rfc4566#page-22
                "\nm=".$content->description->attributes()->media.
                ' '.$port.
                ' '.$proto.
                $priority.
                "\nc=IN IP4 ".$ip."\n".
                $p.
                //'a=setup:actpass'."\n".
                $c;
                //'a=rtcp-mux'."\n";
        }
        
        if($this->iceufrag && $this->icepwd) {
            $ice = 
                "\na=ice-ufrag:".$this->iceufrag.
                "\na=ice-pwd:".$this->icepwd;
            if($this->icefingerprint && $this->icefingerprinthash)
                $ice .= 
                    "\na=fingerprint:".
                    $this->icefingerprinthash.
                    ' '.
                    $this->icefingerprint;
        } else {
            $ice = '';
        }
        
        $this->sdp = 
            'v=0'.
            "\no=".$username.' '.base_convert($sessid, 30, 10).' 0 IN IP4 0.0.0.0'.
            "\ns=TestCall".
            "\nt=0 0".
            $ice.
            $this->sdp;
            
        if($this->valid)
            return $this->sdp;
        else
            return false;
    }
}
