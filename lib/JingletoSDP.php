<?php 

class JingletoSDP {
    private $sdp;
    private $jingle;
    private $jid;

    private $iceufrag;
    private $icepwd;

    function __construct($jingle) {
        $this->jingle = $jingle;
    }

    function generate() {
        foreach($this->jingle->children() as $content) {
            $this->icepwd = $content->transport->attributes()->pwd;
            $this->iceufrag = $content->transport->attributes()->ufrag;
            
            $p = $c = '';
            $priority = '';
            $port = false;
            $ip = false;
            
            foreach($content->description->children() as $payload) {
                $p .= 
                    'a=rtpmap'.
                    ':'.$payload->attributes()->id.
                    ' '.$payload->attributes()->name.
                    '/'.$payload->attributes()->clockrate.
                    "\n";
                    
                $priority .= ' '.$payload->attributes()->id;
            }
                
            foreach($content->transport->children() as $candidate) {
                $c .= 
                    'a=candidate:'.$candidate->attributes()->component.
                    ' '.$candidate->attributes()->foundation.
                    ' '.$candidate->attributes()->protocol.
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
                
                $c .= "\n";
            }
            
            $this->sdp .= 
                'm='.$content->description->attributes()->media.
                ' '.$port.
                ' RTP/SAVPF'.
                $priority.
                "\n".
                'c=IN IP4 '.$ip."\n".
                $p.
                'a=setup:actpass'."\n".
                $c.
                'a=rtcp-mux'."\n";
        }
        
        $this->sdp = 
            'v=0'."\n".
            'o=Mozilla-SIPUA-29.0a1 2019 0 IN IP4 0.0.0.0'."\n".
            's=SIP Call'."\n".
            't=0 0'."\n".
            'a=ice-ufrag:'.$this->iceufrag."\n".
            'a=ice-pwd:'.$this->icepwd."\n".
            'a=fingerprint:sha-256 D4:E6:DC:30:3F:63:0A:55:8D:65:F6:7C:F7:81:47:F8:3D:45:74:EE:74:61:CB:9A:F5:4F:60:79:F2:2D:D2:20'."\n".
            $this->sdp;
        
        return $this->sdp;
    }
}
