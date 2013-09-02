<?php

namespace modl;

class CapsDAO extends ModlSQL { 
    /*function create() {
        $sql = '
        drop table if exists Caps';
        
        $this->_db->query($sql);

        $sql = '          
        create table if not exists `Caps` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `node` varchar(256) DEFAULT NULL,
            `category` varchar(128) DEFAULT NULL,
            `type` varchar(128) DEFAULT NULL,
            `name` varchar(128) DEFAULT NULL,
            `features` text,
            PRIMARY KEY (`id`)
        ) CHARACTER SET utf8 COLLATE utf8_bin
        ';
        $this->_db->query($sql);     
    }*/
    
    function get($node) {
        //$sql = 'select * from Caps where node=\''.$node.'\'';
        //return $this->mapper('Caps', $this->_db->query($sql), 'item');    
        
        $this->_sql = '
            select * from caps
            where 
                node = :node';
        
        $this->prepare(
            'Caps', 
            array(
                'node' => $node
            )
        );
        
        return $this->run('Caps', 'item');
    }
    
    function getAll() {
        //$sql = 'select * from Caps';
        //return $this->mapper('Caps', $this->_db->query($sql));       
        
        $this->_sql = '
            select * from caps';
        
        $this->prepare(
            'Caps'
        );
        
        return $this->run('Caps');
    }
    
    function set(Caps $caps) {
        /*$request = $this->prepare('
            insert into Caps
            (node,
            category,
            type,
            name,
            features
            )
            values(
                ?,?,?,?,?
                )', $caps);
                
        $request->bind_param(
            'sssss',
            $caps->node,
            $caps->category,
            $caps->type,
            $caps->name,
            $caps->features
            );
        $request->execute();

        $request->close();*/
        
        
        $this->_sql = '
            insert into caps
            (
            node,
            category,
            type,
            name,
            features
            )
            values(
                :node,
                :category,
                :type,
                :name,
                :features
                )';
        
        $this->prepare(
            'Caps', 
            array(
                'node'      => $caps->node,
                'category'  => $caps->category,
                'type'      => $caps->type,
                'name'      => $caps->name,
                'features'  => $caps->features,
            )
        );
        
        return $this->run('Caps');
    }
}
