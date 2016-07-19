<?php

class User {
    public  $username = '';
    private $config = [];

    public $caps;

    public $userdir;
    public $useruri;

    public $sizelimit;

    /**
     * Class constructor. Reloads the user's session or attempts to authenticate
     * the user.
     */
    function __construct($username = false)
    {
        if($username) {
            $this->username = $username;
        }

        $session = \Sessionx::start();
        if($session->active && $this->username == null) {
            $this->username = $session->user.'@'.$session->host;
        }

        if($this->username != null) {
            $this->userdir = DOCUMENT_ROOT.'/users/'.$this->username.'/';
            $this->useruri = BASE_URI.'users/'.$this->username.'/';
        }
    }

    /**
     * @brief Reload the user configuration
     */
    function reload()
    {
        $session = \Sessionx::start();
        if($session->config) {
            $this->config = $session->config;
            $lang = $this->getConfig('language');
            if(isset($lang)) {
                $l = Movim\i18n\Locale::start();
                $l->load($lang);
            }

            $cd = new modl\CapsDAO;
            $caps = $cd->get($session->host);
            $this->caps = unserialize($caps->features);
        }
    }

    /**
     * Checks if the user has an open session.
     */
    function isLogged()
    {
        // We check if the session exists in the daemon
        $session = \Sessionx::start();
        return (bool)requestURL('http://localhost:1560/exists/', 2, ['sid' => $session->sessionid]);
    }

    function createDir()
    {
        if(!is_dir($this->userdir)
        && $this->userdir != '') {
            mkdir($this->userdir);
            touch($this->userdir.'index.html');
        }
    }

    function getLogin()
    {
        return $this->username;
    }

    function getServer()
    {
        $exp = explodeJid($this->username);
        return $exp['server'];
    }

    function getUser()
    {
        $exp = explodeJid($this->username);
        return $exp['username'];
    }

    function setConfig(array $config)
    {
        $session = \Sessionx::start();
        $session->config = $config;

        file_put_contents($this->userdir.'config.dump', serialize($config));

        $this->reload();
    }

    function getConfig($key = false)
    {
        if($key == false)
            return $this->config;
        if(isset($this->config[$key]))
            return $this->config[$key];
    }

    function getDumpedConfig($key = false)
    {
        $config = unserialize(file_get_contents($this->userdir.'config.dump'));

        if($key == false)
            return $config;
        if(isset($config[$key]))
            return $config[$key];
    }

    function isSupported($key)
    {
        $this->reload();

        if($this->caps != null) {
            switch($key) {
                case 'pubsub':
                    return in_array('http://jabber.org/protocol/pubsub#publish', $this->caps);
                    break;
                case 'upload':
                    $id = new \Modl\ItemDAO;
                    return ($id->getUpload($this->getServer()) != null);
                    break;
                default:
                    return false;
                    break;
            }
        } elseif($key == 'anonymous') {
            $session = \Sessionx::start();
            return ($session->mechanism == 'ANONYMOUS');
        } else {
            return false;
        }
    }
}
