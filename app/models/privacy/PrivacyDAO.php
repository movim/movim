<?php

namespace modl;

class PrivacyDAO extends SQL {    
    function set(Privacy $p) {        
        $this->_sql = '
            update privacy
            set value = :value,
                hash = :hash
            where pkey = :pkey';
        
        $this->prepare(
            'Privacy', 
            array(
                'pkey'  => $p->pkey,
                'value' => $p->value,
                'hash'  => $p->hash
            )
        );
        
        $this->run('Privacy');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into privacy
                (pkey, value, hash)
                values (:pkey,:value,:hash)';
            
            $this->prepare(
                'Privacy', 
                array(
                    'pkey'  => $p->pkey,
                    'value' => $p->value,
                    'hash'  => $p->hash
                )
            );
            
            $this->run('Privacy');
        }
    }
    
    function get($key) {
        $this->_sql = '
            select * from privacy
            where 
                pkey = :pkey';
        
        $this->prepare(
            'Privacy', 
            array(
                'pkey' => $key
            )
        );
        
        return $this->run('Privacy', 'item');
    }
}
