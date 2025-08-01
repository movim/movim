<?php

namespace Movim\Librairies;

use DOMElement;
use Movim\Session;

class SDPtoJingle
{
    private $sdp;
    private $arr;
    private $jingle;

    private $content    = null;
    private $transport  = null;

    private ?string $action = null;

    private $ufrag = null;
    private $mid = null;
    private $msid = null;
    private $sid = null;
    private $mujiRoom = null;

    // Move the global fingerprint into each medias
    private $globalFingerprint = [];
    private $fmtpCache = [];
    private $rtcpFbCache = [];

    private $directionToSenders = [
        'inactive' => 'none',
        'sendonly' => 'initiator',
        'recvonly' => 'responder',
        'sendrecv' => 'both',
    ];

    private $regex = [
        'bandwidth'       => "/^b=(\w+):(\d+)/i",
        'candidate'       => "/^a=candidate:(\w{1,32}) (\d{1,5}) (udp|tcp) (\d{1,10}) ([a-zA-Z0-9:\.]{1,45}) (\d{1,5}) (typ) (host|srflx|prflx|relay|ufrag)\s?(.+)?/i",
        'crypto'          => "/^a=crypto:(\d{1,9}) (\w+) (\S+)( (\S+))?/i",
        'extmap'          => "/^a=extmap:([^\s\/]+)(\/([^\s\/]+))? (\S+)/i",
        'fingerprint'     => "/^a=fingerprint:(\S+) (\S+)/i",
        'fmtp'            => "/^a=fmtp:(\d+) (.+)/i",
        'group'           => "/^a=group:(\S+) (.+)/i",
        'maxptime'        => "/^a=maxptime:(\d+)/i",
        'media'           => "/^m=(audio|video|application|data)/i",
        'mid'             => "/^a=mid:(\S+)/i",
        'msid'            => "/^a=msid:(.+)/i",
        'ptime'           => "/^a=ptime:(\d+)/i",
        'pwd'             => "/^a=ice-pwd:(\S+)/i",
        'rtcp_fb_trr_int' => "/^a=rtcp-fb:(\d+) trr-int (\d+)/i",
        'rtcp_fb'         => "/^a=rtcp-fb:(\S+) (\S+)( (\S+))?/i",
        'rtcp_mux'        => "/^a=rtcp-mux/i",
        'rtpmap'          => "/^a=rtpmap:(\d+) (([^\s\/]+)(\/(\d+)(\/([^\s\/]+))?)?)?/i",
        'sctpmap'         => "/^a=sctpmap:(\d+) (\S+) (\d+)/i",
        'senders'         => "/^a=(sendrecv|sendonly|inactive|recvonly)/i",
        'sess_id'         => "/^o=(\S+) (\d+)/i",
        'setup'           => "/^a=setup:(\S+)/i",
        'ssrc'            => "/^a=ssrc:(\d+) (\w+)(:(\S+))?( (\w+))?/i",
        'ufrag'           => "/^a=ice-ufrag:(\S+)/i",
        'zrtp_hash'       => "/^a=zrtp-hash:(\S+) (\w+)/i",
    ];

    public function __construct(
        string $sdp,
        string $sid,
        bool $muji = false,
        ?string $responder = null,
        ?string $action = null,
        ?string $mid = null,
        ?string $ufrag = null,
    ) {
        $this->sdp = $sdp;
        $this->arr = explode("\n", $this->sdp);
        $this->sid = $sid;

        if ($mid !== null) {
            $this->mid = $mid;
        }

        if ($ufrag !== null) {
            $this->ufrag = $ufrag;
        }

        if ($muji) {
            $this->jingle = new \SimpleXMLElement('<muji></muji>');
            $this->jingle->addAttribute('xmlns', 'urn:xmpp:jingle:muji:0');
        } else {
            $this->jingle = new \SimpleXMLElement('<jingle></jingle>');
            $this->jingle->addAttribute('xmlns', 'urn:xmpp:jingle:1');
            $this->jingle->addAttribute('initiator', me()->id);
        }

        if ($action) {
            $this->action = $action;
            $this->jingle->addAttribute('action', $action);
        }

        if ($responder) {
            $this->jingle->addAttribute('responder', $responder);
        }
    }

    public function setMujiRoom(string $mujiRoom)
    {
        $this->mujiRoom = $mujiRoom;
    }

