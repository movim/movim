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
 * @subpackage xmpp
 * @author Abhinav Singh <me@abhinavsingh.com>
 * @copyright Abhinav Singh
 * @link http://code.google.com/p/jaxl 
 */

    // include required classes
    jaxl_require(array(
        'JAXLUtil',
        'JAXLPlugin'
    ));
    
    /**
     * XMPP Auth class for performing various SASL auth mechanisms
     * DIGEST-MD5, X-FACEBOOK-PLATFORM, SCRAM-SHA-1, CRAM-MD5
    */
    class XMPPAuth {
        
        public static function getResponse($authType, $challenge, $jaxl) {
            $response = array();
            $decoded = base64_decode($challenge);
            $xml = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">';
            
            if($authType == 'X-FACEBOOK-PLATFORM') {
                $decoded = explode('&', $decoded);
                foreach($decoded as $k=>$v) {
                    list($kk, $vv) = explode('=', $v);
                    $decoded[$kk] = $vv;
                    unset($decoded[$k]);
                }
                        
                list($secret, $decoded['api_key'], $decoded['session_key']) = $jaxl->executePlugin('jaxl_get_facebook_key', false);
                        
                $decoded['call_id'] = $jaxl->clock;
                $decoded['v'] = '1.0';
                        
                $base_string = '';
                foreach(array('api_key', 'call_id', 'method', 'nonce', 'session_key', 'v') as $key) {
                    if(isset($decoded[$key])) {
                        $response[$key] = $decoded[$key];
                        $base_string .= $key.'='.$decoded[$key];
                    }
                }
                        
                $base_string .= $secret;
                $response['sig'] = md5($base_string);
                        
                $responseURI = '';
                foreach($response as $k=>$v) {
                    if($responseURI == '') $responseURI .= $k.'='.urlencode($v);
                    else $responseURI .= '&'.$k.'='.urlencode($v);
                }
                        
                $xml .= base64_encode($responseURI);
            }
            else if($authType == 'DIGEST-MD5') {
                $decoded = JAXLUtil::explodeData($decoded);     
                if(!isset($decoded['digest-uri'])) $decoded['digest-uri'] = 'xmpp/'.$jaxl->domain;  
                $decoded['cnonce'] = base64_encode(JAXLUtil::generateNonce());
                        
                if(isset($decoded['qop'])
                && $decoded['qop'] != 'auth' 
                && strpos($decoded['qop'],'auth') !== false
                ) { $decoded['qop'] = 'auth'; }
                        
                $response = array('username'=>$jaxl->user,
                    'response' => JAXLUtil::encryptPassword(array_merge($decoded,array('nc'=>'00000001')), $jaxl->user, $jaxl->pass),
                    'charset' => 'utf-8',
                    'nc' => '00000001',
                    'qop' => 'auth'
                );
                        
                foreach(array('nonce', 'digest-uri', 'realm', 'cnonce') as $key)
                    if(isset($decoded[$key]))
                        $response[$key] = $decoded[$key];
                
                $xml .= base64_encode(JAXLUtil::implodeData($response));
            }
            else if($authType == 'SCRAM-SHA-1') {
                $decoded = JAXLUtil::explodeData($decoded);
                        
                // SaltedPassword  := Hi(Normalize(password), salt, i)
                $saltedPasswd = JAXLUtil::pbkdf2($jaxl->pass, $decoded['s'], $decoded['i']);
                        
                // ClientKey       := HMAC(SaltedPassword, "Client Key")
                $clientKey = JAXLUtil::hashMD5($saltedPassword, "Client Key");
                        
                // StoredKey       := H(ClientKey)
                $storedKey = sha1("Client Key");
                        
                // assemble client-final-message-without-proof
                $clientFinalMessage = "c=bwis,r=".$decoded['r'];
                        
                // AuthMessage     := client-first-message-bare + "," + server-first-message + "," + client-final-message-without-proof
                // ClientSignature := HMAC(StoredKey, AuthMessage)
                
                // ClientProof     := ClientKey XOR ClientSignature
                // ServerKey       := HMAC(SaltedPassword, "Server Key")
                // ServerSignature := HMAC(ServerKey, AuthMessage)
                        
                foreach(array('c', 'r', 'p') as $key)
                    if(isset($decoded[$key]))
                        $response[$key] = $decoded[$key];
                        
                $xml .= base64_encode(JAXLUtil::implodeData($response));
            }
            else if($authType == 'CRAM-MD5') {
                $xml .= base64_encode($jaxl->user.' '.hash_hmac('md5', $jaxl->pass, $arr['challenge']));
            }

            $xml .= '</response>';
            $jaxl->secondChallenge = true;
            
            return $xml;
        } 
    }
    
?>
