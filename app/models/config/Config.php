<?php

namespace Modl;

class Config extends Model {    
    public $environment;
    public $description;
    public $theme;
    public $locale;
    public $maxusers;
    public $loglevel;
    public $timezone;
    public $boshurl;
    public $xmppwhitelist;
    public $info;
    public $unregister;
    public $username;
    public $password;
    public $sizelimit;
    
    public function __construct() {
        $this->_struct = '
        {
            "environment" : 
                {"type":"string", "size":64, "mandatory":true },
            "description" : 
                {"type":"text" },
            "theme" : 
                {"type":"string", "size":32, "mandatory":true },
            "locale" : 
                {"type":"string", "size":8, "mandatory":true  },
            "maxusers" : 
                {"type":"int", "size":16 },
            "loglevel" : 
                {"type":"string", "size":16, "mandatory":true  },
            "timezone" : 
                {"type":"string", "size":16, "mandatory":true  },
            "boshurl" : 
                {"type":"string", "size":128, "mandatory":true  },
            "xmppwhitelist" : 
                {"type":"text" },
            "info" : 
                {"type":"text" },
            "unregister" : 
                {"type":"int", "size":1 },
            "username" : 
                {"type":"string", "size":32, "mandatory":true },
            "password" : 
                {"type":"string", "size":64, "mandatory":true  },
            "sizelimit" : 
                {"type":"int", "size":16 }
        }';
        
        parent::__construct();

        $this->environment      = 'development';
        $this->description      = __('global.description');
        $this->theme            = 'movim';
        $this->locale           = 'en';
        $this->maxusers         = -1;
        $this->loglevel         = 'empty';
        $this->timezone         = 'Etc/GMT';
        $this->boshurl          = 'http://localhost:5280/http-bind';
        $this->xmppwhitelist    = '';
        $this->info             = '';
        $this->unregister       = false;
        $this->username         = 'admin';
        $this->password         = sha1('password');
        $this->sizelimit        = 20240001;
    }
}
