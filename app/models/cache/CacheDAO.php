<?php

namespace modl;

class CacheDAO extends ModlSQL { 
    /*function create() {
        $sql = '
        drop table if exists CacheVar';
        $this->_db->query($sql);

        $sql = '          
        create table if not exists `CacheVar` (
          `id`        binary(40) NOT NULL,
          `key`       varchar(128) DEFAULT NULL,
          `data`      LONGTEXT,
          `checksum`  varchar(64) DEFAULT NULL,
          `timestamp` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) CHARACTER SET utf8 COLLATE utf8_bin
        ';
        $this->_db->query($sql);     
    }*/
    
    function get($key) {
        //$sql = 'select * from CacheVar where CacheVar.key=\''.$key.'\'';
        //return $this->mapper('Cache', $this->_db->query($sql), 'item');
        
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
        /*$request = $this->_db->prepare('
            update CacheVar
            set data = ?,
                checksum = ?,
                timestamp = ?
            where id = ?');
            
        $hash = sha1($cache->key);
                
        $request->bind_param(
            'ssis',
            $cache->data,
            $cache->checksum,
            $cache->timestamp,
            $hash
            );
              
        $request->execute();
        
        if($this->_db->affected_rows == 0) {
            $request = $this->_db->prepare('
                insert into CacheVar
                (`id`,`key`, `data`, `checksum`, `timestamp`)
                values (?,?,?,?,?)');
                
            $request->bind_param(
                'ssssi',
                $hash,
                $cache->key,
                $cache->data,
                $cache->checksum,
                $cache->timestamp
            );
                
            $request->execute();            
        }
        
        $request->close();*/
    }
}
