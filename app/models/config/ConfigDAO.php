<?php

namespace Modl;

class ConfigDAO extends SQL {
    function set(Config $c) {
        $this->_sql = '
            update config
            set environment   = :environment,  
                description   = :description, 
                theme         = :theme,
                locale        = :locale,
                maxusers      = :maxusers,
                loglevel      = :loglevel,
                timezone      = :timezone,
                boshurl       = :boshurl,
                xmppwhitelist = :xmppwhitelist,
                info          = :info,
                unregister    = :unregister,
                username      = :username,
                password      = :password,
                sizelimit     = :sizelimit';
        
        $this->prepare(
            'Config', 
            array(                
                'environment'  => $c->environment,
                'description'  => $c->description,
                'theme'        => $c->theme,
                'locale'       => $c->locale,
                'maxusers'     => $c->maxusers,
                'loglevel'     => $c->loglevel,
                'timezone'     => $c->timezone,
                'boshurl'      => $c->boshurl,
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
                    environment,
                    description,
                    theme,
                    locale,
                    maxusers,
                    loglevel,
                    timezone,
                    boshurl,
                    xmppwhitelist,
                    info,
                    unregister,
                    username,
                    password,
                    sizelimit
                )
                values
                (
                    :environment,
                    :description,
                    :theme,
                    :locale,
                    :maxusers,
                    :loglevel,
                    :timezone,
                    :boshurl,
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
                    'environment'  => $c->environment,
                    'description'  => $c->description,
                    'theme'        => $c->theme,
                    'locale'       => $c->locale,
                    'maxusers'     => $c->maxusers,
                    'loglevel'     => $c->loglevel,
                    'timezone'     => $c->timezone,
                    'boshurl'      => $c->boshurl,
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

        $this->prepare('Config', array());
                
        $conf = $this->run('Config', 'item');

        if(!isset($conf))
            return new Config;

        return $conf;
    }
}
