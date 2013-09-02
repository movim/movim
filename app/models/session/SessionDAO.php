<?php

namespace modl;

class SessionDAO extends ModlSQL {
    /*function create() {
        $sql = '
        drop table if exists `SessionVar`';
        
        $this->_db->query($sql);

        $sql = '
        create table if not exists `SessionVar` (
            `id`        binary(40) NOT NULL,
            `name`      varchar(128) DEFAULT NULL,
            `value`     text,
            `session`   varchar(128) DEFAULT NULL,
            `container` varchar(128) DEFAULT NULL,
            `timestamp` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) CHARACTER SET utf8 COLLATE utf8_bin';
        $this->_db->query($sql);   
    }*/
    
    function set($session, $container, $name, $value, $timestamp) {
        $timestamp = date(DATE_ISO8601, $timestamp);
        /*
        $request = $this->prepare('
            update SessionVar
            set value = ?,
                timestamp = ?
            where id = ?', $session);
            
        $hash = sha1(
                $session.
                $container.
                $name
            ) ;

            where session = ?
                and container = ?
                and name = ?', $session);

        if(!$request)
            return $request;
                
        $request->bind_param(
            'sis',
            $value,
            $timestamp,
            $hash
            );
              
        $request->execute();
        
        if($this->_db->affected_rows == 0) {
            $request = $this->_db->prepare('
                insert into SessionVar 
                (id, name, value, session, container, timestamp)
                values (?,?,?,?,?,?)');
                
            $request->bind_param(
                'sssssi',
                $hash,
                $name,
                $value,
                $session,
                $container,
                $timestamp);
                
            $request->execute();            
        }
        
        $request->close();*/
        
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
        /*$sql = '
            delete from SessionVar 
            where session = \''.$session.'\'
                and container = \''.$container.'\'
                and name = \''.$name.'\'';
                
        return $this->_db->query($sql);     */
        
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
        /*$sql = '
            delete from SessionVar 
            where session = \''.$session.'\'
                and container = \''.$container.'\'';
                
        return $this->_db->query($sql);*/
        
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
