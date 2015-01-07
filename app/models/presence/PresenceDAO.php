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
                $presence->resource
            );

        $this->_sql = '
            update presence
            set value       = :value,
                priority    = :priority,
                status      = :status,
                node        = :node,
                ver         = :ver,
                delay       = :delay,
                last        = :last,
                publickey   = :publickey,
                muc         = :muc,
                mucaffiliation = :mucaffiliation,
                mucrole     = :mucrole
            where id        = :id';
        
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
                'muc'       => $presence->muc,
                'mucaffiliation' => $presence->mucaffiliation,
                'mucrole'   => $presence->mucrole,
                'id'        => $id
            )
        );
        
        $this->run('Presence');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into presence
                (id,
                session,
                jid,
                resource,
                value,
                priority,
                status,
                node,
                ver,
                delay,
                last,
                publickey,
                muc,
                mucaffiliation,
                mucrole)
                values(
                    :id,
                    :session,
                    :jid,
                    :resource,
                    :value,
                    :priority,
                    :status,
                    :node,
                    :ver,
                    :delay,
                    :last,
                    :publickey,
                    :muc,
                    :mucaffiliation,
                    :mucrole)';
            
            $this->prepare(
                'Presence', 
                array(
                    'id'        => $id,
                    'session'   => $presence->session,
                    'jid'       => $presence->jid,
                    'resource'  => $presence->resource,
                    'value'     => $presence->value,
                    'priority'  => $presence->priority,
                    'status'    => $presence->status,
                    'node'      => $presence->node,
                    'ver'       => $presence->ver,
                    'delay'     => $presence->delay,
                    'last'      => $presence->last,
                    'publickey' => $presence->publickey,
                    'muc'       => $presence->muc,
                    'mucaffiliation' => $presence->mucaffiliation,
                    'mucrole'   => $presence->mucrole
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
    
  
    function getPresence($jid, $resource) {        
        $this->_sql = '
            select * from presence
            where 
                session = :session
                and jid = :jid
                and resource = :resource';
        
        $this->prepare(
            'Presence', 
            array(
                'session' => $this->_user,
                'jid' => $jid,
                'resource' => $resource
            )
        );
        
        return $this->run('Presence', 'item');
    }
    
    
    function getJid($jid) {       
        $this->_sql = '
            select * from presence
            where 
                session = :session
                and jid = :jid
            order by mucaffiliation desc';
        
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
    
    function clearMuc($muc) {
        $this->_sql = '
            delete from presence
            where 
                session = :session
                and jid = :jid';
        
        $this->prepare(
            'Presence', 
            array(
                'session' => $this->_user,
                'jid'     => $muc
            )
        );
        
        return $this->run('Presence');
    }
}
