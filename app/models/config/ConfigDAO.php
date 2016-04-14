<?php

namespace Modl;

class ConfigDAO extends SQL {
    function set(Config $c) {
        $this->_sql = '
            update config
            set description   = :description,
                theme         = :theme,
                locale        = :locale,
                maxusers      = :maxusers,
                loglevel      = :loglevel,
                timezone      = :timezone,
                xmppdomain    = :xmppdomain,
                xmppdescription = :xmppdescription,
                xmppcountry   = :xmppcountry,
                xmppwhitelist = :xmppwhitelist,
                info          = :info,
                unregister    = :unregister,
                username      = :username,
                password      = :password,
                sizelimit     = :sizelimit';

        $this->prepare(
            'Config',
            array(
                'description'  => $c->description,
                'theme'        => $c->theme,
                'locale'       => $c->locale,
                'maxusers'     => $c->maxusers,
                'loglevel'     => $c->loglevel,
                'timezone'     => $c->timezone,
                'xmppdomain'   => $c->xmppdomain,
                'xmppdescription' => $c->xmppdescription,
                'xmppcountry'  => $c->xmppcountry,
                'xmppwhitelist'=> $c->xmppwhitelist,
                'info'         => $c->info,
                'unregister'   => $c->unregister,
                'username'     => $c->username,
                'password'     => $c->password,
                'sizelimit'    => $c->sizelimit
            )
        );

        $this->run('Config');

        if(!$this->_effective) {
            $this->_sql = '
                truncate table config;';

            $this->prepare(
                'Config',
                array(
                )
            );

            $this->run('Config');

            $this->_sql = '
                insert into config
                (
                    description,
                    theme,
                    locale,
                    maxusers,
                    loglevel,
                    timezone,
                    xmppdomain,
                    xmppdescription,
                    xmppcountry,
                    xmppwhitelist,
                    info,
                    unregister,
                    username,
                    password,
                    sizelimit
                )
                values
                (
                    :description,
                    :theme,
                    :locale,
                    :maxusers,
                    :loglevel,
                    :timezone,
                    :xmppdomain,
                    :xmppdescription,
                    :xmppcountry,
                    :xmppwhitelist,
                    :info,
                    :unregister,
                    :username,
                    :password,
                    :sizelimit
                )
                ';

            $this->prepare(
                'Config',
                array(
                    'description'  => $c->description,
                    'theme'        => $c->theme,
                    'locale'       => $c->locale,
                    'maxusers'     => $c->maxusers,
                    'loglevel'     => $c->loglevel,
                    'timezone'     => $c->timezone,
                    'xmppdomain'   => $c->xmppdomain,
                    'xmppdescription' => $c->xmppdescription,
                    'xmppcountry'  => $c->xmppcountry,
                    'xmppwhitelist'=> $c->xmppwhitelist,
                    'info'         => $c->info,
                    'unregister'   => $c->unregister,
                    'username'     => $c->username,
                    'password'     => $c->password,
                    'sizelimit'    => $c->sizelimit
                )
            );

            $this->run('Config');
        }
    }

    function get() {
        $this->_sql = '
            select * from config';

        $this->prepare('Config');

        $conf = $this->run('Config', 'item');

        if(!isset($conf))
            return new Config;

        return $conf;
    }
}
