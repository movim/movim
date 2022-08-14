<?php

use Movim\Session;

class SDPtoJingle
{
    private $sdp;
    private $arr;
    private $jingle;

    private $content    = null;
    private $transport  = null;

    private $action;

    private $ufrag = null;
    private $mid = null;
    private $sid;

    // Move the global fingerprint into each medias
    private $global_fingerprint = [];
    private $fmtp_cache = [];
    private $rtcp_fb_cache = [];

    private $regex = [
      'candidate'       => "/^a=candidate:(\w{1,32}) (\d{1,5}) (udp|tcp) (\d{1,10}) ([a-zA-Z0-9:\.]{1,45}) (\d{1,5}) (typ) (host|srflx|prflx|relay|ufrag)\s?(.+)?/i",
      'sess_id'         => "/^o=(\S+) (\d+)/i",
      'group'           => "/^a=group:(\S+) (.+)/i",
      'rtpmap'          => "/^a=rtpmap:(\d+) (([^\s\/]+)(\/(\d+)(\/([^\s\/]+))?)?)?/i",
      'fmtp'            => "/^a=fmtp:(\d+) (.+)/i",
      'rtcp_fb'         => "/^a=rtcp-fb:(\S+) (\S+)( (\S+))?/i",
      'rtcp_fb_trr_int' => "/^a=rtcp-fb:(\d+) trr-int (\d+)/i",
      'pwd'             => "/^a=ice-pwd:(\S+)/i",
      'ufrag'           => "/^a=ice-ufrag:(\S+)/i",
      'ptime'           => "/^a=ptime:(\d+)/i",
      'maxptime'        => "/^a=maxptime:(\d+)/i",
      'ssrc'            => "/^a=ssrc:(\d+) (\w+)(:(\S+))?( (\w+))?/i",
      'rtcp_mux'        => "/^a=rtcp-mux/i",
      'crypto'          => "/^a=crypto:(\d{1,9}) (\w+) (\S+)( (\S+))?/i",
      'zrtp_hash'       => "/^a=zrtp-hash:(\S+) (\w+)/i",
      'fingerprint'     => "/^a=fingerprint:(\S+) (\S+)/i",
      'setup'           => "/^a=setup:(\S+)/i",
      'extmap'          => "/^a=extmap:([^\s\/]+)(\/([^\s\/]+))? (\S+)/i",
      'sctpmap'         => "/^a=sctpmap:(\d+) (\S+) (\d+)/i",
      'mid'             => "/^a=mid:(\S+)/i",
      'bandwidth'       => "/^b=(\w+):(\d+)/i",
      'media'           => "/^m=(audio|video|application|data)/i"
    ];

    public function __construct($sdp, $initiator, $responder = false, $action = false, $mid = null, $ufrag = null)
    {
        $this->sdp = $sdp;
        $this->arr = explode("\n", $this->sdp);

        if ($mid !== null) {
            $this->mid = $mid;
        }

        if ($ufrag !== null) {
            $this->ufrag = $ufrag;
        }

        $this->jingle = new SimpleXMLElement('<jingle></jingle>');
        $this->jingle->addAttribute('xmlns', 'urn:xmpp:jingle:1');
        $this->jingle->addAttribute('initiator', $initiator);

        if ($action) {
            $this->jingle->addAttribute('action', $action);
        }
        if ($responder) {
            $this->jingle->addAttribute('responder', $responder);
        }

        $this->action = $action;
    }

    public function setSessionId(string $sid)
    {
        $this->sid = $sid;
    }

    private function getSessionId()
    {
        $s = Session::start();

        if ($sid = $s->get('jingleSid')) {
            return $sid;
        } else {
            $o = $this->arr[1];
            $sid = explode(" ", $o);
            return substr(base_convert($sid[1], 30, 10), 0, 6);
        }
    }

