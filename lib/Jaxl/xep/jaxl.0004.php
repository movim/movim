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
     * XEP-0004 : Data Forms
    */
    class JAXL0004 {

        public static $ns = 'jabber:x:data';

        public static function init($jaxl) {
            $jaxl->features[] = self::$ns;
        }

        /*
         * create XEP-0004 complaint data form using $fields
        */
        public static function setFormField($fields, $title=false, $inst=false, $type='form') {
            $payload = '';
            
            $payload .= '<x xmlns="'.self::$ns.'" type="'.$type.'">';
            if($title) $payload .= '<title>'.$title.'</title>';
            if($inst) $payload .= '<instruction>'.$inst.'</instruction>';
            foreach($fields as $field) {
                $payload .= '<field var="'.$field['var'].'">';
                $payload .= '<value>'.$field['value'].'</value>';
                $payload .= '</field>';
            }
            $payload .= '</x>';
            
            return $payload;
        }
        
        /*
         * Parses incoming form $fields
        */
        public static function getFormField($fields) {
            $result = array();
            foreach($fields as $field) {
                $f = array();
            
                $f['type'] = $field['@']['type'];
                $f['label'] = $field['@']['label'];
                $f['var'] = $field['@']['var'];
                
                $f['desc'] = $field['#']['desc'][0]['#'];
                $f['required'] = $field['#']['required'][0]['#'];
                $f['value'] = $field['#']['value'][0]['#'];
                                
                if(is_array($field['#']['option'])) { 
                    $f['option'] = array();
                    foreach($field['#']['option'] as $option) {
                        $f['option'][] = array('label'=>$option['@']['label'], 'value'=>$option['#']['value'][0]['#']);
                    }
                }
                
                $result[] = $f;
            }
            return $result;
        }

    }

?>
