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
     * XEP-0080: User Location
    */
    class JAXL0080 {
        
        public static $ns = 'http://jabber.org/protocol/geoloc';

        public static function init($jaxl) {
            // requires PEP XEP
            $jaxl->requires('JAXL0163');

            // update client feature list
            $jaxl->features[] = self::$ns;

            JAXLXml::addTag('message', 'geoloc', '//message/event/items/item/geoloc/@xmlns');
            JAXLXml::addTag('message', 'geolocLang', '//message/event/items/item/geoloc/@xml:lang');
            JAXLXml::addTag('message', 'geolocAccuracy', '//message/event/items/item/geoloc/accuracy');
            JAXLXml::addTag('message', 'geolocAlt', '//message/event/items/item/geoloc/alt');
            JAXLXml::addTag('message', 'geolocBearing', '//message/event/items/item/geoloc/bearing');
            JAXLXml::addTag('message', 'geolocBuilding', '//message/event/items/item/geoloc/building');
            JAXLXml::addTag('message', 'geolocCountry', '//message/event/items/item/geoloc/country');
            JAXLXml::addTag('message', 'geolocCountryCode', '//message/event/items/item/geoloc/countrycode');
            JAXLXml::addTag('message', 'geolocDatum', '//message/event/items/item/geoloc/datum');
            JAXLXml::addTag('message', 'geolocDescription', '//message/event/items/item/geoloc/description');
            JAXLXml::addTag('message', 'geolocError', '//message/event/items/item/geoloc/error');
            JAXLXml::addTag('message', 'geolocFloor', '//message/event/items/item/geoloc/floor');
            JAXLXml::addTag('message', 'geolocLat', '//message/event/items/item/geoloc/lat');
            JAXLXml::addTag('message', 'geolocLocality', '//message/event/items/item/geoloc/locality');
            JAXLXml::addTag('message', 'geolocLon', '//message/event/items/item/geoloc/lon');
            JAXLXml::addTag('message', 'geolocPostalCode', '//message/event/items/item/geoloc/postalcode');
            JAXLXml::addTag('message', 'geolocRegion', '//message/event/items/item/geoloc/region');
            JAXLXml::addTag('message', 'geolocRoom', '//message/event/items/item/geoloc/room');
            JAXLXml::addTag('message', 'geolocSpeed', '//message/event/items/item/geoloc/speed');
            JAXLXml::addTag('message', 'geolocStreet', '//message/event/items/item/geoloc/street');
            JAXLXml::addTag('message', 'geolocText', '//message/event/items/item/geoloc/text');
            JAXLXml::addTag('message', 'geolocTimestamp', '//message/event/items/item/geoloc/timestamp');
            JAXLXml::addTag('message', 'geolocURI', '//message/event/items/item/geoloc/uri');
        }

        public static function publishLocation($jaxl, $from, $item) {
            return JAXL0163::publishItem($jaxl, $from, self::$ns, $item);
        }
        
    }

?>
