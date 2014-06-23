<?php
/**
 * @file Request.php
 *
 * @brief Build and fire the XML requests to the BOSH module of the
 * XMPP server.
 *
 * Copyright © 2012 Timothée Jaussoin
 *
 * This file is part of Moxl.
 *
 * Moxl is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Moxl is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Datajar.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Moxl;

class Request {
    private $_xml;

    // Curl attributes
    private $_url;

    // System attributes
    private $_callback;

    // Proxy attributes
    private $_proxyenabled;
    private $_proxyurl;
    private $_proxyport;
    private $_proxyuser;
    private $_proxypass;
    
    private $_login;
    
    // Some attributes to catch the global state of the request
    // The global state ('ok' or 'error')
    public $state;
    
    // If an error occured
    public $error_number;
    public $error_message;
    
    // If all is OK
    public $xmlr;

    public function __construct($xml = false)
    {
        // We load the session in the internal attributes
        $session = \Sessionx::start();
        
        $this->_url = $session->url;
        /*
        $this->_proxyenabled = $session['proxyenabled'];
        $this->_proxyurl  = $session['proxyurl'];
        $this->_proxyport = $session['proxyport'];
        $this->_proxyuser = $session['proxyuser'];
        $this->_proxypass = $session['proxypass'];
        */
        // We set the XML
        if($xml != false)
            $this->_xml = $xml;
    }
    
    public function cut() {
        $this->_cut = true;
    }

    public function setXML($xml)
    {
        // Load and save the XML using DOMDocument to clean it
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xml = $doc->saveXML();
        $this->_xml = $xml;

        return $this;
    }


    public function fire()
    {
        Utils::log(
            $this->_url."\n"
            .">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n"
            .Utils::cleanXML($this->_xml)
            .">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>", LOG_DEBUG);

        $ch = curl_init($this->_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Little hack to fix a return error on the Squid proxy Daemon
        curl_setopt($ch, CURLOPT_HTTPHEADER,
                            array(
                                'Expect:',
                                /*'User-Agent: Moxl',
                                'Connection: close',
                                'Content-Type: text/xml; charset=utf-8',
                                'Content-length: '.strlen($this->_xml))*/)); 

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, trim($this->_xml));

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if($this->_proxyenabled) {
            curl_setopt($ch, CURLOPT_PROXY, $this->_proxyurl);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->_proxyport);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->_proxyuser.":".$this->_proxypass);

        }

        // Fire !
        $rs = array();

        $content = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", curl_exec($ch));

        $rs['content'] = $content;
        $rs['errno'] = curl_errno($ch);
        $rs['errmsg'] = curl_error($ch);
        $rs['header'] = curl_getinfo($ch);
        if($rs['content'] != false && $rs['content'] != '') {
            Utils::log(
                "<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<\n"
                .Utils::cleanXML($rs['content'])
                ."<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<", LOG_DEBUG);
                
            $this->state = 'ok';
            $this->xmlr  = $rs;
        }

        /*
         * If an error occur during the request, we log it and stop the
         * ping loop
         */
        else {
            Utils::log(
                "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n"
                ."Error n°".$rs['errno']." - ".$rs['errmsg']."\n"
                ."!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!", LOG_ERR);

            $this->state = 'error';
            
            if($rs['errno'] > 0) {
                $this->error_number  = $rs['errno'];
                $this->error_message = $rs['errmsg'];
        
            }
        }
        curl_close($ch);

        return $this->xmlr;
    }
}
