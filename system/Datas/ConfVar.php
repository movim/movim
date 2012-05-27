<?php

class ConfVar extends DatajarBase {
    protected $login;
    protected $pass;

    protected $host;
    protected $domain;
    protected $port;

    protected $boshHost;
    protected $boshSuffix;
    protected $boshPort;

    protected $language;

    protected $first;

    protected function type_init() {
        $this->login      = DatajarType::varchar(128);
        $this->pass       = DatajarType::varchar(128);

        $this->host       = DatajarType::varchar(128);
        $this->domain     = DatajarType::varchar(128);
        $this->port       = DatajarType::int();

        $this->boshHost   = DatajarType::varchar(128);
        $this->boshSuffix = DatajarType::varchar(128);
        $this->boshPort   = DatajarType::int();

        $this->language   = DatajarType::varchar(128);

        $this->first      = DatajarType::int();
    }
    
    public function get($element = false) {
        $conf = array();
        $arr = get_object_vars($this);
        
        foreach($arr as $key => $value) {
            if(method_exists($value, 'getval'))
                $conf[$key] = $value->getval();
        }
        
        if($element == false)
            return $conf;
        else
            return $conf[$element];
    }
    
    public function set($att, $val) {
        $arr = get_object_vars($this);
        
        if(array_key_exists($att, $arr))
            $this->$att->setval($val);
        else
            Logger::log(3, 'ConfVar::set() set a value on a non existent attribute');

        return $this;
    }

}

class UserConf {
    static function getConf($jid = false, $element = false) {
        $sess = Session::start(APP_NAME);
        
        if($jid)
            $login = $jid;
        elseif($sess->get('login') != '')
            $login = $sess->get('login');
        else
            Logger::log(3, 'UserConf::getConf() on an unset Session');

        $query = ConfVar::query()
            ->where(array('login' => $login));
            
        $conf = ConfVar::run_query($query);

        if($conf != false) {
			$arr = $conf[0]->get();
			
			if($element != false)
				return $arr[$element];
			else
				return $arr;
		} else {
			return false;
		}
    }
    
}
