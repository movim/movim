<?php

namespace modl;

class Sessionx extends ModlModel {
    public $session;
    public $user;
    public $ressource;
    public $rid;
    public $sid;
    public $id;
    public $url;
    public $port;
    public $host;
    public $domain;
    public $config;
    public $active;
    public $timestamp;
    
    public function __construct() {
        $this->_struct = '
        {
            "session" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "user" : 
                {"type":"string", "size":64 },
            "ressource" : 
                {"type":"string", "size":64 },
            "rid" : 
                {"type":"int",    "size":8,   "mandatory":true },
            "sid" : 
                {"type":"int",    "size":8 },
            "id" : 
                {"type":"int",    "size":8,   "mandatory":true },
            "url" : 
                {"type":"string", "size":128, "mandatory":true },
            "port" : 
                {"type":"int",    "size":5,   "mandatory":true },
            "host" : 
                {"type":"string", "size":64,  "mandatory":true },
            "domain" : 
                {"type":"string", "size":64,  "mandatory":true },
            "config" : 
                {"type":"text" },
            "active" : 
                {"type":"int",    "size":4 },
            "timestamp" : 
                {"type":"date" }
        }';
        
        parent::__construct();
    }
}
/*            $session = array(
                    'rid' => 1,
                    'sid' => 0,
                    'id'  => 0,
                    'url' => $serverconfig['boshUrl'],
                    'port'=> 5222,
                    'host'=> $host,
                    'domain' => $domain,
                    'ressource' => 'moxl'.substr(md5(date('c')), 3, 6),

                    'user'     => $user,
                    'password' => $element['pass'],

                    'proxyenabled' => $serverconfig['proxyEnabled'],
                    'proxyurl' => $serverconfig['proxyURL'],
                    'proxyport' => $serverconfig['proxyPort'],
                    'proxyuser' => $serverconfig['proxyUser'],
                    'proxypass' => $serverconfig['proxyPass']);*/
