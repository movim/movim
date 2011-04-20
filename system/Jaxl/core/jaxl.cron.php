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
	 * Jaxl Cron Job
	 * 
     * Add periodic cron in your xmpp applications
	*/
	class JAXLCron {

		private static $cron = array();
        
		public static function init($jaxl) {
            $jaxl->addPlugin('jaxl_get_xml', array('JAXLCron', 'ticker'));
        }
        
        public static function ticker($payload, $jaxl) {
            foreach(self::$cron as $interval => $jobs) {
                foreach($jobs as $key => $job) {
                    if($jaxl->clock != 0
                    && $jaxl->clocked - $job['lastCall'] > $interval // if cron interval has already passed
                    ) {
                        self::$cron[$interval][$key]['lastCall'] = $jaxl->clocked;
                        $arg = $job['arg'];
                        array_unshift($arg, $jaxl);

                        $jaxl->log("[[JAXLCron]] Executing cron job\nInterval:$interval, Callback:".$job['callback'], 5);
                        call_user_func_array($job['callback'], $arg);
                    }
                }
            }
            return $payload;
        }
		
		public static function add(/* $callback, $interval, $param1, $param2, .. */) {
            $arg = func_get_args();
            $callback = array_shift($arg);
            $interval = array_shift($arg);
            self::$cron[$interval][self::generateCbSignature($callback)] = array('callback'=>$callback, 'arg'=>$arg, 'lastCall'=>time());
        }
		
		public static function delete($callback, $interval) {
            $sig = self::generateCbSignature($callback);
            if(isset(self::$cron[$interval][$sig]))
                unset(self::$cron[$interval][$sig]);
        }

        protected static function generateCbSignature($callback) {
            return is_array($callback) ? md5(json_encode($callback)) : md5($callback);
        }
		
	}

?>
