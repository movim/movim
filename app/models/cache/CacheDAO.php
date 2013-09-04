<?php

namespace modl;

class CacheDAO extends ModlSQL { 
    function get($key) {
        $this->_sql = '
            select * from cache
            where 
                session = :session';
        
        $this->prepare(
            'Cache', 
            array(
                'session' => $key
            )
        );
        
        return $this->run('Cache', 'item');
    }
    
    function set(Cache $cache) {
        $this->_sql = '
            update cache
                set data = :data,
                    checksum = :checksum,
                    timestamp = :timestamp
                where session = :session';
        
        $this->prepare(
            'Cache', 
            array(
                'session'   => $cache->session,
                'data'      => $cache->data,
                'timestamp' => $cache->timestamp,
                'checksum'  => $cache->checksum
            )
        );
        
        $this->run('Cache');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into cache
                (session, data, checksum, timestamp)
                values (:session, :data, :checksum, :timestamp)';
            
            $this->prepare(
                'Cache', 
                array(
                    'session'   => $cache->session,
                    'data'      => $cache->data,
                    'timestamp' => $cache->timestamp,
                    'checksum'  => $cache->checksum
                )
            );
            
            return $this->run('Cache');
        }
    }
}
