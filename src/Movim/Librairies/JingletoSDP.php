<?php

namespace Movim\Librairies;

use SimpleXMLElement;

class JingletoSDP
{
    private string $sdp = '';
    private SimpleXMLElement $jingle;
    public ?string $sid = null;

    private string $action;

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

    private $sendersToDirection  = [
        'none'      => 'inactive',
        'initiator' => 'sendonly',
        'responder' => 'recvonly',
        'both'      => 'sendrecv',
    ];

    public function __construct(SimpleXMLElement $jingle)
    {
        $this->jingle = $jingle;

        if (isset($this->jingle->attributes()->sid)) {
            $this->sid = (string)$this->jingle->attributes()->sid;
        }

        $this->action = (string)$this->jingle->attributes()->action;
    }

    public function generate()
    {
        if ($this->jingle->attributes()->initiator) {
            $username = explode('@', (string)$this->jingle->attributes()->initiator);
            $username = $username[0];
        } else {
            $username = '-';
        }

        $this->values['session_sdp_id'] = substr(base_convert(hash('sha256', $this->sid), 30, 10), 0, 6);

        $sdpVersion =
            'v=0';

        $sdpOrigin =
            'o=' .
            $username . ' ' .
            $this->values['session_sdp_id'] . ' ' .
            $this->values['session_version'] . ' ' .
            $this->values['nettype'] . ' ' .
            $this->values['addrtype'] . ' ' .
            $this->values['unicast_address'];

        $sdpSessionName =
            's=-'; // Use the sessid ?

        $sdpTiming =
            't=0 0';

        $sdpGroup = null;

        $sdpMedias = '';

        // http://xmpp.org/extensions/xep-0338.html
        if ($this->jingle->group) {
            $sdpGroup .=
                "a=group:" .
                (string)$this->jingle->group->attributes()->semantics;

            foreach ($this->jingle->group->content as $content) {
                $sdpGroup .= " " . (string)$content->attributes()->name;
            }
        }

        foreach ($this->jingle->content as $content) {
            $mediaHeaderIds = [];
            $mediaHeaderFirstPort = null;
            $mediaHeaderLastIp = null;

            $sdpMedia = '';

            $this->name = (string)$content->attributes()->name;

            if (isset($content->transport->attributes()->pwd)) {
                $sdpMedia .= "\r\na=ice-pwd:" . $content->transport->attributes()->pwd;
            }

            // ufrag can be alone without a password for candidates
            if (
                isset($content->transport->attributes()->ufrag)
                && isset($content->transport->attributes()->pwd)
            ) {
                $sdpMedia .= "\r\na=ice-ufrag:" . $content->transport->attributes()->ufrag;
            }

            if (isset($content->description)) {
                foreach ($content->description->children() as $payload) {
                    switch ($payload->getName()) {
                        case 'rtp-hdrext':
                            $sdpMedia .=
                                "\r\na=extmap:" .
                                $payload->attributes()->id;

                            if (isset($payload->attributes()->senders)) {
                                $sdpMedia .= '/' . $this->sendersToDirection[(string)$payload->attributes()->senders];
                            }

                            $sdpMedia .= ' ' . $payload->attributes()->uri;
                            break;

                        // https://xmpp.org/extensions/xep-0293.html
                        case 'rtcp-fb':
                            $sdpMedia .=
                                "\r\na=rtcp-fb:" .
                                '* ' .
                                $payload->attributes()->type;

                            if (isset($payload->attributes()->subtype)) {
                                $sdpMedia .= ' ' . $payload->attributes()->subtype;
                            }
                            break;

                        case 'rtcp-mux':
                            $sdpMedia .=
                                "\r\na=rtcp-mux";

                            // no break
                        case 'encryption':
                            if (isset($payload->crypto)) {
                                $sdpMedia .=
                                    "\r\na=crypto:" .
                                    $payload->crypto->attributes()->tag . ' ' .
                                    $payload->crypto->attributes()->{'crypto-suite'} . ' ' .
                                    $payload->crypto->attributes()->{'key-params'};

                                // TODO session params ?
                            }

                            if (isset($payload->{'zrtp-hash'})) {
                                $sdpMedia .=
                                    "\r\na=zrtp-hash:" .
                                    $payload->{'zrtp-hash'}->attributes()->version . ' ' .
                                    (string)$payload->{'zrtp-hash'};
                            }
                            break;

                        case 'payload-type':
                            $payloadId = $payload->attributes()->id;
                            $sdpMedia .=
                                "\r\na=rtpmap:" .
                                $payloadId;

                            array_push($mediaHeaderIds, $payloadId);

                            if (isset($payload->attributes()->name)) {
                                $sdpMedia .= ' ' . $payload->attributes()->name;

                                if (isset($payload->attributes()->clockrate)) {
                                    $sdpMedia .= '/' . $payload->attributes()->clockrate;

                                    if (isset($payload->attributes()->channels)) {
                                        $sdpMedia .= '/' . $payload->attributes()->channels;
                                    }
                                }
                            }

                            $first_fmtp = true;

                            foreach ($payload->children() as $param) {
                                switch ($param->getName()) {
                                    case 'rtcp-fb':
                                        $sdpMedia .=
                                            "\r\na=rtcp-fb:" .
                                            $payloadId . ' ' .
                                            $param->attributes()->type;

                                        if (isset($param->attributes()->subtype)) {
                                            $sdpMedia .= ' ' . $param->attributes()->subtype;
                                        }

                                        break;
                                    // http://xmpp.org/extensions/xep-0167.html#format
                                    case 'parameter':
                                        if ($first_fmtp) {
                                            $sdpMedia .=
                                                "\r\na=fmtp:" .
                                                $payloadId .
                                                ' ';
                                        } else {
                                            $sdpMedia .= ';';
                                        }

                                        if (isset($param->attributes()->name)) {
                                            $sdpMedia .=
                                                $param->attributes()->name .
                                                '=';
                                        }

                                        $sdpMedia .=
                                            $param->attributes()->value;

                                        $first_fmtp = false;

                                        break;
                                }

                                // TODO rtcp_fb_trr_int ?
                            }

                            break;

                        case 'source':
                            foreach ($payload->children() as $s) {
                                $sdpMedia .=
                                    "\r\na=ssrc:" . $payload->attributes()->ssrc . ' ' .
                                    $s->attributes()->name . ':' .
                                    $s->attributes()->value;
                            }
                            break;
                    }
                    // TODO sendrecv ?
                }
            }

            if (
                isset($content->description)
                && isset($content->description->attributes()->ptime)
            ) {
                $sdpMedia .=
                    "\r\na=ptime:" . $content->description->attributes()->ptime;
            }

            if (
                isset($content->description)
                && isset($content->description->attributes()->maxptime)
            ) {
                $sdpMedia .=
                    "\r\na=maxptime:" . $content->description->attributes()->maxptime;
            }

            foreach ($content->transport->children() as $payload) {
                switch ($payload->getName()) {
                    case 'fingerprint':
                        if (isset($content->transport->fingerprint->attributes()->hash)) {
                            $sdpMedia .=
                                "\r\na=fingerprint:" .
                                $content->transport->fingerprint->attributes()->hash .
                                ' ' .
                                strtoupper($content->transport->fingerprint);
                        }

                        if (isset($content->transport->fingerprint->attributes()->setup)) {
                            $sdpMedia .=
                                "\r\na=setup:" .
                                $content->transport->fingerprint->attributes()->setup;
                        }
                        break;

                    // https://xmpp.org/extensions/xep-0343.html
                    case 'sctpmap':
                        $sdpMedia .=
                            "\r\na=sctpmap:" .
                            $payload->attributes()->number . ' ' .
                            $payload->attributes()->protocol . ' ' .
                            $payload->attributes()->streams . ' ';


                        array_push($mediaHeaderIds, $payload->attributes()->number);

                        break;

                    case 'candidate':
                        $sdpMedia .=
                            "\r\na=candidate:" .
                            $payload->attributes()->foundation . ' ' .
                            $payload->attributes()->component . ' ' .
                            $payload->attributes()->protocol . ' ' .
                            $payload->attributes()->priority . ' ' .
                            $payload->attributes()->ip . ' ' .
                            $payload->attributes()->port . ' ' .
                            'typ ' . $payload->attributes()->type;

                        if (
                            isset($payload->attributes()->{'rel-addr'})
                            && isset($payload->attributes()->{'rel-port'})
                        ) {
                            $sdpMedia .=
                                ' raddr ' . $payload->attributes()->{'rel-addr'} .
                                ' rport ' . $payload->attributes()->{'rel-port'};

                            if ($mediaHeaderFirstPort == null) {
                                $mediaHeaderFirstPort = $payload->attributes()->port;
                            }
                        }

                        if (isset($payload->attributes()->tcptype)) {
                            $sdpMedia .=
                                ' tcptype ' . $payload->attributes()->tcptype;
                        }

                        if (isset($payload->attributes()->generation)) {
                            $sdpMedia .=
                                ' generation ' . $payload->attributes()->generation;
                        }

                        // ufrag in candidate transport
                        if (isset($content->transport->attributes()->ufrag)) {
                            $sdpMedia .=
                                ' ufrag ' . $content->transport->attributes()->ufrag;
                        }

                        if (isset($payload->attributes()->network)) {
                            $sdpMedia .=
                                ' network ' . $payload->attributes()->network;
                        }

                        if (isset($payload->attributes()->id)) {
                            $sdpMedia .=
                                ' id ' . $payload->attributes()->id;
                        }

                        if (isset($payload->attributes()->{'network-id'})) {
                            $sdpMedia .=
                                ' network-id ' . $payload->attributes()->{'network-id'};
                        }

                        if (isset($payload->attributes()->{'network-cost'})) {
                            $sdpMedia .=
                                ' network-id ' . $payload->attributes()->{'network-cost'};
                        }

                        // mid, mlineindex
                        if (isset($payload->attributes()->mid)) {
                            $this->mid = (int)$payload->attributes()->mid;
                        }

                        if (isset($payload->attributes()->mlineindex)) {
                            $this->mlineindex = (int)$payload->attributes()->mlineindex;
                        }

                        $mediaHeaderLastIp = $payload->attributes()->ip;

                        break;
                }
            }

            if ($mediaHeaderFirstPort == null) {
                $mediaHeaderFirstPort = 1;
            }

            if ($mediaHeaderLastIp == null) {
                $mediaHeaderLastIp = '0.0.0.0';
            }

            if (isset($content->description)) {
                $this->media = (string)$content->description->attributes()->media;
                $this->mlineindex = ($this->media == 'audio') ? 0 : 1;
            }

            if ($this->action != 'transport-info') {
                $sdpmediaHeader =
                    "\r\nm=" . $this->media .
                    ' ' . $mediaHeaderFirstPort . ' ';

                if (isset($content->transport->sctpmap)) {
                    $sdpmediaHeader .= 'DTLS/SCTP';
                } elseif (
                    isset($content->description->crypto)
                    || isset($content->transport->fingerprint)
                ) {
                    $sdpmediaHeader .= 'UDP/TLS/RTP/SAVPF';
                } else {
                    $sdpmediaHeader .= 'UDP/TLS/RTP/AVP';
                }

                $sdpmediaHeader = $sdpmediaHeader . ' ' . implode(' ', $mediaHeaderIds);

                $ipVersion = filter_var($mediaHeaderLastIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
                    ? 'IP6'
                    : 'IP4';

                $sdpMedias .=
                    $sdpmediaHeader .
                    "\r\nc=IN " . $ipVersion . " " . $mediaHeaderLastIp .
                    $sdpMedia;

                if ($this->name !== null) {
                    $sdpMedias .= "\r\na=mid:" . $this->name;
                }

                if ($content->attributes()->senders) {
                    $sdpMedias .= "\r\na=" . $this->sendersToDirection[(string)$content->attributes()->senders];
                }
            } else {
                $sdpMedias = $sdpMedia;
            }
        }

        if ($this->action != 'transport-info') {
            $this->sdp .= /*"\r\n".*/ $sdpVersion;
            $this->sdp .= "\r\n" . $sdpOrigin;
            $this->sdp .= "\r\n" . $sdpSessionName;
            $this->sdp .= "\r\n" . $sdpTiming;

            if ($sdpGroup) $this->sdp .= "\r\n" . $sdpGroup;
        }

        $this->sdp .= $sdpMedias;

        return trim($this->sdp . "\r\n");
    }
}
