<?php

namespace modl;

class SessionxDAO extends ModlSQL {
    function init(Sessionx $s) {        
        $this->_sql = '
            update sessionx
            set user        = :user,
                ressource   = :ressource,
                rid         = :rid,
                sid         = :sid,
                id          = :id,
                url         = :url,
                port        = :port,
                host        = :host,
                domain      = :domain,
                config      = :config,
                active      = :active,
                timestamp   = :timestamp
            where session = :session';
        
        $this->prepare(
            'Sessionx', 
            array(
                'session'   => $s->session,
                'user'      => $s->user,
                'ressource' => $s->ressource,
                'rid'       => $s->rid,
                'sid'       => $s->sid,
                'id'        => $s->id,
                'url'       => $s->url,
                'port'      => $s->port,
                'host'      => $s->host,
                'domain'    => $s->domain,
                'config'    => $s->config,
                'active'    => $s->active,
                'timestamp' => $s->timestamp
                )
        );
        
        $this->run('Sessionx');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into sessionx
                (session,
                 user,
                 ressource,
                 rid,
                 sid,
                 id,
                 url,
                 port,
                 host,
                 domain,
                 config,
                 active,
                 timestamp)
                values
                (:session,
                 :user,
                 :ressource,
                 :rid,
                 :sid,
                 :id,
                 :url,
                 :port,
                 :host,
                 :domain,
                 :config,
                 :active,
                 :timestamp)';
            
            $this->prepare(
                'Sessionx', 
                array(
                    'session'   => $s->session,
                    'user'      => $s->user,
                    'ressource' => $s->ressource,
                    'rid'       => $s->rid,
                    'sid'       => $s->sid,
                    'id'        => $s->id,
                    'url'       => $s->url,
                    'port'      => $s->port,
                    'host'      => $s->host,
                    'domain'    => $s->domain,
                    'config'    => $s->config,
                    'active'    => $s->active,
                    'timestamp' => $s->timestamp
                )
            );
            
            $this->run('Sessionx');
        }
    }

    function getId($session) {
        $this->_sql = '
            select id from sessionx
            where 
                session = :session';
        
        $this->prepare(
            'Sessionx', 
            array(
                'session' => $session
            )
        );
        
        $value = $this->run(null, 'array');
        $value = $value[0]['id'];

        $this->_sql = '
            update sessionx
            set
                id = :id
            where 
                session = :session';
        
        $this->prepare(
            'Sessionx', 
            array(
                'session' => $session,
                'id' => $value+1
            )
        );

        $this->run();
        
        return $value;
    }

    function getRid($session) {
        $this->_sql = '
            select rid from sessionx
            where 
                session = :session';
        
        $this->prepare(
            'Sessionx', 
            array(
                'session' => $session
            )
        );
        
        $value = $this->run(null, 'array');
        $value = $value[0]['rid'];

        $this->_sql = '
            update sessionx
            set
                rid = :rid
            where 
                session = :session';
        
        $this->prepare(
            'Sessionx', 
            array(
                'session' => $session,
                'rid' => $value+1
            )
        );

        $this->run();
        
        return $value;
    }

    function delete($session) {
        $this->_sql = '
            delete from sessionx
            where 
                session = :session';
        
        $this->prepare(
            'Sessionx', 
            array(
                'session' => $session
            )
        );
        
        return $this->run('Sessionx');
    }
    /*function set($session, $container, $name, $value, $timestamp) {
        $timestamp = date(DATE_ISO8601, $timestamp);

        $this->_sql = '
            update session
            set value = :value,
                timestamp = :timestamp
            where session = :session
                and container = :container
                and name = :name';
        
        $this->prepare(
            'Session', 
            array(
                'session'   => $session,
                'container' => $container,
                'name'      => $name,
                'value'     => $value,
                'timestamp' => $timestamp
            )
        );
        
        $this->run('Session');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into session
                (name, value, session, container, timestamp)
                values (:name, :value, :session, :container, :timestamp)';
            
            $this->prepare(
                'Session', 
                array(
                    'session'   => $session,
                    'container' => $container,
                    'name'      => $name,
                    'value'     => $value,
                    'timestamp' => $timestamp
                )
            );
            
            return $this->run('Session');
        }
    }
    
    function get($session, $container, $name) {        
        $this->_sql = '
            select * from session
            where 
                session = :session
                and container = :container
                and name = :name';
        
        $this->prepare(
            'Session', 
            array(
                'session' => $session,
                'container' => $container,
                'name' => $name
            )
        );
        
        return $this->run('Session', 'item');
    }
    
    function delete($session, $container, $name) {
        $this->_sql = '
            delete from session
            where 
                session = :session
                and container = :container
                and name = :name';
        
        $this->prepare(
            'Session', 
            array(
                'session' => $session,
                'container' => $container,
                'name' => $name
            )
        );
        
        return $this->run('Session');
    }
    
    function deleteContainer($session, $container) {
        $this->_sql = '
            delete from session
            where 
                session = :session
                and container = :container';
        
        $this->prepare(
            'Session', 
            array(
                'session' => $session,
                'container' => $container,
            )
        );
        
        return $this->run('Session');
    }*/
}
