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
        'JAXLPlugin',
        'JAXLog',
        'XMPPGet',
        'XMPPSend'
    ));
    
    /**
     * Base XMPP class
    */
    class XMPP {
        
        /**
         * Auth status of Jaxl instance
         *
         * @var bool 
        */
        var $auth = false;

        /**
         * Connected XMPP stream session requirement status
         *
         * @var bool
        */
        var $sessionRequired = false;

        /**
         * SASL second auth challenge status
         *
         * @var bool
        */
        var $secondChallenge = false;

        /**
         * Id of connected Jaxl instance
         *
         * @var integer
        */
        var $lastid = 0;
        
        /**
         * Connected socket stream handler
         *
         * @var bool|object
        */
        var $stream = false;

        /**
         * Connected socket stream id
         *
         * @var bool|integer
        */
        var $streamId = false;

        /**
         * Socket stream connected host
         *
         * @var bool|string
        */
        var $streamHost = false;

        /**
         * Socket stream version
         *
         * @var bool|integer
        */
        var $streamVersion = false;

        /**
         * Last error number for connected socket stream
         *
         * @var bool|integer
        */
        var $streamENum = false;

        /**
         * Last error string for connected socket stream
         *
         * @var bool|string
        */
        var $streamEStr = false;

        /**
         * Timeout value for connecting socket stream
         *
         * @var bool|integer
        */
        var $streamTimeout = false;

        /**
         * Blocking or Non-blocking socket stream
         *
         * @var bool
        */
        var $streamBlocking = 0;
        
        /**
         * Nap in seconds between two socket reads
         *
         * @var integer
        */
        var $getSleep = 1;

        /**
         * Number of packets to read in one socket read
         *
         * @var integer
        */
        var $getPkts = false;

        /**
         * Size of each packet to be read from the socket
        */
        var $getPktSize = false;

        /**
         * Number of empty packet read before aborting further reads
        */
        var $getEmptyLines = false;

        /**
         * Maximum rate at which XMPP stanza's can flow out
        */
        var $sendRate = false;

        /**
         * Input XMPP stream buffer
        */
        var $buffer = '';

        /**
         * Output XMPP stream buffer
        */
        var $obuffer = '';

        /**
         * Current value of instance clock
        */
        var $clock = false;

        /**
         * When was instance clock last sync'd?
        */
        var $clocked = false;

        /**
         * Enable/Disable rate limiting
        */
        var $rateLimit = true;

        /**
         * Timestamp of last outbound XMPP packet
        */
        var $lastSendTime = false;
        
        /**
         * XMPP constructor
        */
        function __construct($config) {
            $this->clock = 0;
            $this->clocked = time();
            
            /* Parse configuration parameter */
            $this->lastid = rand(1, 9);
            $this->streamTimeout = isset($config['streamTimeout']) ? $config['streamTimeout'] : 20;
            $this->rateLimit = isset($config['rateLimit']) ? $config['rateLimit'] : true;
            $this->getPkts = isset($config['getPkts']) ? $config['getPkts'] : 1600;
            $this->getPktSize = isset($config['getPktSize']) ? $config['getPktSize'] : 2048;
            $this->getEmptyLines = isset($config['getEmptyLines']) ? $config['getEmptyLines'] : 15;
            $this->sendRate = isset($config['sendRate']) ? $config['sendRate'] : 0.1;
        }
        
        /**
         * Open socket stream to jabber server host:port for connecting Jaxl instance
        */
        function connect() {
            if(!$this->stream) {
                if($this->stream = @fsockopen($this->host, $this->port, $this->streamENum, $this->streamEStr, $this->streamTimeout)) {
                    $this->log("[[XMPP]] Socket opened to the jabber host ".$this->host.":".$this->port." ...");
                    stream_set_blocking($this->stream, $this->streamBlocking);
                    stream_set_timeout($this->stream, $this->streamTimeout);
                }
                else {
                    $this->log("[[XMPP]] Unable to open socket to the jabber host ".$this->host.":".$this->port." ...");
                    throw new JAXLException("[[XMPP]] Unable to open socket to the jabber host");
                }
            }
            else {
                $this->log("[[XMPP]] Socket already opened to the jabber host ".$this->host.":".$this->port." ...");
            }

            $ret = $this->stream ? true : false;
            JAXLPlugin::execute('jaxl_post_connect', $ret, $this);
            return $ret;
        }
        
        /**
         * Send XMPP start stream
        */
        function startStream() {
            return XMPPSend::startStream($this);
        }

        /**
         * Send XMPP end stream
        */
        function endStream() {
            return XMPPSend::endStream($this);
        }
        
        /**
         * Send session start XMPP stanza 
        */
        function startSession() {
            return XMPPSend::startSession($this, array('XMPPGet', 'postSession'));
        }
        
        /**
         * Bind connected Jaxl instance to a resource
        */
        function startBind() {
            return XMPPSend::startBind($this, array('XMPPGet', 'postBind'));
        }
       
        /**
         * Return back id for use with XMPP stanza's
         *
         * @return integer $id
        */
        function getId() {
            $id = JAXLPlugin::execute('jaxl_get_id', ++$this->lastid, $this);
            if($id === $this->lastid) return dechex($this->uid + $this->lastid);
            else return $id;
        }
        
        /**
         * Read connected XMPP stream for new data
        */
        function getXML($nap=TRUE) {
            // sleep between two reads
            if($nap) sleep($this->getSleep);
            
            // initialize empty lines read
            $emptyLine = 0;
            
            // read previous buffer
            $payload = $this->buffer;
            $this->buffer = '';
            
            // read socket data
            for($i=0; $i<$this->getPkts; $i++) {
                if($this->stream) {
                    $line = fread($this->stream, $this->getPktSize);
                    if(strlen($line) == 0) {
                        $emptyLine++;
                        if($emptyLine > $this->getEmptyLines)
                            break;
                    }
                    else {
                        $payload .= $line;
                    }
                }
            }
            
            // update clock
            $now = time();
            $this->clock += $now-$this->clocked;
            $this->clocked = $now;

            // trim read data
            $payload = trim($payload);
            $payload = JAXLPlugin::execute('jaxl_get_xml', $payload, $this);
            if($payload != '') $this->handler($payload);
            
            // flush obuffer
            if($this->obuffer != '') {
                $payload = $this->obuffer;
                $this->obuffer = '';
                $this->_sendXML($payload);
            }
        }
        
        /**
         * Send XMPP XML packet over connected stream
        */
        function sendXML($xml, $force=false) {
            $xml = JAXLPlugin::execute('jaxl_send_xml', $xml, $this);
            
            if($this->mode == "cgi") {
                JAXLPlugin::execute('jaxl_send_body', $xml, $this);
            }
            else {
                if($this->rateLimit
                && !$force
                && $this->lastSendTime
                && JAXLUtil::getTime() - $this->lastSendTime < $this->sendRate
                ) { $this->obuffer .= $xml; }
                else {
                    $xml = $this->obuffer.$xml;
                    $this->obuffer = '';
                    return $this->_sendXML($xml);
                }
            }
        }

        /**
         * Send XMPP XML packet over connected stream
        */
        protected function _sendXML($xml) {
            if($this->stream) {
                $this->lastSendTime = JAXLUtil::getTime();
                if(($ret = fwrite($this->stream, $xml)) !== false) {
                    $this->log("[[XMPPSend]] $ret\n".$xml, 4);
                }
                else {
                    $this->log("[[XMPPSend]] Failed\n".$xml);
                    throw new JAXLException("[[XMPPSend]] Failed");
                }
                return $ret;
            }
            else {
                $this->log("[[XMPPSend]] Jaxl stream not connected to jabber host, unable to send xmpp payload...");
                throw new JAXLException("[[XMPPSend]] Jaxl stream not connected to jabber host, unable to send xmpp payload...");
                return false;
            }
        }
        
        /**
         * Routes incoming XMPP data to appropriate handlers
        */
        function handler($payload) {
            $this->log("[[XMPPGet]] \n".$payload, 4);
            
            $buffer = array();
            $payload = JAXLPlugin::execute('jaxl_pre_handler', $payload, $this);
            
            $xmls = JAXLUtil::splitXML($payload);
            $pktCnt = count($xmls);
            
            foreach($xmls as $pktNo => $xml) {  
                if($pktNo == $pktCnt-1) {
                    if(substr($xml, -1, 1) != '>') {
                        $this->buffer = $xml;
                        break;
                    }
                }
                
                if(substr($xml, 0, 7) == '<stream') 
                    $arr = $this->xml->xmlize($xml);
                else 
                    $arr = JAXLXml::parse($xml);
                
                switch(true) {
                    case isset($arr['stream:stream']):
                        XMPPGet::streamStream($arr['stream:stream'], $this);
                        break;
                    case isset($arr['stream:features']):
                        XMPPGet::streamFeatures($arr['stream:features'], $this);
                        break;
                    case isset($arr['stream:error']):
                        XMPPGet::streamError($arr['stream:error'], $this);
                        break;
                    case isset($arr['failure']);
                        XMPPGet::failure($arr['failure'], $this);
                        break;
                    case isset($arr['proceed']):
                        XMPPGet::proceed($arr['proceed'], $this);
                        break;
                    case isset($arr['challenge']):
                        XMPPGet::challenge($arr['challenge'], $this);
                        break;
                    case isset($arr['success']):
                        XMPPGet::success($arr['success'], $this);
                        break;
                    case isset($arr['presence']):
                        $buffer['presence'][] = $arr['presence'];
                        break;
                    case isset($arr['message']):
                        $buffer['message'][] = $arr['message'];
                        break;
                    case isset($arr['iq']):
                        XMPPGet::iq($arr['iq'], $this);
                        break;
                    default:
                        $jaxl->log("[[XMPPGet]] Unrecognized payload received from jabber server...");
                        throw new JAXLException("[[XMPPGet]] Unrecognized payload received from jabber server...");
                        break;
                }
            }
            
            if(isset($buffer['presence'])) XMPPGet::presence($buffer['presence'], $this);
            if(isset($buffer['message'])) XMPPGet::message($buffer['message'], $this);
            unset($buffer);
            
            JAXLPlugin::execute('jaxl_post_handler', $payload, $this);
        }

    }

?>
