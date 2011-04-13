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
     * XEP-0118 : User Tune
    */
    class JAXL0118 {

        public static $ns = 'http://jabber.org/protocol/tune';

        public static function init($jaxl) {
            // requires PEP XEP
            $jaxl->requires('JAXL0163');

            // update client feature list
            $jaxl->features[] = self::$ns;

            JAXLXml::addTag('message', 'tune', '//message/event/items/item/tune/@xmlns');
            JAXLXml::addTag('message', 'tuneArtist', '//message/event/items/item/tune/artist');
            JAXLXml::addTag('message', 'tuneLength', '//message/event/items/item/tune/length');
            JAXLXml::addTag('message', 'tuneRating', '//message/event/items/item/tune/rating');
            JAXLXml::addTag('message', 'tuneSource', '//message/event/items/item/tune/source');
            JAXLXml::addTag('message', 'tuneTitle', '//message/event/items/item/tune/title');
            JAXLXml::addTag('message', 'tuneTrack', '//message/event/items/item/tune/track');
            JAXLXml::addTag('message', 'tuneURI', '//message/event/items/item/tune/uri');
        }

        public static function publishTune($jaxl, $from, $item) {
            return JAXL0163::publishItem($jaxl, $from, self::$ns, $item);
        }

    }

?>
