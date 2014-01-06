<?php 

class JingletoSDP {
    private $sdp = '';
    private $jingle;
    
    private $values = array(
        'session_id'        => 1,
        'session_version'   => 0,
        'nettype'           => 'IN',
        'addrtype'          => 'IP4',
        'unicast_address'   => '0.0.0.0'
        );
    
    function __construct($jingle) {
        $this->jingle = $jingle;
    }

    function generate() {
        $username = substr($this->jingle->attributes()->initiator, 0, strpos("@", $this->jingle->attributes()->initiator));//sinon le - marche pas
        $username = $username? $username : "-";
        $sessid   = $this->jingle->attributes()->sid;
        $this->values['session_id']   = substr(base_convert($sessid, 30, 10), 0, 6);
        
        $sdp_version =
            'v=0';
            
        $sdp_origin = 
            'o='.
            $username.' '.
            $this->values['session_id'].' '.
            $this->values['session_version'].' '.
            $this->values['nettype'].' '.
            $this->values['addrtype'].' '.
            $this->values['unicast_address'];
            
        $sdp_session_name =
            's=SIP Call'; // Use the sessid ?
            
        $sdp_timing =
            't=0 0';
        
        $sdp_medias = '';
            
        foreach($this->jingle->children() as $content) {
            $media_header_ids = array();
            
            $sdp_media_header = 
                "\nm=".$content->description->attributes()->media.
                ' 1 ';

            if(isset($content->description->crypto)
            || isset($content->transport->fingerprint)) {
                $sdp_media_header .= 'RTP/SAVPF';
            } else {
                $sdp_media_header .= 'RTP/AVPF';
            }

            $sdp_media = 
                "\nc=IN IP4 0.0.0.0".
                "\na=rtcp:1 IN IP4 0.0.0.0";
                
            if(isset($content->transport->attributes()->ufrag))
                $sdp_media .= "\na=ice-ufrag:".$content->transport->attributes()->ufrag;
                
            if(isset($content->transport->attributes()->pwd))
                $sdp_media .= "\na=ice-pwd:".$content->transport->attributes()->pwd;
            
            foreach($content->description->children() as $payload) {
                switch($payload->getName()) {
                    case 'rtp-hdrext':  
                        $sdp_media .= 
                            "\na=extmap:".
                            $payload->attributes()->id;
                            
                        if(isset($payload->attributes()->senders))
                            $sdp_media .= ' '.$payload->attributes()->senders;

                        $sdp_media .= ' '.$payload->attributes()->uri;
                        break;
                        
                    case 'rtcp-mux':
                        $sdp_media .= 
                            "\na=rtcp-mux"; 
                    
                    case 'encryption':
                        if(isset($payload->crypto)) {
                            $sdp_media .= 
                                "\na=crypto:".
                                $payload->crypto->attributes()->tag.' '.                          
                                $payload->crypto->attributes()->{'crypto-suite'}.' '.                          
                                $payload->crypto->attributes()->{'key-params'};

                            // TODO session params ?
                        }
                        break;

                    case 'payload-type':
                        $sdp_media .= 
                            "\na=rtpmap:".
                            $payload->attributes()->id;

                        array_push($media_header_ids, $payload->attributes()->id);

                        if(isset($payload->attributes()->name)) {
                            $sdp_media .= ' '.$payload->attributes()->name;

                            if(isset($payload->attributes()->clockrate)) {
                                $sdp_media .= '/'.$payload->attributes()->clockrate;

                                if(isset($payload->attributes()->channels)) {
                                    $sdp_media .= '/'.$payload->attributes()->channels;
                                }
                            }
                        }

                        $first_fmtp = true;

                        foreach($payload->children() as $param) {
                            switch($param->getName()) {
                                case 'rtcp-fb' :
                                    $sdp_media .= 
                                        "\na=rtcp-fb:".
                                        $param->attributes()->id.' '.
                                        $param->attributes()->type;

                                    if(isset($param->attributes()->subtype)) {
                                        $sdp_media .= ' '.$param->attributes()->subtype;
                                    }

                                    break;

                                // http://xmpp.org/extensions/xep-0167.html#format
                                case 'parameter' :
                                    if($first_fmtp) {
                                        $sdp_media .=
                                            "\na=fmtp:".
                                            $payload->attributes()->id.
                                            ' ';
                                    } else {
                                        $sdp_media .= '; ';
                                    }

                                    if(isset($param->attributes()->name)) {
                                        $sdp_media .=
                                            $param->attributes()->name.
                                            '=';
                                    }

                                    $sdp_media .=
                                        $param->attributes()->value;

                                    $first_fmtp = false;
                                    
                                    break;
                            }

                            // TODO rtcp_fb_trr_int ?
                        }
                        
                        break;

                    case 'source':
                        foreach($payload->children() as $s) {
                            $sdp_media .= 
                                "\na=ssrc:".$payload->attributes()->id.' '.
                                $s->attributes()->name.':'.
                                $s->attributes()->value;
                        }
                        break;
                }
                // TODO sendrecv ?
            }

            if(isset($content->description->attributes()->ptime)) {
                $sdp_media .= 
                    "\na=ptime:".$content->description->attributes()->ptime;
            }
            
            if(isset($content->description->attributes()->maxptime)) {
                $sdp_media .= 
                    "\na=maxptime:".$content->description->attributes()->maxptime;
            }

            foreach($content->transport->children() as $payload) {
                switch($payload->getName()) {
                    case 'fingerprint':
                        if(isset($content->transport->fingerprint->attributes()->hash)) {
                            $sdp_media .= 
                                "\na=fingerprint:".
                                $content->transport->fingerprint->attributes()->hash.
                                ' '.
                                $content->transport->fingerprint;    
                        }
                        
                        if(isset($content->transport->fingerprint->attributes()->setup)) {
                            $sdp_media .= 
                                "\na=setup:".
                                $content->transport->fingerprint->attributes()->setup;                    
                        }
                        break;

                    // http://xmpp.org/extensions/inbox/jingle-dtls.html
                    case 'sctpmap':
                        $sdp_media .=
                            "\na=sctpmap:".
                            $payload->attributes()->number.' '.
                            $payload->attributes()->protocol.' '.
                            $payload->attributes()->streams.' '
                            ;
                        break;

                    case 'candidate':
                        $sdp_media .= 
                            "\na=candidate:".
                            $payload->attributes()->foundation.' '.
                            $payload->attributes()->component.' '.
                            $payload->attributes()->protocol.' '.
                            $payload->attributes()->priority.' '.
                            $payload->attributes()->ip.' '.
                            $payload->attributes()->port.' '.
                            'typ '.$payload->attributes()->type;

                        if(isset($payload->attributes()->{'rel-addr'})
                        && isset($payload->attributes()->{'rel-port'})) {
                            $sdp_media .=
                                ' raddr '.$payload->attributes()->{'rel-addr'}.
                                ' rport '.$payload->attributes()->{'rel-port'};
                        }
                        if(isset($payload->attributes()->generation)) {
                            $sdp_media .=
                                ' generation '.$payload->attributes()->generation.
                                ' network '.$payload->attributes()->network.
                                ' id '.$payload->attributes()->id;
                        }
                        break;
                }
            }

            $sdp_media_header = $sdp_media_header.' '.implode(' ', $media_header_ids);

            $sdp_medias .=
                $sdp_media_header.
                $sdp_media;
        }
        
        $this->sdp .= $sdp_version;
        $this->sdp .= "\n".$sdp_origin;
        $this->sdp .= "\n".$sdp_session_name;
        $this->sdp .= "\n".$sdp_timing;
        $this->sdp .= $sdp_medias;
        
        return $this->sdp;
    }
}
