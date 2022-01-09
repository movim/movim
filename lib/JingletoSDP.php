<?php

use Movim\Session;

class JingletoSDP
{
    private $sdp = '';
    private $jingle;

    private $action;

    // Only used for ICE Candidate (Jingle transport-info)
    public $media;
    public $name = null;

    // Non standard
    public $mid;
    public $mlineindex;

    private $values = [
        'session_sdp_id'    => 1,
        'session_version'   => 0,
        'nettype'           => 'IN',
        'addrtype'          => 'IP4',
        'unicast_address'   => '0.0.0.0'
    ];

    public function __construct($jingle)
    {
        $this->jingle = $jingle;

        if (isset($this->jingle->attributes()->sid)) {
            $sid = (string)$this->jingle->attributes()->sid;
            $s = Session::start();
            $s->set('jingleSid', $sid);
        }

        $this->action = (string)$this->jingle->attributes()->action;
    }

    public function getSessionId()
    {
        $s = Session::start();
        return substr(base_convert($s->get('jingleSid') ?? '', 30, 10), 0, 6);
    }

    public function generate()
    {
        if ($this->jingle->attributes()->initiator) {
            $username = explode('@', (string)$this->jingle->attributes()->initiator);
            $username = $username[0];
        } else {
            $username = '-';
        }

        $this->values['session_sdp_id'] = $this->getSessionId();

        $sdp_version =
            'v=0';

        $sdp_origin =
            'o='.
            $username.' '.
            $this->values['session_sdp_id'].' '.
            $this->values['session_version'].' '.
            $this->values['nettype'].' '.
            $this->values['addrtype'].' '.
            $this->values['unicast_address'];

        $sdp_session_name =
            's=-'; // Use the sessid ?

        $sdp_timing =
            't=0 0';

        $sdp_medias = '';

        foreach ($this->jingle->children() as $content) {
            $media_header_ids = [];
            $media_header_first_port = null;
            $media_header_last_ip = null;

            $sdp_media = '';

            // http://xmpp.org/extensions/xep-0338.html
            if ((string)$content->getName() == 'group') {
                $sdp_medias .=
                    "\r\na=group:".
                    (string)$content->attributes()->semantics;
                foreach ($content->children() as $content) {
                    $sdp_medias .= " ".(string)$content->attributes()->name;
                }

                continue;
            }

            if ($content->getName() != 'content') {
                break;
            }

            $this->name = (string)$content->attributes()->name;

            if (isset($content->transport->attributes()->pwd)) {
                $sdp_media .= "\r\na=ice-pwd:".$content->transport->attributes()->pwd;
            }

            // ufrag can be alone without a password for candidates
            if (isset($content->transport->attributes()->ufrag)
            && isset($content->transport->attributes()->pwd)) {
                $sdp_media .= "\r\na=ice-ufrag:".$content->transport->attributes()->ufrag;
            }

            if (isset($content->description)) {
                foreach ($content->description->children() as $payload) {
                    switch ($payload->getName()) {
                        case 'rtp-hdrext':
                            $sdp_media .=
                                "\r\na=extmap:".
                                $payload->attributes()->id;

                            if (isset($payload->attributes()->senders)) {
                                $sdp_media .= ' '.$payload->attributes()->senders;
                            }

                            $sdp_media .= ' '.$payload->attributes()->uri;
                            break;

                        // https://xmpp.org/extensions/xep-0293.html
                        case 'rtcp-fb':
                            $sdp_media .=
                                "\r\na=rtcp-fb:".
                                '* '.
                                $payload->attributes()->type;

                            if (isset($payload->attributes()->subtype)) {
                                $sdp_media .= ' '.$payload->attributes()->subtype;
                            }
                            break;

                        case 'rtcp-mux':
                            $sdp_media .=
                                "\r\na=rtcp-mux";

                            // no break
                        case 'encryption':
                            if (isset($payload->crypto)) {
                                $sdp_media .=
                                    "\r\na=crypto:".
                                    $payload->crypto->attributes()->tag.' '.
                                    $payload->crypto->attributes()->{'crypto-suite'}.' '.
                                    $payload->crypto->attributes()->{'key-params'};

                                // TODO session params ?
                            }

                            if (isset($payload->{'zrtp-hash'})) {
                                $sdp_media .=
                                    "\r\na=zrtp-hash:".
                                    $payload->{'zrtp-hash'}->attributes()->version.' '.
                                    (string)$payload->{'zrtp-hash'};
                            }
                            break;

                        case 'payload-type':
                            $payload_id = $payload->attributes()->id;
                            $sdp_media .=
                                "\r\na=rtpmap:".
                                $payload_id;

                            array_push($media_header_ids, $payload_id);

                            if (isset($payload->attributes()->name)) {
                                $sdp_media .= ' '.$payload->attributes()->name;

                                if (isset($payload->attributes()->clockrate)) {
                                    $sdp_media .= '/'.$payload->attributes()->clockrate;

                                    if (isset($payload->attributes()->channels)) {
                                        $sdp_media .= '/'.$payload->attributes()->channels;
                                    }
                                }
                            }

                            $first_fmtp = true;

                            foreach ($payload->children() as $param) {
                                switch ($param->getName()) {
                                    case 'rtcp-fb':
                                        $sdp_media .=
                                            "\r\na=rtcp-fb:".
                                            $payload_id.' '.
                                            $param->attributes()->type;

                                        if (isset($param->attributes()->subtype)) {
                                            $sdp_media .= ' '.$param->attributes()->subtype;
                                        }

                                        break;
                                    // http://xmpp.org/extensions/xep-0167.html#format
                                    case 'parameter':
                                        if ($first_fmtp) {
                                            $sdp_media .=
                                                "\r\na=fmtp:".
                                                $payload_id.
                                                ' ';
                                        } else {
                                            $sdp_media .= ';';
                                        }

                                        if (isset($param->attributes()->name)) {
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
                            foreach ($payload->children() as $s) {
                                $sdp_media .=
                                    "\r\na=ssrc:".$payload->attributes()->ssrc.' '.
                                    $s->attributes()->name.':'.
                                    $s->attributes()->value;
                            }
                            break;
                    }
                    // TODO sendrecv ?
                }
            }

            if (isset($content->description)
            && isset($content->description->attributes()->ptime)) {
                $sdp_media .=
                    "\r\na=ptime:".$content->description->attributes()->ptime;
            }

            if (isset($content->description)
            && isset($content->description->attributes()->maxptime)) {
                $sdp_media .=
                    "\r\na=maxptime:".$content->description->attributes()->maxptime;
            }

            foreach ($content->transport->children() as $payload) {
                switch ($payload->getName()) {
                    case 'fingerprint':
                        if (isset($content->transport->fingerprint->attributes()->hash)) {
                            $sdp_media .=
                                "\r\na=fingerprint:".
                                $content->transport->fingerprint->attributes()->hash.
                                ' '.
                                strtoupper($content->transport->fingerprint);
                        }

                        if (isset($content->transport->fingerprint->attributes()->setup)) {
                            $sdp_media .=
                                "\r\na=setup:".
                                $content->transport->fingerprint->attributes()->setup;
                        }
                        break;

                    // https://xmpp.org/extensions/xep-0343.html
                    case 'sctpmap':
                        $sdp_media .=
                            "\r\na=sctpmap:".
                            $payload->attributes()->number.' '.
                            $payload->attributes()->protocol.' '.
                            $payload->attributes()->streams.' '
                            ;


                        array_push($media_header_ids, $payload->attributes()->number);

                        break;

                    case 'candidate':
                        $sdp_media .=
                            "\r\na=candidate:".
                            $payload->attributes()->foundation.' '.
                            $payload->attributes()->component.' '.
                            $payload->attributes()->protocol.' '.
                            $payload->attributes()->priority.' '.
                            $payload->attributes()->ip.' '.
                            $payload->attributes()->port.' '.
                            'typ '.$payload->attributes()->type;

                        if (isset($payload->attributes()->{'rel-addr'})
                        && isset($payload->attributes()->{'rel-port'})) {
                            $sdp_media .=
                                ' raddr '.$payload->attributes()->{'rel-addr'}.
                                ' rport '.$payload->attributes()->{'rel-port'};

                            if ($media_header_first_port == null) {
                                $media_header_first_port = $payload->attributes()->port;
                            }
                        }

                        if (isset($payload->attributes()->tcptype)) {
                            $sdp_media .=
                                ' tcptype '.$payload->attributes()->tcptype;
                        }

                        if (isset($payload->attributes()->generation)) {
                            $sdp_media .=
                                ' generation '.$payload->attributes()->generation;
                        }

                        // ufrag in candidate transport
                        if (isset($content->transport->attributes()->ufrag)) {
                            $sdp_media .=
                                ' ufrag '.$content->transport->attributes()->ufrag;
                        }

                        if (isset($payload->attributes()->network)) {
                            $sdp_media .=
                                ' network '.$payload->attributes()->network;
                        }

                        if (isset($payload->attributes()->id)) {
                            $sdp_media .=
                                ' id '.$payload->attributes()->id;
                        }

                        if (isset($payload->attributes()->{'network-id'})) {
                            $sdp_media .=
                                ' network-id '.$payload->attributes()->{'network-id'};
                        }

                        if (isset($payload->attributes()->{'network-cost'})) {
                            $sdp_media .=
                                ' network-id '.$payload->attributes()->{'network-cost'};
                        }

                        // mid, mlineindex
                        if (isset($payload->attributes()->mid)) {
                            $this->mid = (int)$payload->attributes()->mid;
                        }

                        if (isset($payload->attributes()->mlineindex)) {
                            $this->mlineindex = (int)$payload->attributes()->mlineindex;
                        }

                        $media_header_last_ip = $payload->attributes()->ip;

                        break;
                }
            }

            if ($media_header_first_port == null) {
                $media_header_first_port = 1;
            }

            if ($media_header_last_ip == null) {
                $media_header_last_ip = '0.0.0.0';
            }

            if (isset($content->description)) {
                $this->media = (string)$content->description->attributes()->media;
                $this->mlineindex = ($this->media == 'audio') ? 0 : 1;
            }

            if ($this->action != 'transport-info') {
                $sdp_media_header =
                    "\r\nm=".$this->media.
                    ' '.$media_header_first_port.' ';

                if (isset($content->transport->sctpmap)) {
                    $sdp_media_header .= 'DTLS/SCTP';
                } elseif (isset($content->description->crypto)
                || isset($content->transport->fingerprint)) {
                    $sdp_media_header .= 'UDP/TLS/RTP/SAVPF';
                } else {
                    $sdp_media_header .= 'UDP/TLS/RTP/AVP';
                }

                $sdp_media_header = $sdp_media_header.' '.implode(' ', $media_header_ids);

                $ipVersion = filter_var($media_header_last_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
                    ? 'IP6'
                    : 'IP4';

                $sdp_medias .=
                    $sdp_media_header.
                    "\r\nc=IN ".$ipVersion." ".$media_header_last_ip.
                    $sdp_media;
                //"\r\na=sendrecv";

                if ($this->name !== null) {
                    $sdp_medias .= "\r\na=mid:".$this->name;
                }
            } else {
                $sdp_medias = $sdp_media;
            }
        }

        if ($this->action != 'transport-info') {
            $this->sdp .= /*"\r\n".*/$sdp_version;
            $this->sdp .= "\r\n".$sdp_origin;
            $this->sdp .= "\r\n".$sdp_session_name;
            $this->sdp .= "\r\n".$sdp_timing;
        }

        $this->sdp .= $sdp_medias;

        return trim($this->sdp."\r\n");
    }
}