    private function initContent($force = false)
    {
        if ($this->content == null
        || $force) {
            $this->content      = $this->jingle->addChild('content');
            $this->transport    = $this->content->addChild('transport');
            $this->transport->addAttribute('xmlns', "urn:xmpp:jingle:transports:ice-udp:1");
            $this->content->addAttribute('creator', 'initiator'); // FIXME
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

    public function generate()
    {
        foreach ($this->arr as $l) {
            foreach ($this->regex as $key => $r) {
                if (preg_match($r, $l, $matches)) {
                    switch ($key) {
                        case 'sess_id':
                            $this->jingle->addAttribute('sid', $this->sid ?? $this->getSessionId());
                            break;
                        case 'media':
                            $this->initContent(true);

                            // The description node
                            if ($this->action != 'transport-info') {
                                $description = $this->content->addChild('description');
                                $description->addAttribute('xmlns', "urn:xmpp:jingle:apps:rtp:1");
                                $description->addAttribute('media', $matches[1]);
                            }

                            if (!empty($this->global_fingerprint)) {
                                $fingerprint = $this->transport->addChild('fingerprint', $this->global_fingerprint['fingerprint']);
                                $fingerprint->addAttribute('xmlns', "urn:xmpp:jingle:apps:dtls:0");
                                $fingerprint->addAttribute('hash', $this->global_fingerprint['hash']);
                            }

                            break;

                        case 'mid':
                            $this->addName($matches[1]);
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

                            if (isset($this->fmtp_cache[$matches[1]])) {
                                $this->addFmtpParameters($payloadtype, $this->fmtp_cache[$matches[1]]);
                                unset($this->fmtp_cache[$matches[1]]);
                            }

                            if (isset($this->rtcp_fb_cache[$matches[1]])) {
                                $this->addRtcpFbParameters($payloadtype, $this->rtcp_fb_cache[$matches[1]]);
                                unset($this->rtcp_fb_cache[$matches[1]]);
                            }

                            break;


                        // http://xmpp.org/extensions/xep-0167.html#format
                        case 'fmtp':
                            // If fmtp is added just after the correspondant rtpmap
                            $params = explode(';', $matches[2]);

                            if (isset($payloadtype)
                            && $matches[1] == $payloadtype->attributes()->id) {
                                $this->addFmtpParameters($payloadtype, $params);
                            // If not we cache it
                            } else {
                                $this->fmtp_cache[$matches[1]] = $params;
                            }
                            break;

                        // http://xmpp.org/extensions/xep-0293.html
                        case 'rtcp_fb':
                            if ($matches[1] == '*') {
                                $this->addRtcpFbParameters($description, [$matches]);
                            } else {
                                if (isset($payloadtype)
                                && $matches[1] == $payloadtype->attributes()->id) {
                                    $this->addRtcpFbParameters($payloadtype, [$matches]);
                                } else {
                                    if (!isset($this->rtcp_fb_cache[$matches[1]])) {
                                        $this->rtcp_fb_cache[$matches[1]] = [];
                                    }

                                    array_push($this->rtcp_fb_cache[$matches[1]], $matches);
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
                                $rtphdrext->addAttribute('senders', $matches[3]);
                            }
                            break;

                        // http://xmpp.org/extensions/xep-0339.html
                        case 'ssrc':
                            if (!$description->source) {
                                $ssrc = $description->addChild('source');
                                $ssrc->addAttribute('xmlns', "urn:xmpp:jingle:apps:rtp:ssma:0");
                                $ssrc->addAttribute('ssrc', $matches[1]);
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
                                $this->global_fingerprint['fingerprint'] = $matches[2];
                                $this->global_fingerprint['hash']        = $matches[1];
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
                            $s = Session::start();
                            $s->set('icePwd', $matches[1]);
                            $this->transport->addAttribute('pwd', $matches[1]);
                            break;

                        case 'ufrag':
                            $this->transport->addAttribute('ufrag', $matches[1]);
                            break;

                        case 'candidate':
                            $this->initContent();
                            $this->addName();

                            $this->jingle->addAttribute('sid', $this->sid ?? $this->getSessionId());
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
                                $keyValues = explode(' ', $matches[9]);
                                foreach ($keyValues as $key)

                                foreach (array_chunk($keyValues, 2) as $pair) {
                                    list($key, $value) = $pair;
                                    $args[$key] = $value;
                                }
                            }

                            $candidate->addAttribute('generation',
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
                            $s = Session::start();
                            if ($this->ufrag && $s->get('icePwd')) {
                                $this->transport->addAttribute('ufrag', $this->ufrag);
                                $this->transport->addAttribute('pwd', $s->get('icePwd'));
                            }

                            break;
                    }
                }
            }
        }

        return dom_import_simplexml($this->jingle);
    }
}
