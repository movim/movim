<?php
/*
 * @file Handler.php
 * 
 * @brief Handle incoming XMPP request and dispatch them to the correct 
 * XECElement
 * 
 * Copyright 2012 edhelas <edhelas@edhelas-laptop>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */

namespace Moxl\Xec;

use Moxl\Utils;

class Handler {

    /**
     * Constructor of class Handler.
     *
     * @return void
     */
    static public function handle($array)
    {
        $_instances = 'empty';

        $user = new \User();
        
        $db = \Modl\Modl::getInstance();
        $db->setUser($user->getLogin());
        
        foreach($array as $child) {
            $id = '';
            $element = '';
            
            // Id verification in the returned stanza
            if($child->getName() == 'iq') {
                $id = (string)$child->attributes()->id;
                $element = 'iq';
            }

            if($child->getName() == 'presence') {
                $id = (string)$child->attributes()->id;
                $element = 'presence';
            }

            if($child->getName() == 'message') {
                $id = (string)$child->attributes()->id;
                $element = 'message';
            }

            if($id != '' && $_instances == 'empty') {
                // We get the cached instances
                $sess = \Session::start(APP_NAME);
                $_instances = $sess->get('xecinstances');
            }

            if(
                $id != '' && 
                $_instances != false && 
                array_key_exists($id, $_instances)
              ) {
                // We search an existent instance
                if(!array_key_exists($id, $_instances))
                    Utils::log('Handler : Memory instance not found');
                else {
                    $instance = $_instances[$id];
                    
                    $action = unserialize($instance['object']);
        
                    $error = false;
                    
                    // Handle specific query error
                    if($child->query->error)
                        $error = $child->query->error;
                    elseif($child->error)
                        $error = $child->error;
        
                    // XMPP returned an error
                    if($error) {
                        $errors = $error->children();

                        $errorid = Handler::formatError($errors->getName());

                        $message = false;

                        if($error->text)
                            $message = (string)$error->text;

                        Utils::log('Handler : '.$id.' - '.$errorid);

                        /* If the action has defined a special handler
                         * for this error
                         */
                        if(method_exists($action, $errorid)) {
                            $action->method($errorid);
                            $action->$errorid($errorid, $message);
                        }
                        // We also call a global error handler
                        elseif(method_exists($action, 'error'))
                            $action->error($errorid, $message);
                    } else {
                        // We launch the object handle
                        $action->method('handle');
                        $action->handle($child);
                    }
                    // We clean the object from the cache
                    unset($_instances[$id]);
                    
                    $sess->set('xecinstances', $_instances);
                }
            } else {                                
                Utils::log('Handler : Not an XMPP ACK');

                Handler::handleNode($child);
                
                foreach($child->children() as $s1) {
                    Handler::handleNode($s1, $child);  
                    foreach($s1->children() as $s2) 
                        Handler::handleNode($s2, $child);  
                }
            }
        }
    }
    
    static public function handleNode($s, $sparent = false) {
        $name = $s->getName();
        $ns = $s->getNamespaces();

		$node = false;
		
		if($s->items && $s->items->attributes()->node)
			$node = (string)$s->items->attributes()->node;
        
        if(is_array($ns))
            $ns = current($ns);
 
        if($node != false) {
            $hash = md5($name.$ns.$node);
            Utils::log('Handler : Searching a payload for "'.$name . ':' . $ns . ' [' . $node . ']", "'.$hash.'"'); 
            Handler::searchPayload($hash, $s, $sparent);
        } else {      
			$hash = md5($name.$ns);
			Utils::log('Handler : Searching a payload for "'.$name . ':' . $ns . ' ", "'.$hash.'"'); 
			$more = Handler::searchPayload($hash, $s, $sparent);
		}

    }

    static function getHashToClass() {
        return array(
            '9b98cd868d07fb7f6d6cb39dad31f10e' => 'Message',
            'e83b2aea042b74b1bec00b7d1bba2405' => 'Presence',
            'a0e8e987b067b6b0470606f4f90d5362' => 'Roster',
            
            '89d8bb4741fd3a62e8b20e0d52a85a36' => 'MucUser',
            
            '039538ac1c9488f4a612b89c48a35e32' => 'Post',
            
            '4c9681f0e9aca8a5b65f86b8b80d490f' => 'DiscoInfo',
            '482069658b024085fbc4e311fb771fa6' => 'DiscoInfo',
            
            '37ff18f136d5826c4426af5a23729e48' => 'Mood',
            '6b38ed328fb77617c6e4a5ac9dda0ad2' => 'Tune',
            '0981a46bbfa88b3500c4bccda18ccb89' => 'Location',
            '9c8ed44d4528a66484b0fbd44b0a9070' => 'Nickname',
            
            'd8ea912a151202700bb399c9e04d205f' => 'Caps',
            
            'cb52f989717d25441018703ea1bc9819' => 'Attention',

            '54c22c37d17c78ee657ea3d40547a970' => 'Version',
            
            '1cb493832467273efa384bbffa6dc35a' => 'Avatar',
            '36fe2745bdc72b1682be2c008d547e3d' => 'Vcard4',
            
            'd84d4b89d43e88a244197ccf499de8d8' => 'Jingle',

            '09ef1b34cf40fdd954f10d6e5075ee5c' => 'Carbons',
            '201fa54dd93e3403611830213f5f9fbc' => 'Carbons'
        );

    }
    
    static public function searchPayload($hash, $s, $sparent = false) {       
        $base = __DIR__.'/';
        
        $hashToClass = self::getHashToClass();
        
        if(isset($hashToClass[$hash])) {
            if(file_exists($base.'Payload/'.$hashToClass[$hash].'.php')) {
                require_once($base.'Payload/'.$hashToClass[$hash].'.php');
                $classname = '\\Moxl\\Xec\\Payload\\'.$hashToClass[$hash];
                
                if(class_exists($classname)) {
                    $payload_class = new $classname();
                    $payload_class->prepare($s, $sparent);
                    $payload_class->handle($s, $sparent);
                } else {
                   Utils::log('Handler : Payload class "'.$hashToClass[$hash].'" not found'); 
                }
            } else {
                Utils::log('Handler : Payload file "'.$hashToClass[$hash].'" not found');
            }
        } else {
            Utils::log('Handler : This event is not listed');
            return true;
        }
    }
    
    static public function handleError($number, $message) {
        $payload_class = new Payload\RequestError();
        $payload_class->handle($number, $message);
    }

    /* A simple function to format a error-string-text to a
     * camelTypeText 
     */
    static public function formatError($string) {

        $words = explode('-', $string);
        $f = 'error';
        foreach($words as $word)
            $f .= ucfirst($word);

        return $f;
    }

}
