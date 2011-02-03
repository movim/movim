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
     * XEP-0054 : vcard-temp
    */
	class JAXL0054 {
		
		public static $ns = 'vcard-temp';
		
		public static function init($jaxl) {
            $jaxl->features[] = self::$ns;

			JAXLXml::addTag('iq', 'vCard', '//iq/vCard/@xmlns');
			JAXLXml::addTag('iq', 'vCardFN', '//iq/vCard/FN');
			JAXLXml::addTag('iq', 'vCardNFamily', '//iq/vCard/N/FAMILY');
			JAXLXml::addTag('iq', 'vCardNGiven', '//iq/vCard/N/GIVEN');
			JAXLXml::addTag('iq', 'vCardNMiddle', '//iq/vCard/N/MIDDLE');
			JAXLXml::addTag('iq', 'vCardNickname', '//iq/vCard/NICKNAME');
			JAXLXml::addTag('iq', 'vCardPhotoType', '//iq/vCard/PHOTO/TYPE');
			JAXLXml::addTag('iq', 'vCardPhotoBinVal', '//iq/vCard/PHOTO/BINVAL');
			JAXLXml::addTag('iq', 'vCardUrl', '//iq/vCard/URL');
			JAXLXml::addTag('iq', 'vCardBDay', '//iq/vCard/BDAY');
			JAXLXml::addTag('iq', 'vCardOrgName', '//iq/vCard/ORGNAME');
			JAXLXml::addTag('iq', 'vCardOrgUnit', '//iq/vCard/ORGUNIT');
			JAXLXml::addTag('iq', 'vCardTitle', '//iq/vCard/TITLE');
			JAXLXml::addTag('iq', 'vCardRole', '//iq/vCard/ROLE');
			JAXLXml::addTag('iq', 'vCardTelWork', '//iq/vCard/TEL/WORK');
			JAXLXml::addTag('iq', 'vCardTelVoice', '//iq/vCard/TEL/VOICE');
			JAXLXml::addTag('iq', 'vCardTelFax', '//iq/vCard/TEL/FAX');
			JAXLXml::addTag('iq', 'vCardTelMsg', '//iq/vCard/TEL/MSG');
			JAXLXml::addTag('iq', 'vCardAdrWork', '//iq/vCard/ADR/WORK');
			JAXLXml::addTag('iq', 'vCardAdrExtadd', '//iq/vCard/ADR/EXTADD');
			JAXLXml::addTag('iq', 'vCardAdrStreet', '//iq/vCard/ADR/STREET');
			JAXLXml::addTag('iq', 'vCardAdrLocality', '//iq/vCard/ADR/LOCALITY');
			JAXLXml::addTag('iq', 'vCardAdrRegion', '//iq/vCard/ADR/REGION');
			JAXLXml::addTag('iq', 'vCardAdrPcode', '//iq/vCard/ADR/PCODE');
			JAXLXml::addTag('iq', 'vCardAdrCtry', '//iq/vCard/ADR/CTRY');
			JAXLXml::addTag('iq', 'vCardDesc', '//iq/vCard/DESC');
		}

		public static function getVCard($jaxl, $to, $from, $callback) {
            $payload = '<vCard xmlns="'.self::$ns.'"/>';
            return XMPPSend::iq($jaxl, 'get', $payload, $to, $from, $callback);
		}
		
		public static function updateVCard($jaxl, $to, $from) {
			
		}
		
		
	}
	
?>