    private function initContent($force = false)
    {
        if (
            $this->content == null
            || $force
        ) {
            $this->content = $this->jingle->addChild('content');
            $this->content->addAttribute('creator', 'initiator');

            $this->transport    = $this->content->addChild('transport');
            $this->transport->addAttribute('xmlns', "urn:xmpp:jingle:transports:ice-udp:1");
            $this->msid = null;

            // A hack to ensure that Dino is returning complete Muji content proposal
            if ($this->jingle->getName() == 'muji') {
                $this->content->addAttribute('xmlns', 'urn:xmpp:jingle:1');
            }
        }
    }

    private function addFmtpParameters($payloadtype, $params)
    {
        foreach ($params as $value) {
            $p = explode('=', trim($value));

            if (count($p) == 1) {
                /**
                 * http://xmpp.org/extensions/xep-0167.html#format
                 * doesn't specifiy a case where we only have a value
                 */
                //$parameter->addAttribute('value', $p[0]);
            } else {
                $parameter = $payloadtype->addChild('parameter');
                $parameter->addAttribute('name', $p[0]);
                $parameter->addAttribute('value', $p[1]);
            }
        }
    }

    private function addRtcpFbParameters($payloadtype, $params)
    {
        foreach ($params as $matches) {
            $rtcpfp = $payloadtype->addChild('rtcp-fb');
            $rtcpfp->addAttribute('xmlns', "urn:xmpp:jingle:apps:rtp:rtcp-fb:0");
            $rtcpfp->addAttribute('type', $matches[2]);

            if (isset($matches[4])) {
                $rtcpfp->addAttribute('subtype', $matches[4]);
            }
        }
    }

    public function addName($name = null)
    {
        if ($name !== null) {
            $this->content->addAttribute('name', $name);
        } elseif ($this->mid !== null) {
            $this->content->addAttribute('name', $this->mid);
        }
    }

