<?php

namespace modl;

class CapsDAO extends SQL { 
    function get($node) {
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
        $this->_sql = '
            select * from caps';
        
        $this->prepare(
            'Caps'
        );
        
        return $this->run('Caps');
    }
    
    function set(Caps $caps) {
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
