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
     * Jaxl Plugin Framework
    */ 
    class JAXLPlugin {
       
        /**
         * Registry of all registered hooks
        */
        public static $registry = array();
       
        /**
         * Register callback on hook
         *
         * @param string $hook
         * @param string|array $callback A valid callback inside your application code
         * @param integer $priority (>0) When more than one callbacks is attached on hook, they are called in priority order or which ever was registered first
         * @param integer $uid random id $jaxl->uid of connected Jaxl instance, $uid=0 means callback registered for all connected instances
        */
        public static function add($hook, $callback, $priority=10, $uid=0) {
            if(!isset(self::$registry[$uid])) 
                self::$registry[$uid] = array();

            if(!isset(self::$registry[$uid][$hook]))
                self::$registry[$uid][$hook] = array();
            
            if(!isset(self::$registry[$uid][$hook][$priority])) 
                self::$registry[$uid][$hook][$priority] = array();
            
            array_push(self::$registry[$uid][$hook][$priority], $callback);
        }
       
        /**
         * Removes a previously registered callback on hook
         *
         * @param string $hook
         * @param string|array $callback
         * @param integer $priority
         * @param integer $uid random id $jaxl->uid of connected Jaxl instance, $uid=0 means callback registered for all connected instances
        */
        public static function remove($hook, $callback, $priority=10, $uid=0) {
            if(($key = array_search($callback, self::$registry[$uid][$hook][$priority])) !== FALSE)
                unset(self::$registry[$uid][$hook][$priority][$key]);

            if(count(self::$registry[$uid][$hook][$priority]) == 0)
                unset(self::$registry[$uid][$hook][$priority]);
            
            if(count(self::$registry[$uid][$hook]) == 0)
                unset(self::$registry[$uid][$hook]);
        }
        
        /*
         * Method calls previously registered callbacks on executing hook
         * 
         * @param string $hook
         * @param mixed $payload
         * @param object $jaxl
         * @param array $filter
        */
        public static function execute($hook, $payload=null, $jaxl=false, $filter=false) {
            if($jaxl) $uids = array($jaxl->uid, 0);
            else $uids = array(0);
            foreach($uids as $uid) {
                if(isset(self::$registry[$uid][$hook]) && count(self::$registry[$uid][$hook]) > 0) {
                    foreach(self::$registry[$uid][$hook] as $priority) {
                        foreach($priority as $callback) {
                            if($filter === false || (is_array($filter) && in_array($callback[0], $filter))) {
                                if($jaxl) $jaxl->log("[[JAXLPlugin]] Executing hook $hook for uid $uid", 7);
                                $payload = call_user_func($callback, $payload, $jaxl);
                            }
                        }
                    }
                }
            }
            return $payload;
        }
        
    }
    
?>
