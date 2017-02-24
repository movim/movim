<?php

class User
{
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
        $s = \Session::start();
        if($username) {
            $s->set('username', $username);
        }

        if($s->get('jid')) {
            $this->userdir = DOCUMENT_ROOT.'/users/'.$s->get('jid').'/';
            $this->useruri = BASE_URI.'users/'.$s->get('jid').'/';
        }
    }

    /**
     * @brief Reload the user configuration
     */
    function reload($language = false)
    {
        $sd = new \Modl\SessionxDAO;
        $session = $sd->get(SESSION_ID);

        if($session && $session->config) {
            if($language) {
                $this->config = $session->config;
                $lang = $this->getConfig('language');
                if(isset($lang)) {
                    $l = Movim\i18n\Locale::start();
                    $l->load($lang);
                }
            }

            $cd = new \Modl\CapsDAO;
            $caps = $cd->get($session->host);
            $this->caps = $caps->features;
        }
    }

    /**
     * Checks if the user has an open session.
     */
    function isLogged()
    {
        return (bool)requestURL('http://localhost:1560/exists/', 2, ['sid' => SESSION_ID]);
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
        $s = \Session::start();
        return $s->get('jid');
    }

    function getServer()
    {
        $s = \Session::start();
        return $s->get('host');
    }

    function getUser()
    {
        $s = \Session::start();
        return $s->get('username');
    }

    function setConfig(array $config)
    {
        $sd = new \Modl\SessionxDAO;
        $session = $sd->get(SESSION_ID);
        $session->config = $config;
        $sd->set($session);

        $this->createDir();

        file_put_contents($this->userdir.'config.dump', serialize($config));

        $this->reload(true);
    }

    function getConfig($key = false)
    {
        if($key == false) {
            return $this->config;
        } if(isset($this->config[$key])) {
            return $this->config[$key];
        }
    }

    function getDumpedConfig($key = false)
    {
        if(!file_exists($this->userdir.'config.dump')) return [];

        $config = unserialize(file_get_contents($this->userdir.'config.dump'));

        if($key == false) {
            return $config;
        } if(isset($config[$key])) {
            return $config[$key];
        }
    }

    function isSupported($key)
    {
        $this->reload();
        if($this->caps != null) {
            switch($key) {
                case 'pubsub':
                    return in_array('http://jabber.org/protocol/pubsub#persistent-items', $this->caps);
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
            $session = \Session::start();
            return ($session->get('mechanism') == 'ANONYMOUS');
        } else {
            return false;
        }
    }
}
