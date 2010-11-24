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
                'url'   =>  'http://localhost:5280/http-bind'
            );
            
            // parse user options
            $jaxl->bosh['host'] = isset($jaxl->config['boshHost']) ? $jaxl->config['boshHost'] : (@constant("JAXL_BOSH_HOST") == null ? $jaxl->bosh['host'] : JAXL_BOSH_HOST);
            $jaxl->bosh['port'] = isset($jaxl->config['boshPort']) ? $jaxl->config['boshPort'] : (@constant("JAXL_BOSH_PORT") == null ? $jaxl->bosh['port'] : JAXL_BOSH_PORT);
            $jaxl->bosh['suffix'] = isset($jaxl->config['boshSuffix']) ? $jaxl->config['boshSuffix'] : (@constant("JAXL_BOSH_SUFFIX") == null ? $jaxl->bosh['suffix'] : JAXL_BOSH_SUFFIX);
            $jaxl->bosh['out'] = isset($jaxl->config['boshOut']) ? $jaxl->config['boshOut'] : (@constant("JAXL_BOSH_OUT") == null ? $jaxl->bosh['out'] : JAXL_BOSH_OUT);
            $jaxl->bosh['cookie']['ttl'] = isset($jaxl->config['boshCookieTTL']) ? $jaxl->config['boshCookieTTL'] : (@constant("JAXL_BOSH_COOKIE_TTL") == null ? $jaxl->bosh['cookie']['ttl'] : JAXL_BOSH_COOKIE_TTL);
            $jaxl->bosh['cookie']['path'] = isset($jaxl->config['boshCookiePath']) ? $jaxl->config['boshCookiePath'] : (@constant("JAXL_BOSH_COOKIE_PATH") == null ? $jaxl->bosh['cookie']['path'] : JAXL_BOSH_COOKIE_PATH);
            $jaxl->bosh['cookie']['domain'] = isset($jaxl->config['boshCookieDomain']) ? $jaxl->config['boshCookieDomain'] : (@constant("JAXL_BOSH_COOKIE_DOMAIN") == null ? $jaxl->bosh['cookie']['domain'] : JAXL_BOSH_COOKIE_DOMAIN);
            $jaxl->bosh['cookie']['https'] = isset($jaxl->config['boshCookieHTTPS']) ? $jaxl->config['boshCookieHTTPS'] : (@constant("JAXL_BOSH_COOKIE_HTTPS") == null ? $jaxl->bosh['cookie']['https'] : JAXL_BOSH_COOKIE_HTTPS);
            $jaxl->bosh['cookie']['httponly'] = isset($jaxl->config['boshCookieHTTPOnly']) ? $jaxl->config['boshCookieHTTPOnly'] : (@constant("JAXL_BOSH_COOKIE_HTTP_ONLY") == null ? $jaxl->bosh['cookie']['httponly'] : JAXL_BOSH_COOKIE_HTTP_ONLY); 
            $jaxl->bosh['url'] = "http://".$jaxl->bosh['host'].":".$jaxl->bosh['port']."/".$jaxl->bosh['suffix']."/";
           
           session_set_cookie_params(
                $jaxl->bosh['cookie']['ttl'],
                $jaxl->bosh['cookie']['path'],
                $jaxl->bosh['cookie']['domain'],
                $jaxl->bosh['cookie']['https'],
                $jaxl->bosh['cookie']['httponly']
            );
            session_start();
            
            JAXLPlugin::add('jaxl_post_bind', array('JAXL0124', 'postBind'));
            JAXLPlugin::add('jaxl_send_xml', array('JAXL0124', 'wrapBody'));
            JAXLPlugin::add('jaxl_pre_handler', array('JAXL0124', 'preHandler'));
            JAXLPlugin::add('jaxl_post_handler', array('JAXL0124', 'postHandler'));
            JAXLPlugin::add('jaxl_pre_curl', array('JAXL0124', 'saveSession'));
            JAXLPlugin::add('jaxl_send_body', array('JAXL0124', 'sendBody'));

            self::loadSession($jaxl);
        }
        
        public static function postHandler($payload, $jaxl) {
            if(!$jaxl->bosh['out']) return $payload;

            $payload = json_encode(self::$buffer);
            $jaxl->log("[[BoshOut]]\n".$payload, 5);
            header($jaxl->bosh['outheaders']);
            echo $payload;
            exit;
        }
        
        public static function postBind($jaxl) {
            $jaxl->bosh['jid'] = $jaxl->jid;
            $_SESSION['auth'] = true;
            return;
        }
        
        public static function out($payload) {
            self::$buffer[] = $payload;
        }
        
        public static function loadSession($jaxl) {
            $jaxl->bosh['rid'] = isset($_SESSION['rid']) ? (string) $_SESSION['rid'] : rand(1000, 10000);
            $jaxl->bosh['sid'] = isset($_SESSION['sid']) ? (string) $_SESSION['sid'] : false;
            $jaxl->lastid = isset($_SESSION['id']) ? $_SESSION['id'] : $jaxl->lastid;
            $jaxl->jid = isset($_SESSION['jid']) ? $_SESSION['jid'] : $jaxl->jid;
            $jaxl->log("Loading session data\n".json_encode($_SESSION), 5);
        }
        
        public static function saveSession($xml, $jaxl) {
            if($_SESSION['auth'] === true) {
                $_SESSION['rid'] = isset($jaxl->bosh['rid']) ? $jaxl->bosh['rid'] : false;
                $_SESSION['sid'] = isset($jaxl->bosh['sid']) ? $jaxl->bosh['sid'] : false;
                $_SESSION['jid'] = $jaxl->jid;
                $_SESSION['id'] = $jaxl->lastid;

                if($jaxl->bosh['out'])
                    session_write_close();

                if(self::$sess && $jaxl->bosh['out']) {
                    list($body, $xml) = self::unwrapBody($xml);
                    $jaxl->log("[[".$_REQUEST['jaxl']."]] Auth complete, sync now\n".json_encode($_SESSION), 5);
                    return self::out(array('jaxl'=>'jaxl', 'xml'=>urlencode($xml)));
                }
                else {
                    self::$sess = true;
                    $jaxl->log("[[".$_REQUEST['jaxl']."]] Auth complete, commiting session now\n".json_encode($_SESSION), 5);
                }
            }
            else {
                $jaxl->log("[[".$_REQUEST['jaxl']."]] Not authed yet, Not commiting session\n".json_encode($_SESSION), 5);
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
                
                $_SESSION['rid'] = $jaxl->bosh['rid'];
            }
            
            return $body;
        }
        
        public static function sendBody($xml, $jaxl) {
            $xml = JAXLPlugin::execute('jaxl_pre_curl', $xml, $jaxl);
            if($xml != false) {
                $jaxl->log("[[XMPPSend]] body\n".$xml, 4);
                
                $payload = JAXLUtil::curl($jaxl->bosh['url'], 'POST', $jaxl->bosh['headers'], $xml);
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
                    if($_SESSION['auth'] === 'disconnect') {
                        $_SESSION['auth'] = false;
                        JAXLPlugin::execute('jaxl_post_disconnect');
                    }
                    else {
                        JAXLPlugin::execute('jaxl_get_empty_body', $body, $jaxl);
                    }
                }

                if($_SESSION['auth'] === 'connect') {
                    $arr = $jaxl->xml->xmlize($body);
                    if(isset($arr["body"]["@"]["sid"])) {
                        $_SESSION['auth'] = false;
                        $_SESSION['sid'] = $arr["body"]["@"]["sid"];
                        $jaxl->bosh['sid'] = $arr["body"]["@"]["sid"];
                    } 
                }
            }
            return $payload;
        }
        
    }
    
?>
