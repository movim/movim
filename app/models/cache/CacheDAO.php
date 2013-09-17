<?php

namespace modl;

class CacheDAO extends ModlSQL { 
    function get($session, $key) {
        $this->_sql = '
            select * from cache
            where 
                session = :session
            and key     = :key';
        
        $this->prepare(
            'Cache', 
            array(
                'session' => $session,
                'key' => $key
            )
        );
        
        return $this->run('Cache', 'item');
    }
    
    function set(Cache $cache) {
        $this->_sql = '
            update cache
                set data = :data,
                    timestamp = :timestamp
                where session = :session
                and key = :key';
        
        $this->prepare(
            'Cache', 
            array(
                'session'   => $cache->session,
                'data'      => $cache->data,
                'timestamp' => $cache->timestamp,
                'key'       => $cache->key
            )
        );
        
        $this->run('Cache');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into cache
                (session, key, data, timestamp)
                values (:session, :key, :data, :timestamp)';
            
            $this->prepare(
                'Cache', 
                array(
                    'session'   => $cache->session,
                    'key'       => $cache->key,
                    'data'      => $cache->data,
                    'timestamp' => $cache->timestamp
                )
            );
            
            return $this->run('Cache');
        }
    }
}
