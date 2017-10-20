<?php

namespace Movim;

use Movim\i18n\Locale;

class User
{
    private $config = [];
    public $caps;
    public $userdir;

    /**
     * Class constructor. Reloads the user's session or attempts to authenticate
     * the user.
     */
    function __construct($username = false)
    {
        $s = Session::start();
        if($username) {
            $s->set('username', $username);
            $this->userdir = DOCUMENT_ROOT.'/users/'.$username.'/';
        }
    }

    /**
     * @brief Reload the user configuration
     */
    function reload($language = false)
    {
        $sd = new \Modl\SessionxDAO;
        $session = $sd->get(SESSION_ID);

        if($session) {
            if($language) {
                $lang = $this->getConfig('language');
                if(isset($lang)) {
                    $l = Locale::start();
                    $l->load($lang);
                }
            }

            $cd = new \Modl\CapsDAO;
            $caps = $cd->get($session->host);
            if($caps) {
                $this->caps = $caps->features;
            }
        }
    }

    /**
     * Checks if the user has an open session.
     */
    function isLogged()
    {
        $s = Session::start();
        return (bool)$s->get('jid');
    }

    function createDir()
    {
        $s = Session::start();
        if($s->get('jid')) {
            $this->userdir = DOCUMENT_ROOT.'/users/'.$s->get('jid').'/';

            if(!is_dir($this->userdir)) {
                mkdir($this->userdir);
                touch($this->userdir.'index.html');
            }
        }
    }

    function getLogin()
    {
        $s = Session::start();
        return $s->get('jid');
    }

    function getServer()
    {
        $s = Session::start();
        return $s->get('host');
    }

    function getUser()
    {
        $s = Session::start();
        return $s->get('username');
    }

    function setConfig(array $config)
    {
        $s = new \Modl\Setting;

        if(isset($config['language'])) {
            $s->language = $config['language'];
        }

        if(isset($config['cssurl'])) {
            $s->cssurl   = $config['cssurl'];
        }

        if(isset($config['nsfw'])) {
            $s->nsfw     = $config['nsfw'];
        }

        $sd = new \Modl\SettingDAO;
        $sd->set($s);

        $this->createDir();

        file_put_contents($this->userdir.'config.dump', serialize($config));

        $this->reload(true);
    }

    function getConfig($key = false)
    {
        $sd = new \Modl\SettingDAO;
        $s = $sd->get();

        if($key == false) {
            return $s;
        }

        if(is_object($s)
        && property_exists($s, $key)
        && isset($s->$key)) {
            return $s->$key;
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
                    $cd = new \Modl\CapsDAO;
                    return ($cd->getUpload($this->getServer()) != null);
                    break;
                default:
                    return false;
                    break;
            }
        } elseif($key == 'anonymous') {
            $session = Session::start();
            return ($session->get('mechanism') == 'ANONYMOUS');
        } else {
            return false;
        }
    }
}
