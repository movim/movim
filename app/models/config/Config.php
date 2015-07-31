<?php

namespace Modl;

class Config extends Model {
    public $description;
    public $theme;
    public $locale;
    public $maxusers;
    public $loglevel;
    public $timezone;
    public $xmppwhitelist;
    public $info;
    public $unregister;
    public $username;
    public $password;
    public $rewrite;
    public $sizelimit;

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
                {"type":"string", "size":16, "mandatory":true  },
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
            "rewrite" :
                {"type":"int", "size":1 },
            "sizelimit" :
                {"type":"int", "size":16 }
        }';

        parent::__construct();

        $this->description      = __('global.description');
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
        $this->rewrite          = false;
        $this->sizelimit        = 20240001;
    }
}
