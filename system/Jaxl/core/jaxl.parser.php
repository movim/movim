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
 * @subpackage core
 * @author Abhinav Singh <me@abhinavsingh.com>
 * @copyright Abhinav Singh
 * @link http://code.google.com/p/jaxl
 */

    /**
     * Jaxl XML Parsing Framework
    */
    class JAXLXml {
        
        /**
         * Contains XPath Map for various XMPP stream and stanza's
         * http://tools.ietf.org/html/draft-ietf-xmpp-3920bis-18
         * 
        */ 
        protected static $tagMap = array(

            'starttls'      =>  array(
                'xmlns'     =>  '//starttls/@xmlns'
            ),
        
            'proceed'       =>  array(
                'xmlns'     =>  '//proceed/@xmlns'
            ),
        
            'challenge'     =>  array(
                'xmlns'     =>  '//challenge/@xmlns',
                'challenge' =>  '//challenge/text()'
            ),
        
            'success'       =>  array(
                'xmlns'     =>  '//success/@xmlns'
            ),
            
            'failure'       =>  array(
                'xmlns'     =>  '//failure/@xmlns',
                'condition' =>  '//failure/*[1]/name()',
                'desc'      =>  '//failure/text',
                'descLang'  =>  '//failure/text/@xml:lang'
            ),
            
            'message'       =>  array(
                'to'        =>  '//message/@to',
                'from'      =>  '//message/@from',
                'id'        =>  '//message/@id',
                'type'      =>  '//message/@type',
                'xml:lang'  =>  '//message/@xml:lang',
                'body'      =>  '//message/body',
                'subject'   =>  '//message/subject',
                'thread'    =>  '//message/thread',
                'xXmlns'    =>  '//presence/x/@xmlns',
                'errorType' =>  '//message/error/@type',
                'errorCode' =>  '//message/error/@code'
            ),
            
            'presence'      =>  array(
                'to'        =>  '//presence/@to',
                'from'      =>  '//presence/@from',
                'id'        =>  '//presence/@id',
                'type'      =>  '//presence/@type',
                'xml:lang'  =>  '//presence/@xml:lang',
                'show'      =>  '//presence/show',
                'status'    =>  '//presence/status',
                'priority'  =>  '//presence/priority',
                'xXmlns'    =>  '//presence/x/@xmlns',
                'errorType' =>  '//presence/error/@type',
                'errorCode' =>  '//presence/error/@code'
            ),
            
            'iq'            =>  array(
                'to'        =>  '//iq/@to',
                'from'      =>  '//iq/@from',
                'id'        =>  '//iq/@id',
                'type'      =>  '//iq/@type',
                'xml:lang'  =>  '//iq/@xml:lang',
                'bindResource'  =>  '//iq/bind/resource',
                'bindJid'   =>  '//iq/bind/jid',
                'queryXmlns'=>  '//iq/query/@xmlns',
                'queryVer'  =>  '//iq/query/@ver',
                'queryItemSub'  =>  '//iq/query/item/@subscription',
                'queryItemJid'  =>  '//iq/query/item/@jid',
                'queryItemName' =>  '//iq/query/item/@name',
                'queryItemAsk'  =>  '//iq/query/item/@ask',
                'queryItemGrp'  =>  '//iq/query/item/group',
                'errorType' =>  '//iq/error/@type',
                'errorCode' =>  '//iq/error/@code',
                'errorCondition'=>  '//iq/error/*[1]/name()',
                'errorXmlns'=>  '//iq/error/*[1]/@xmlns'
            )
            
        );

        /**
         * Parses passed $xml string and returns back parsed nodes as associative array
         * 
         * Method assumes passed $xml parameter to be a single xmpp packet.
         * This method only parses the node xpath mapping defined inside self::$tagMap
         * Optionally, custom $tagMap can also be passed for parsing custom nodes for passed $xml string
         *
         * @param string $xml XML string to be parsed
         * @param bool $sxe Whether method should return SimpleXMLElement object along with parsed nodes
         * @param array $tagMap Custom tag mapping to be applied for this parse
         *
         * @return array $payload An associative array of parsed xpaths as specified inside tagMap
        */
        public static function parse($xml, $sxe=false, &$tagMap=null) {
            $payload = array();
            
            $xml = str_replace('xmlns=', 'ns=', $xml);
            try { $xml = @new SimpleXMLElement($xml); }
            catch(Exception $e) { return false; }
            $node = $xml->getName();
            $parents = array();

            if(!$tagMap)
                $tagMap = &self::$tagMap[$node];
            
            foreach($tagMap as $tag=>$xpath) {
                $xpath = str_replace('/@xmlns', '/@ns', $xpath);
                $parentXPath = implode('/', explode('/', $xpath, -1));
                $tagXPath = str_replace($parentXPath.'/', '', $xpath);
                
                // save parent XPath in buffer
                if(!isset($parents[$parentXPath])) 
                    $parents[$parentXPath] = $xml->xpath($parentXPath);
                
                if(!is_array($parents[$parentXPath]))
                    continue;

                // loop through all the extracted parent nodes 
                foreach($parents[$parentXPath] as $key=>$obj) {
                    //echo PHP_EOL."starting loop for tag: ".$tag.", xpath: ".$xpath.", parentXPath: ".$parentXPath.", tagXPath: ".$tagXPath." ======>".PHP_EOL;
                    //print_r($obj);

                    if($tagXPath == 'name()') {
                        $values = $obj->getName();
                    }
                    else if($tagXPath == 'text()') {
                        $values = array('0'=>(string)$obj);
                    }
                    else if($tagXPath == 'xml()') {
                        $values = $obj->asXML();
                    }
                    else if(substr($tagXPath, 0, 1) == '@') {
                        $txpath = str_replace('@', '', $tagXPath);
                        $values = $obj->attributes();
                        $values = (array)$values[$txpath];
                        unset($txpath);
                    }
                    else {
                        $values = $obj->{$tagXPath};
                    }

                    if(is_array($values) && sizeof($values) > 1) {
                        $temp = array();
                        foreach($values as $value) $temp[] = (string)$value[0];
                        $payload[$node][$tag][] = $temp;
                        unset($temp);
                    }
                    else if($tagXPath == 'name()') {
                        $payload[$node][$tag] = $values;
                    }
                    else if($tagXPath == 'xml()') {
                        $payload[$node][$tag] = $values;
                    }
                    else {
                        if(sizeof($parents[$parentXPath]) == 1) $payload[$node][$tag] = isset($values[0]) ? (string)$values[0] : null;
                        else $payload[$node][$tag][] = isset($values[0]) ? (string)$values[0] : null;
                    }
                }
            }
            
            if($sxe) $payload['xml'] = $xml;
            unset($xml);
            return $payload;
        }
       
        /**
         * Add node xpath and corresponding tag mapping for parser
         *
         * @param string $node Node for which this tag map should be parsed
         * @param string $tag Tag associated with this xpath
         * @param string $map XPath to be extracted
        */
        public static function addTag($node, $tag, $map) {
            self::$tagMap[$node][$tag] = $map;
        }
        
        /**
         * Remove node xpath and corresponding tag mapping for parser
         * 
         * @param string $node
         * @param string $tag
        */
        public static function removeTag($node, $tag) {
            unset(self::$tagMap[$node][$tag]);
        }
        
        /**
         * Creates XML string from passed $tagMap values
         *
         * @param array $tagVals
         * @return string $xml
        */
        public static function create($tagVals) {
            foreach($tagVals as $node=>$tagVal) {
                // initialize new XML document
                $dom = new DOMDocument();
                $superRoot = $dom->createElement($node);
                $dom->appendChild($superRoot);
                
                $childs = array();
                // iterate over all tag values
                foreach($tagVal as $tag=>$value) {
                    // find xpath where this $tag and $value should go
                    $xpath = self::$tagMap[$node][$tag];
                    
                    // xpath parts for depth detection
                    $xpath = str_replace('//'.$node.'/', '', $xpath);
                    $xpart = explode('/', $xpath);
                    $depth = sizeof($xpart);
                    
                    $root = $superRoot;
                    for($currDepth=0; $currDepth<$depth; $currDepth++) {
                        $element = $xpart[$currDepth];
                        $isAttr = (substr($element, 0, 1) == '@') ? true : false;
                        
                        if($isAttr) {
                            $element = str_replace('@', '', $element);
                            $attr = $dom->createAttribute($element);
                            $root->appendChild($attr);
                            $text = $dom->createTextNode($value);
                            $attr->appendChild($text);
                        }
                        else {
                            if(!isset($childs[$currDepth][$element])) {
                                $child = $dom->createElement($element);
                                $root->appendChild($child);
                                $childs[$currDepth][$element] = true;
                            }
                            else if($currDepth == $depth-1) {
                                //echo ' value '.$value.PHP_EOL.PHP_EOL;
                                $text = $dom->createTextNode($value);
                                $child->appendChild($text);
                            }
                            $root = $child;
                        }
                    }
                }
                
                $xml = $dom->saveXML();
                unset($dom); unset($attr); unset($child); unset($text);
                return $xml;
            }
        }
        
    }

?>
