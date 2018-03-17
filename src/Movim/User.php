<?php

namespace Movim;

use Movim\i18n\Locale;
use App\Session as DBSession;
use App\User as DBUser;

class User
{
    public $caps;
    public $userdir;
    public $dbuser;

    /**
     * Class constructor. Reloads the user's session or attempts to authenticate
     * the user.
     */
    function __construct($username = false)
    {
        $s = Session::start();
        $this->dbuser = DBUser::firstOrNew(['jid' => $s->get('jid')]);

        if ($username) {
            $s->set('username', $username);
            $this->userdir = DOCUMENT_ROOT.'/users/'.$username.'/';
        }
    }

    /**
     * @brief Reload the user configuration
     */
    function reload($language = false)
    {
        $session = DBSession::find(SESSION_ID);

        if ($session) {
            if ($language) {
                $lang = $this->getConfig('language');
                if (isset($lang)) {
                    $l = Locale::start();
                    $l->load($lang);
                }
            }

            $cd = new \Modl\CapsDAO;
            $caps = $cd->get($session->host);
            if ($caps) {
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
        if ($s->get('jid')) {
            $this->userdir = DOCUMENT_ROOT.'/users/'.$s->get('jid').'/';

            if (!is_dir($this->userdir)) {
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
        if (isset($config['language'])) {
            $this->dbuser->language = $config['language'];
        }

        if (isset($config['cssurl'])) {
            $this->dbuser->cssurl = $config['cssurl'];
        }

        if (isset($config['nsfw'])) {
            $this->dbuser->nsfw = $config['nsfw'];
        }

        if (isset($config['nightmode'])) {
            $this->dbuser->nightmode = $config['nightmode'];
        }

        $this->dbuser->save();
        $this->reload(true);
    }

    function getConfig($key = false)
    {
        if ($key == false) {
            return $this->dbuser;
        }

        return $this->dbuser->{$key};
    }

    function getDumpedConfig($key = false)
    {
        if (!file_exists($this->userdir.'config.dump')) {
            return [];
        }

        $config = unserialize(file_get_contents($this->userdir.'config.dump'));

        if ($key == false) {
            return $config;
        }

        if (isset($config[$key])) {
            return $config[$key];
        }
    }

    function isSupported($key)
    {
        $this->reload();
        if ($this->caps != null) {
            switch ($key) {
                case 'pubsub':
                    return in_array('http://jabber.org/protocol/pubsub#persistent-items', $this->caps);
                case 'upload':
                    $cd = new \Modl\CapsDAO;
                    return ($cd->getUpload($this->getServer()) != null);
                default:
                    return false;
            }
        }
        if ($key == 'anonymous') {
            $session = Session::start();
            return ($session->get('mechanism') == 'ANONYMOUS');
        }
        return false;
    }
}
