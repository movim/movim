<?php

namespace modl;

class SessionxDAO extends ModlSQL {
    function init(Sessionx $s) {        
        $this->_sql = '
            update sessionx
            set user        = :user,
                password    = :password,
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
                'password'  => $s->password,
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
                 password,
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
                 :password,
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
                    'password'  => $s->password,
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

    function update($session, $key, $value) {
        $this->_sql = '
            update sessionx
            set
                '.$key.' = :'.$key.'
            where 
                session = :session';
        
        $this->prepare(
            'Sessionx', 
            array(
                'session' => $session,
                $key => $value
            )
        );

        $this->run('Sessionx');
    }

    function get($session) {
        $this->_sql = '
            select * from sessionx
            where 
                session = :session';
        
        $this->prepare(
            'Sessionx', 
            array(
                'session' => $session
            )
        );

        return $this->run('Sessionx', 'item');
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
}
