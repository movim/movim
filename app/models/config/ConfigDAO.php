<?php

namespace Modl;

class ConfigDAO extends SQL
{
    function set($c)
    {
        $this->_sql = '
            update config
            set description   = :description,
                theme         = :theme,
                locale        = :locale,
                loglevel      = :loglevel,
                timezone      = :timezone,
                xmppdomain    = :xmppdomain,
                xmppdescription = :xmppdescription,
                xmppcountry   = :xmppcountry,
                xmppwhitelist = :xmppwhitelist,
                info          = :info,
                unregister    = :unregister,
                username      = :username,
                password      = :password';

        $this->prepare(
            'Config',
            [
                'description'  => $c->description,
                'theme'        => $c->theme,
                'locale'       => $c->locale,
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
            ]
        );

        $this->run('Config');

        if(!$this->_effective) {
            $this->_sql = '
                truncate table config;';

            $this->prepare('Config');

            $this->run('Config');

            $this->_sql = '
                insert into config
                (
                    description,
                    theme,
                    locale,
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
                )
                values
                (
                    :description,
                    :theme,
                    :locale,
                    :loglevel,
                    :timezone,
                    :xmppdomain,
                    :xmppdescription,
                    :xmppcountry,
                    :xmppwhitelist,
                    :info,
                    :unregister,
                    :username,
                    :password
                )
                ';

            $this->prepare(
                'Config',
                [
                    'description'  => $c->description,
                    'theme'        => $c->theme,
                    'locale'       => $c->locale,
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
                ]
            );

            $this->run('Config');
        }
    }

    function get()
    {
        $this->_sql = '
            select * from config';

        $this->prepare('Config');

        $conf = $this->run('Config', 'item');

        if(!isset($conf)) {
            return new Config;
        }

        return $conf;
    }
}
