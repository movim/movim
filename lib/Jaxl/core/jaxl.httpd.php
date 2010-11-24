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
    
    // JAXLHTTPd server meta    
    define('JAXL_HTTPd_SERVER_NAME', 'JAXLHTTPd');
    define('JAXL_HTTPd_SERVER_VERSION', '0.0.1');

    // JAXLHTTPd settings
    define('JAXL_HTTPd_MAXQ', 20);
    define('JAXL_HTTPd_SELECT_TIMEOUT', 1);
    
    // Jaxl core dependency
    jaxl_require(array(
        'JAXLPlugin'
    ));
    
    // Simple socket select server
    class JAXLHTTPd { 
               
        // HTTP request/response code list
        public static $headers = array(
            100 => "100 Continue",
            200 => "200 OK",
            201 => "201 Created",
            204 => "204 No Content",
            206 => "206 Partial Content",
            300 => "300 Multiple Choices",
            301 => "301 Moved Permanently",
            302 => "302 Found",
            303 => "303 See Other",
            304 => "304 Not Modified",
            307 => "307 Temporary Redirect",
            400 => "400 Bad Request",
            401 => "401 Unauthorized",
            403 => "403 Forbidden",
            404 => "404 Not Found",
            405 => "405 Method Not Allowed",
            406 => "406 Not Acceptable",
            408 => "408 Request Timeout",
            410 => "410 Gone",
            413 => "413 Request Entity Too Large",
            414 => "414 Request URI Too Long",
            415 => "415 Unsupported Media Type",
            416 => "416 Requested Range Not Satisfiable",
            417 => "417 Expectation Failed",
            500 => "500 Internal Server Error",
            501 => "501 Method Not Implemented",
            503 => "503 Service Unavailable",
            506 => "506 Variant Also Negotiates"
        );
        
        // server instance
        private static $httpd = null;
        
        // server settings
        private static $settings = null;
        
        // connected socket id
        private static $id = null;
        
        // list of connected clients
        private static $clients = null;
       
        public static function shutdown() {
            JAXLPlugin::execute('jaxl_httpd_pre_shutdown');
            exit;
        }

        private static function reset($options) {
            self::$settings = array(
                'port'  =>  isset($options['port']) ? $options['port'] : 5290,
                'maxq'  =>  isset($options['maxq']) ? $options['maxq'] : 20,
                'pid'   =>  getmypid(),
                'since' =>  time()
            );
        }
       
        public static function start($options) {
            self::reset($options);

            pcntl_signal(SIGTERM, array("JAXLHTTPd", "shutdown"));
            pcntl_signal(SIGINT, array("JAXLHTTPd", "shutdown"));
            
            $options = getopt("p:b:");
            foreach($options as $opt=>$val) {
                switch($opt) {
                    case 'p':
                        self::$settings['port'] = $val;
                        break;
                    case 'b':
                        self::$settings['maxq'] = $val;
                    default:
                        break;
                }
            }
            
            self::$httpd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_set_option(self::$httpd, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_bind(self::$httpd, 0, self::$settings['port']);
            socket_listen(self::$httpd, self::$settings['maxq']);

            self::$id = self::getResourceID(self::$httpd);
            self::$clients = array("0#".self::$settings['port']=>self::$httpd);
            echo "JAXLHTTPd listening on port ".self::$settings['port'].PHP_EOL;
            
            while(true) {
                $read = self::$clients;
                $ns = @socket_select($read, $write=null, $except=null, JAXL_HTTPd_SELECT_TIMEOUT);
                
                if($ns) foreach($read as $read_socket) {
                    $accept_id = self::getResourceID($read_socket);
                    
                    if(self::$id == $accept_id) {
                        $sock = socket_accept($read_socket);
                        socket_getpeername($sock, $ip, $port);
                        self::$clients[$ip."#".$port] = $sock;
                        //echo "Accepted new connection from ".$ip."#".$port.PHP_EOL;
                        continue;
                    }
                    else {
                        socket_getpeername($read_socket, $ip, $port);
                        $data = trim(socket_read($read_socket, 1024));
                    
                        if($data == "") {
                            self::close($ip, $port);
                        }
                        else {
                            //echo "Recv data from ".$ip."#".$port.PHP_EOL;
                            $request = self::parseRequest($data, array(
                                'ip'    =>  $ip,
                                'port'  =>  $port
                            ));
                            
                            if($request['meta']['protocol'] == 'HTTP') {
                                JAXLPlugin::execute('jaxl_httpd_get_http_request', $request);
                            }
                            else {
                                JAXLPlugin::execute('jaxl_httpd_get_sock_request', $request);
                            }
                        }
                    }
                }
                
                JAXLPlugin::execute('jaxl_httpd_post_read');
            }
        }
        
        public static function send($response) {
            $raw = self::prepareResponse($response['meta'], $response['header']);
            @socket_write(self::$clients[$response['client']['ip']."#".$response['client']['port']], $raw);
        }
        
        public static function close($ip, $port) {
            @socket_close(self::$clients[$ip."#".$port]);
            unset(self::$clients[$ip."#".$port]);
        }
        
        private static function parseRequest($raw, $client) {
            list($meta, $headers) = self::parseHeader($raw);
            $request = array(
                'meta'  =>  $meta,
                'header'=>  $headers,
                'client'=>  $client
            ); 
            return $request;
        }
        
        private static function parseHeader($raw) {
            $raw = explode("\r\n", $raw);
            list($method, $path, $protocol) = explode(" ", array_shift($raw));
            list($protocol, $version) = explode("/", $protocol); 
            $meta = array(
                'method'=>trim($method),
                'path'=>trim($path),
                'protocol'=>trim($protocol),
                'version'=>trim($version)
            );
           
            $headers = array(); 
            foreach($raw as $header) {
                $header = trim($header);
                if($header == "") {
                    break;
                }
                else if(strpos($header, ":") != false) {
                    $key = strtoupper(strtok($header, ":"));
                    $val = trim(strtok(""));
                    $headers[$key] = $val;
                }
            }
            
            $meta['body'] = substr(array_pop($raw), 0, $headers['CONTENT-LENGTH']);
            return array($meta, $headers);
        }

        private static function prepareResponse($meta, $headers) {
            $raw = '';
            $raw .= $meta['protocol']."/".$meta['version']." ".self::$headers[$meta['code']]."\r\n";
            $raw .= "Server: ".JAXL_HTTPd_SERVER_NAME."/".JAXL_HTTPd_SERVER_VERSION."\r\n";
            $raw .= "Date: ".gmdate("D, d M Y H:i:s T")."\r\n";
            foreach($headers as $key => $val) $raw .= $key.": ".$val."\r\n";
            $raw .= "\r\n";
            $raw .= $meta['body'];
            return $raw;
        }
        
        private static function getResourceID($socket) {
            return (int)preg_replace("/Resource id #(\d+)/i", "$1", (string)$socket);
        }

    }
    
?>
