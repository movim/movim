<?php 

class JingletoSDP {
    private $sdp;
    private $jingle;
    private $jid;

    private $iceufrag = false;
    private $icepwd   = false;
    
    private $valid    = false;

    function __construct($jingle) {
        $this->jingle = $jingle;
    }

    function generate() {

		$username = substr($this->jingle['initiator'], 0, strpos($this->jingle['initiator'], '@'));
		$sessid = $this->jingle['sid'];
        foreach($this->jingle->children() as $content) {
            $this->icepwd = $content->transport->attributes()->pwd;
            $this->iceufrag = $content->transport->attributes()->ufrag;
            
            //payload and candidate
            $p = $c = '';
            $priority = '';
            $port = false;
            $ip = false;
            
            foreach($content->description->children() as $payload) {
                //paylods without clockrate are striped out
                if($payload->attributes()->clockrate){
                    $p .= 
                        'a=rtpmap'.
                        ':'.$payload->attributes()->id.
                        ' '.$payload->attributes()->name.
                        '/'.$payload->attributes()->clockrate.
                        "\n";
                        
                    $priority .= ' '.$payload->attributes()->id;
                }
            }
                
            foreach($content->transport->children() as $candidate) {
                $c .= 
                    'a=candidate:'.$candidate->attributes()->component.
                    ' '.$candidate->attributes()->foundation.
                    ' '.strtoupper($candidate->attributes()->protocol).
                    ' '.$candidate->attributes()->priority.
                    ' '.$candidate->attributes()->ip.
                    ' '.$candidate->attributes()->port.
                    ' typ '.$candidate->attributes()->type.
                    ' generation '.$candidate->attributes()->generation;

                if($port == false)
                    $port = $candidate->attributes()->port;
                
                if($ip == false)
                    $ip = $candidate->attributes()->ip;
                
                if($candidate->attributes()->type == 'srflx') {
                   $c .= 
                        ' raddr '.$candidate->attributes()->{'rel-addr'}.
                        ' rport '.$candidate->attributes()->{'rel-port'};
                }
                
                $c .= "\n";
                
                $this->valid = true;
            }
            
            $this->sdp .= 
                'm='.$content->description->attributes()->media.
                ' '.$port.
                ' RTP/SAVPF'.
                $priority.
                "\n".
                'c=IN IP4 '.$ip."\n".
                $p.
                //'a=setup:actpass'."\n".
                $c;
                //'a=rtcp-mux'."\n";
        }
        
        if($this->iceufrag && $this->icepwd) {
            $ice = 
                'a=ice-ufrag:'.$this->iceufrag."\n".
                'a=ice-pwd:'.$this->icepwd."\n";
        } else {
            $ice = '';
        }
        
        $this->sdp = 
            'v=0'."\n".
            'o='.$username.' '.substr(base_convert($sessid, 30, 10), 0, 6).' 0 IN IP4 0.0.0.0'."\n".
            's=TestCall'."\n".
            't=0 0'."\n".
            $ice.
            'a=fingerprint:sha-256 D4:E6:DC:30:3F:63:0A:55:8D:65:F6:7C:F7:81:47:F8:3D:45:74:EE:74:61:CB:9A:F5:4F:60:79:F2:2D:D2:20'."\n".
            $this->sdp;
            
        if($this->valid)
            return $this->sdp;
        else
            return false;
    }
}
