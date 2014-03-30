<?php

namespace Modl;

class PresenceDAO extends SQL {
    function __construct() {
        parent::__construct();
    }
    
    function set(Presence $presence) {
        $id = sha1(
                $presence->session.
                $presence->jid.
                $presence->ressource
            );

        $this->_sql = '
            update presence
            set value = :value,
                priority = :priority,
                status = :status,
                node = :node,
                ver = :ver,
                delay = :delay,
                last = :last,
                publickey = :publickey
            where id = :id';
        
        $this->prepare(
            'Presence', 
            array(                
                'value'     => $presence->value,
                'priority'  => $presence->priority,
                'status'    => $presence->status,
                'node'      => $presence->node,
                'ver'       => $presence->ver,
                'delay'     => $presence->delay,
                'last'      => $presence->last,
                'publickey' => $presence->publickey,
                'id'        => $id
            )
        );
        
        $this->run('Presence');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into presence
                (id,session, jid, ressource, value, priority, status, node, ver, delay,last,publickey)
                values(
                    :id,
                    :session,
                    :jid,
                    :ressource,
                    :value,
                    :priority,
                    :status,
                    :node,
                    :ver,
                    :delay,
                    :last,
                    :publickey)';
            
            $this->prepare(
                'Presence', 
                array(
                    'id'        => $id,
                    'session'      => $presence->session,
                    'jid'       => $presence->jid,
                    'ressource' => $presence->ressource,
                    'value'     => $presence->value,
                    'priority'  => $presence->priority,
                    'status'    => $presence->status,
                    'node'      => $presence->node,
                    'ver'       => $presence->ver,
                    'delay'     => $presence->delay,
                    'last'      => $presence->last,
                    'publickey'      => $presence->publickey
                )
            );
            
            $this->run('Presence');
        }
    }
    
    function getAll() {
        $this->_sql = '
            select * from presence;
            ';
            
        $this->prepare('Presence');
        return $this->run('Presence');
    }
    
  
    function getPresence($jid, $ressource) {        
        $this->_sql = '
            select * from presence
            where 
                session = :session
                and jid = :jid
                and ressource = :ressource';
        
        $this->prepare(
            'Presence', 
            array(
                'session' => $this->_user,
                'jid' => $jid,
                'ressource' => $ressource
            )
        );
        
        return $this->run('Presence', 'item');
    }
    
    
    function getJid($jid) {       
        $this->_sql = '
            select * from presence
            where 
                session = :session
                and jid = :jid';
        
        $this->prepare(
            'Presence', 
            array(
                'session' => $this->_user,
                'jid' => $jid
            )
        );
        
        return $this->run('Presence');
    }
    
    function clearPresence($session) {
        $this->_sql = '
            delete from presence
            where 
                session = :session';
        
        $this->prepare(
            'Presence', 
            array(
                'session' => $session
            )
        );
        
        return $this->run('Presence');
    }
}
