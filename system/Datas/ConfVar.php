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

    public function setConf(
                            $login = false,
                            $pass = false,
                            $host = false,
                            $domain = false,
                            $port = false,
                            $boshhost = false,
                            $boshsuffix = false,
                            $boshport = false,
                            $language = false,
                            $first = false
                           ) {

        list($user, $host) = explode('@', $login);

        $this->login->setval(($login != false) ? $login : $this->login->getval());
        $this->pass->setval(($pass != false) ? sha1($pass) : $this->pass->getval());

        $this->host->setval(($host != false) ? $host : $this->host->getval());
        $this->domain->setval(($host != false) ? $host : $this->domain->getval());
        $this->port->setval(5222);

        $this->boshHost->setval(($boshhost != false) ? $boshhost : $this->boshHost->getval());
        $this->boshSuffix->setval(($boshsuffix != false) ? $boshsuffix : $this->boshSuffix->getval());
        $this->boshPort->setval(($boshport != false) ? $boshport : $this->boshPort->getval());

        $this->language->setval(($language != false) ? $language : $this->language->getval());

        if($first) $this->first->setval(1);

    }

    public function getConf() {
        $array = array();
        $array['login'] = $this->login->getval();
        $array['pass'] = $this->pass->getval();

        $array['host'] = $this->host->getval();
        $array['domain'] = $this->domain->getval();
        $array['port'] = $this->port->getval();

        $array['boshHost'] = $this->boshHost->getval();
        $array['boshSuffix'] = $this->boshSuffix->getval();
        $array['boshPort'] = $this->boshPort->getval();

        $array['language'] = $this->language->getval();

        $array['first'] = $this->first->getval();

        return $array;
    }

}
