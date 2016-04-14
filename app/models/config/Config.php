<?php

namespace Modl;

class Config extends Model {
    public $description;
    public $theme;
    public $locale;
    public $maxusers;
    public $loglevel;
    public $timezone;
    public $info;
    public $unregister;
    public $username;
    public $password;
    public $sizelimit;

    public $xmppdomain;
    public $xmppdescription;
    public $xmppcountry;
    public $xmppwhitelist;

    public function __construct() {
        $this->_struct = '
        {
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
                {"type":"string", "size":32, "mandatory":true  },
            "info" :
                {"type":"text" },
            "unregister" :
                {"type":"int", "size":1 },
            "username" :
                {"type":"string", "size":32, "mandatory":true },
            "password" :
                {"type":"string", "size":64, "mandatory":true  },
            "sizelimit" :
                {"type":"int", "size":16 },
            "xmppdomain" :
                {"type":"string", "size":32  },
            "xmppdescription" :
                {"type":"text" },
            "xmppcountry" :
                {"type":"string", "size":4  },
            "xmppwhitelist" :
                {"type":"text" }
        }';

        parent::__construct();

        $this->description      = 'Description';//__('global.description');
        $this->theme            = 'material';
        $this->locale           = 'en';
        $this->maxusers         = -1;
        $this->loglevel         = 'empty';
        $this->timezone         = 'Etc/GMT';
        $this->xmppwhitelist    = '';
        $this->info             = '';
        $this->unregister       = false;
        $this->username         = 'admin';
        $this->password         = sha1('password');
        $this->sizelimit        = 20240001;
    }
}
