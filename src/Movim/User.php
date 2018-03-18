<?php

namespace Movim;

use Movim\i18n\Locale;
use App\Session as DBSession;
use App\User as DBUser;

class User
{
    public $caps;

    /**
     * @brief Reload the user configuration
     */
    function reload($language = false)
    {
        $session = DBSession::find(SESSION_ID);

        if ($session) {
            if ($language) {
                $lang = DBUser::me()->language;
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
