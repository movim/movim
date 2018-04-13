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
}