    public function generate(): DOMElement
    {
        if ($this->mujiRoom) {
            $muji = $this->jingle->addChild('muji');
            $muji->addAttribute('xmlns', 'urn:xmpp:jingle:muji:0');
            $muji->addAttribute('room', $this->mujiRoom);
        }

        foreach ($this->arr as $l) {
            foreach ($this->regex as $key => $r) {
                if (preg_match($r, $l, $matches)) {
                    switch ($key) {
                        case 'sess_id':
                            $this->jingle->addAttribute('sid', $this->sid);
                            break;
                        case 'media':
                            $this->initContent(true);

                            // The description node
                            if ($this->action != 'transport-info') {
                                $description = $this->content->addChild('description');
                                $description->addAttribute('xmlns', "urn:xmpp:jingle:apps:rtp:1");
                                $description->addAttribute('media', $matches[1]);
                            }

                            if (!empty($this->globalFingerprint)) {
                                $fingerprint = $this->transport->addChild('fingerprint', $this->globalFingerprint['fingerprint']);
                                $fingerprint->addAttribute('xmlns', "urn:xmpp:jingle:apps:dtls:0");
                                $fingerprint->addAttribute('hash', $this->globalFingerprint['hash']);
                            }

                            break;

                        case 'mid':
                            $this->addName($matches[1]);
                            break;

                        case 'msid':
                            $this->msid = trim($matches[1]);
                            break;

                        case 'bandwidth':
                            $bandwidth = $description->addChild('bandwidth');
                            $bandwidth->addAttribute('type', $matches[1]);
                            $bandwidth->addAttribute('value', $matches[2]);
                            break;

                        case 'rtpmap':
                            $payloadtype = $description->addChild('payload-type');
                            $payloadtype->addAttribute('id', $matches[1]);
                            $payloadtype->addAttribute('name', $matches[3]);
                            if (isset($matches[4])) {
                                $payloadtype->addAttribute('clockrate', $matches[5]);
                            }

                            if (isset($matches[7])) {
                                $payloadtype->addAttribute('channels', $matches[7]);
                            }

                            if (isset($this->fmtpCache[$matches[1]])) {
                                $this->addFmtpParameters($payloadtype, $this->fmtpCache[$matches[1]]);
                                unset($this->fmtpCache[$matches[1]]);
                            }

                            if (isset($this->rtcpFbCache[$matches[1]])) {
                                $this->addRtcpFbParameters($payloadtype, $this->rtcpFbCache[$matches[1]]);
                                unset($this->rtcpFbCache[$matches[1]]);
                            }

                            break;


                        // http://xmpp.org/extensions/xep-0167.html#format
                        case 'fmtp':
                            $params = explode(';', trim($matches[2]));
                            $this->fmtpCache[$matches[1]] = $params;
                            break;

                        // http://xmpp.org/extensions/xep-0293.html
                        case 'rtcp_fb':
                            if ($matches[1] == '*') {
                                $this->addRtcpFbParameters($description, [$matches]);
                            } else {
                                if (
                                    isset($payloadtype)
                                    && $matches[1] == $payloadtype->attributes()->id
                                ) {
                                    $this->addRtcpFbParameters($payloadtype, [$matches]);
                                } else {
                                    if (!isset($this->rtcpFbCache[$matches[1]])) {
                                        $this->rtcpFbCache[$matches[1]] = [];
                                    }

                                    array_push($this->rtcpFbCache[$matches[1]], $matches);
                                }
                            }

                            break;

                        case 'rtcp_fb_trr_int':
                            $rtcpfp = $payloadtype->addChild('rtcp-fb-trr-int');
                            $rtcpfp->addAttribute('xmlns', "urn:xmpp:jingle:apps:rtp:rtcp-fb:0");
                            $rtcpfp->addAttribute('id', $matches[1]);
                            $rtcpfp->addAttribute('value', $matches[2]);
                            break;

                        // http://xmpp.org/extensions/xep-0167.html#srtp
                        case 'crypto':
                            $encryption = $description->addChild('encryption');
                            $crypto     = $encryption->addChild('crypto');
                            $crypto->addAttribute('crypto-suite', $matches[2]);
                            $crypto->addAttribute('key-params', $matches[3]);
                            $crypto->addAttribute('tag', $matches[1]);
                            if (isset($matches[5])) {
                                $crypto->addAttribute('session-params', $matches[5]);
                            }
                            break;

                        // http://xmpp.org/extensions/xep-0262.html
                        case 'zrtp_hash':
                            $zrtphash   = $encryption->addChild('zrtp-hash', $matches[2]);
                            $zrtphash->addAttribute('xmlns', "urn:xmpp:jingle:apps:rtp:zrtp:1");
                            $zrtphash->addAttribute('version', $matches[1]);
                            break;

                        // Non standard
                        case 'rtcp_mux':
                            $description->addChild('rtcp-mux');
                            break;

                        // http://xmpp.org/extensions/xep-0294.html
                        case 'extmap':
                            $rtphdrext = $description->addChild('rtp-hdrext');
                            $rtphdrext->addAttribute('xmlns', "urn:xmpp:jingle:apps:rtp:rtp-hdrext:0");
                            $rtphdrext->addAttribute('id', $matches[1]);
                            $rtphdrext->addAttribute('uri', $matches[4]);
                            if (isset($matches[3]) && $matches[3] != '') {
                                $rtphdrext->addAttribute('senders', $this->directionToSenders[$matches[3]]);
                            }
                            break;

                        // https://xmpp.org/extensions/xep-0166.html#def-content
                        case 'senders':
                            if ($this->content != null) {
                                $this->content->addAttribute('senders', $this->directionToSenders[$matches[1]]);
                            }
                            break;

                        // http://xmpp.org/extensions/xep-0339.html
                        case 'ssrc':
                            $sources = $description->xpath('source[@ssrc="' . $matches[1] . '"]');
                            $ssrc = is_array($sources) && count($sources) > 0 ? $sources[0] : null;

                            if ($ssrc == null) {
                                $ssrc = $description->addChild('source');
                                $ssrc->addAttribute('xmlns', "urn:xmpp:jingle:apps:rtp:ssma:0");
                                $ssrc->addAttribute('ssrc', $matches[1]);
                            }

                            if ($this->msid != null && $matches[2] != 'msid') {
                                $param = $ssrc->addChild('parameter');
                                $param->addAttribute('name', 'msid');
                                $param->addAttribute('value', $this->msid);
                            }

                            $param = $ssrc->addChild('parameter');
                            $param->addAttribute('name', $matches[2]);
                            $param->addAttribute('value', $matches[4]);
                            break;

                        case 'ptime':
                            $description->addAttribute('ptime', $matches[1]);
                            break;

                        case 'maxptime':
                            $description->addAttribute('maxptime', $matches[1]);
                            break;

                        // http://xmpp.org/extensions/xep-0338.html
                        case 'group':
                            $group = $this->jingle->addChild('group');
                            $group->addAttribute('xmlns', "urn:xmpp:jingle:apps:grouping:0");
                            $group->addAttribute('semantics', $matches[1]);

                            $params = explode(' ', $matches[2]);

                            foreach ($params as $value) {
                                $content = $group->addChild('content');
                                $content->addAttribute('name', trim($value));
                            }
                            break;

                        // http://xmpp.org/extensions/xep-0320.html
                        case 'fingerprint':
                            if ($this->content == null) {
                                $this->globalFingerprint['fingerprint'] = $matches[2];
                                $this->globalFingerprint['hash']        = $matches[1];
                            } else {
                                $fingerprint = $this->transport->addChild('fingerprint', $matches[2]);
                                $fingerprint->addAttribute('xmlns', "urn:xmpp:jingle:apps:dtls:0");
                                $fingerprint->addAttribute('hash', $matches[1]);
                            }

                            break;

                        // https://xmpp.org/extensions/xep-0343.html
                        case 'sctpmap':
                            $sctpmap = $this->transport->addChild('sctpmap');
                            $sctpmap->addAttribute('xmlns', "urn:xmpp:jingle:transports:dtls-sctp:1");
                            $sctpmap->addAttribute('number', $matches[1]);
                            $sctpmap->addAttribute('protocol', $matches[2]);
                            $sctpmap->addAttribute('streams', $matches[3]);
                            break;

                        // http://xmpp.org/extensions/xep-0320.html
                        case 'setup':
                            if ($this->content != null) {
                                $fingerprint->addAttribute('setup', $matches[1]);
                            }

                            break;

                        case 'pwd':
                            $session = Session::instance();
                            $session->set('icePwd', $matches[1]);
                            $this->transport->addAttribute('pwd', $matches[1]);
                            break;

                        case 'ufrag':
                            $this->transport->addAttribute('ufrag', $matches[1]);
                            break;

                        case 'candidate':
                            $this->initContent();
                            $this->addName();

                            if (empty($this->jingle->attributes()->sid)) {
                                $this->jingle->addAttribute('sid', $this->sid);
                            }

                            $candidate = $this->transport->addChild('candidate');

                            $candidate->addAttribute('foundation', $matches[1]);
                            $candidate->addAttribute('component', $matches[2]);
                            $candidate->addAttribute('protocol', strtolower($matches[3]));
                            $candidate->addAttribute('priority', $matches[4]);
                            $candidate->addAttribute('ip', $matches[5]);
                            $candidate->addAttribute('port', $matches[6]);
                            $candidate->addAttribute('type', $matches[8]);

                            // We have other arguments
                            $args = [];
                            if (isset($matches[9])) {
                                $keyValues = explode(' ', trim($matches[9]));
                                foreach ($keyValues as $key)

                                    foreach (array_chunk($keyValues, 2) as $pair) {
                                        list($key, $value) = $pair;
                                        $args[$key] = $value;
                                    }
                            }

                            $candidate->addAttribute(
                                'generation',
                                isset($args['generation'])
                                    ? $args['generation']
                                    : 0
                            );

                            if (isset($args['ufrag'])) {
                                $this->ufrag = $args['ufrag'];
                            }

                            if (isset($args['id'])) {
                                $candidate->addAttribute('id', $args['id']);
                            }
                            if (isset($args['network'])) {
                                $candidate->addAttribute('network', $args['network']);
                            }
                            if (isset($args['network-id'])) {
                                $candidate->addAttribute('network-id', $args['network-id']);
                            }
                            if (isset($args['network-cost'])) {
                                $candidate->addAttribute('network-cost', $args['network-cost']);
                            }
                            if (isset($args['raddr'])) {
                                $candidate->addAttribute('rel-addr', $args['raddr']);
                            }
                            if (isset($args['rport'])) {
                                $candidate->addAttribute('rel-port', $args['rport']);
                            }
                            if (isset($args['tcptype'])) {
                                $candidate->addAttribute('tcptype', $args['tcptype']);
                            }

                            // ufrag to the transport
                            $session = Session::instance();
                            if ($this->ufrag && $session->get('icePwd')) {
                                $this->transport->addAttribute('ufrag', $this->ufrag);
                                $this->transport->addAttribute('pwd', $session->get('icePwd'));
                            }

                            break;
                    }
                }
            }
        }

        return dom_import_simplexml($this->jingle);
    }
}
