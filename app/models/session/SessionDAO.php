<?php

namespace modl;

class SessionDAO extends SQL {
    function set($session, $container, $name, $value) {
        $timestamp = date(DATE_ISO8601);

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
    }
}
