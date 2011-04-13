<?php
/**
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2010, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Abhinav Singh nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package jaxl
 * @subpackage xep
 * @author Abhinav Singh <me@abhinavsingh.com>
 * @copyright Abhinav Singh
 * @link http://code.google.com/p/jaxl
 */

    /**
     * XEP-0124: Bosh Implementation
     * Maintain various attributes like rid, sid across requests
    */
    class JAXL0124 {
        
        private static $buffer = array();
        private static $sess = false;
        
        public static function init($jaxl) {
            // initialize working parameters for this jaxl instance
            $jaxl->bosh = array(
                'host'  =>  'localhost',
                'port'  =>  5280,
                'suffix'=>  'http-bind',
                'out'   =>  true,
                'outheaders'    =>  'Content-type: application/json', 
                'cookie'=>  array(
                    'ttl'   =>  3600,
                    'path'  =>  '/',
                    'domain'=>  false,
                    'https' =>  false,
                    'httponly'  =>  true
                ),
                'hold'  =>  '1',
                'wait'  =>  '30',
                'polling'   =>  '0',
                'version'   =>  '1.6',
                'xmppversion'   =>  '1.0',
                'secure'=>  true,
                'content'   =>  'text/xml; charset=utf-8',
                'headers'   =>  array('Accept-Encoding: gzip, deflate','Content-Type: text/xml; charset=utf-8'),
                'xmlns' =>  'http://jabber.org/protocol/httpbind',
                'xmlnsxmpp' =>  'urn:xmpp:xbosh',
                'url'   =>  'http://localhost:5280/http-bind',
                'session'   =>  true
            );
            
            // parse user options
            $jaxl->bosh['host'] = $jaxl->getConfigByPriority(@$jaxl->config['boshHost'], "JAXL_BOSH_HOST", $jaxl->bosh['host']);
            $jaxl->bosh['port'] = $jaxl->getConfigByPriority(@$jaxl->config['boshPort'], "JAXL_BOSH_PORT", $jaxl->bosh['port']);
            $jaxl->bosh['suffix'] = $jaxl->getConfigByPriority(@$jaxl->config['boshSuffix'], "JAXL_BOSH_SUFFIX", $jaxl->bosh['suffix']);
            $jaxl->bosh['out'] = $jaxl->getConfigByPriority(@$jaxl->config['boshOut'], "JAXL_BOSH_OUT", $jaxl->bosh['out']);
            $jaxl->bosh['session'] = $jaxl->getConfigByPriority(@$jaxl->config['boshSession'], "JAXL_BOSH_SESSION", $jaxl->bosh['session']);
            $jaxl->bosh['url'] = "http://".$jaxl->bosh['host'].":".$jaxl->bosh['port']."/".$jaxl->bosh['suffix']."/";

            // cookie params
            $jaxl->bosh['cookie']['ttl'] = $jaxl->getConfigByPriority(@$jaxl->config['boshCookieTTL'], "JAXL_BOSH_COOKIE_TTL", $jaxl->bosh['cookie']['ttl']);
            $jaxl->bosh['cookie']['path'] = $jaxl->getConfigByPriority(@$jaxl->config['boshCookiePath'], "JAXL_BOSH_COOKIE_PATH", $jaxl->bosh['cookie']['path']);
            $jaxl->bosh['cookie']['domain'] = $jaxl->getConfigByPriority(@$jaxl->config['boshCookieDomain'], "JAXL_BOSH_COOKIE_DOMAIN", $jaxl->bosh['cookie']['domain']);
            $jaxl->bosh['cookie']['https'] = $jaxl->getConfigByPriority(@$jaxl->config['boshCookieHTTPS'], "JAXL_BOSH_COOKIE_HTTPS", $jaxl->bosh['cookie']['https']);
            $jaxl->bosh['cookie']['httponly'] = $jaxl->getConfigByPriority(@$jaxl->config['boshCookieHTTPOnly'], "JAXL_BOSH_COOKIE_HTTP_ONLY", $jaxl->bosh['cookie']['httponly']);
            
            // start session
            self::startSession($jaxl);
            
            $jaxl->addPlugin('jaxl_post_bind', array('JAXL0124', 'postBind'));
            $jaxl->addPlugin('jaxl_send_xml', array('JAXL0124', 'wrapBody'));
            $jaxl->addPlugin('jaxl_pre_handler', array('JAXL0124', 'preHandler'));
            $jaxl->addPlugin('jaxl_post_handler', array('JAXL0124', 'postHandler'));
            $jaxl->addPlugin('jaxl_send_body', array('JAXL0124', 'sendBody'));

            self::loadSession($jaxl);
        }

        public static function startSession($jaxl) {
            if(!$jaxl->bosh['session']) {
                $jaxl->log("[[JAXL0124]] Not starting session as forced by constructor config", 5);
                return;
            }

            session_set_cookie_params(
                $jaxl->bosh['cookie']['ttl'],
                $jaxl->bosh['cookie']['path'],
                $jaxl->bosh['cookie']['domain'],
                $jaxl->bosh['cookie']['https'],
                $jaxl->bosh['cookie']['httponly']
            );
            session_start();
        }
        
        public static function postHandler($payload, $jaxl) {
            if(!$jaxl->bosh['out']) return $payload;
            $payload = json_encode(self::$buffer);
            $jaxl->log("[[BoshOut]]\n".$payload, 5);
            header($jaxl->bosh['outheaders']);
            echo $payload;
            exit;
        }
        
        public static function postBind($payload, $jaxl) {
            $jaxl->bosh['jid'] = $jaxl->jid;
            $_SESSION['jaxl_auth'] = true;
            return;
        }
        
        public static function out($payload) {
            self::$buffer[] = $payload;
        }
        
        public static function loadSession($jaxl) {
            $jaxl->bosh['rid'] = isset($_SESSION['jaxl_rid']) ? (string) $_SESSION['jaxl_rid'] : rand(1000, 10000);
            $jaxl->bosh['sid'] = isset($_SESSION['jaxl_sid']) ? (string) $_SESSION['jaxl_sid'] : false;
            $jaxl->lastid = isset($_SESSION['jaxl_id']) ? $_SESSION['jaxl_id'] : $jaxl->lastid;
            $jaxl->jid = isset($_SESSION['jaxl_jid']) ? $_SESSION['jaxl_jid'] : $jaxl->jid;
            $jaxl->log("[[JAXL0124]] Loading session data\n".json_encode($_SESSION), 5);
        }
        
        public static function saveSession($xml, $jaxl) {
            if($_SESSION['jaxl_auth'] === true) {
                $_SESSION['jaxl_rid'] = isset($jaxl->bosh['rid']) ? $jaxl->bosh['rid'] : false;
                $_SESSION['jaxl_sid'] = isset($jaxl->bosh['sid']) ? $jaxl->bosh['sid'] : false;
                $_SESSION['jaxl_jid'] = $jaxl->jid;
                $_SESSION['jaxl_id'] = $jaxl->lastid;
                
                if($jaxl->bosh['out'] && $jaxl->bosh['session']) {
                    session_write_close();
                }

                if(self::$sess && $jaxl->bosh['out']) {
                    list($body, $xml) = self::unwrapBody($xml);
                    $jaxl->log("[[JAXL0124]] Auth complete, sync now\n".json_encode($_SESSION), 5);
                    return self::out(array('jaxl'=>'jaxl', 'xml'=>urlencode($xml)));
                }
                else {
                    self::$sess = true;
                    $jaxl->log("[[JAXL0124]] Auth complete, commiting session now\n".json_encode($_SESSION), 5);
                }
            }
            else {
                $jaxl->log("[[JAXL0124]] Not authed yet, Not commiting session\n".json_encode($_SESSION), 5);
            }
            
            return $xml;
        }
        
        public static function wrapBody($xml, $jaxl) {
            $body = trim($xml);
            if(substr($body, 1, 4) != 'body') {
                $body = '';
                $body .= '<body rid="'.++$jaxl->bosh['rid'].'"';
                $body .= ' sid="'.$jaxl->bosh['sid'].'"';
                $body .= ' xmlns="http://jabber.org/protocol/httpbind">';
                $body .= $xml;
                $body .= "</body>";
                $_SESSION['jaxl_rid'] = $jaxl->bosh['rid'];
            }
            return $body;
        }
        
        public static function sendBody($xml, $jaxl) {
            $xml = self::saveSession($xml, $jaxl);
            if($xml != false) {
                $jaxl->log("[[XMPPSend]] body\n".$xml, 4);
                $payload = JAXLUtil::curl($jaxl->bosh['url'], 'POST', $jaxl->bosh['headers'], $xml);
                
                // curl error handling
                if($payload['errno'] != 0) {
                    $log = "[[JAXL0124]] Curl errno ".$payload['errno']." encountered";
                    switch($payload['errno']) {
                        case 7:
                            $log .= ". Failed to connect with ".$jaxl->bosh['url'];
                            break;
                        case 52:
                            $log .= ". Empty response rcvd from bosh endpoint";
                            break;
                        default:
                            break;
                    }

                    $jaxl->executePlugin('jaxl_get_bosh_curl_error', $payload);
                    $jaxl->log($log);
                }
                
                $payload = $payload['content'];
                $jaxl->handler($payload);
            }
            return $xml;
        }
        
        public static function unwrapBody($payload) {
            if(substr($payload, -2, 2) == "/>") preg_match_all('/<body (.*?)\/>/smi', $payload, $m);
            else preg_match_all('/<body (.*?)>(.*)<\/body>/smi', $payload, $m);
            
            if(isset($m[1][0])) $body = "<body ".$m[1][0].">";
            else $body = "<body>";
            
            if(isset($m[2][0])) $payload = $m[2][0];
            else $payload = '';
            
            return array($body, $payload);
        }
        
        public static function preHandler($payload, $jaxl) {
            if(substr($payload, 1, 4) == "body") {
                list($body, $payload) = self::unwrapBody($payload);
                
                if($payload == '') {
                    if($_SESSION['jaxl_auth'] === 'disconnect') {
                        $_SESSION['jaxl_auth'] = false;
                        $jaxl->executePlugin('jaxl_post_disconnect', $body);
                    }
                    else {
                        $jaxl->executePlugin('jaxl_get_empty_body', $body);
                    }
                }

                if($_SESSION['jaxl_auth'] === 'connect') {
                    $arr = $jaxl->xml->xmlize($body);
                    if(isset($arr["body"]["@"]["sid"])) {
                        $_SESSION['jaxl_auth'] = false;
                        $_SESSION['jaxl_sid'] = $arr["body"]["@"]["sid"];
                        $jaxl->bosh['sid'] = $arr["body"]["@"]["sid"];
                        $jaxl->log("[[JAXL0124]] Updated session to ".json_encode($_SESSION), 5);
                    } 
                }
            }
            return $payload;
        }
        
    }
    
?>
