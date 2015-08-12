<?php

namespace modl;

class Privacy extends Model {    
    public $pkey;
    public $value;
    protected $hash;
    
    public function __construct() {
        $this->_struct = '
        {
            "pkey" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "value" : 
                {"type":"int",    "size":4,   "mandatory":true },
            "hash" : 
                {"type":"string", "size":128, "mandatory":true }
        }';
        
        parent::__construct();
    }

    static function set($key, $value) {
        $p = new Privacy();
        $p->pkey  = $key;
        $p->value = $value;
        $p->hash  = md5(openssl_random_pseudo_bytes(5));
        
        $pd = new PrivacyDAO();
        $pd->set($p);
    }

    static function get($key) {
        $pd = new PrivacyDAO();
        return (int)$pd->get($key)->value;
    }
}
