<?php

namespace modl;

class ConferenceDAO extends SQL {
    function set(Conference $c) {
        $this->_sql = '
            update conference
            set name        = :name,
                nick        = :nick,
                autojoin    = :autojoin,
                status      = :status
            where jid       = :jid
              and conference= :conference';
        
        $this->prepare(
            'Conference', 
            array(                
                'jid'           => $c->jid,
                'conference'    => $c->conference,
                'name'          => $c->name,
                'nick'          => $c->nick,
                'autojoin'      => $c->autojoin,
                'status'        => $c->status
            )
        );
        
        $this->run('Conference');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into conference
                (jid, conference, name, nick, autojoin, status)
                values (:jid, :conference, :name, :nick, :autojoin, :status)';
            
            $this->prepare(
                'Conference', 
                array(
                    'jid'           => $c->jid,
                    'conference'    => $c->conference,
                    'name'          => $c->name,
                    'nick'          => $c->nick,
                    'autojoin'      => $c->autojoin,
                    'status'        => $c->status
                )
            );
            
            $this->run('Conference');
        }
    }

    function get($conference) {
        $this->_sql = '
            select * from conference
            where jid       = :jid
              and conference= :conference';
        
        $this->prepare(
            'Conference', 
            array(
                'jid' => $this->_user,
                'conference' => $conference,
            )
        );
        
        return $this->run('Conference', 'item');        
    }
    
    function getAll() {
        $this->_sql = '
            select * from conference
            where jid = :jid
            order by conference';
        
        $this->prepare(
            'Conference', 
            array(
                'jid' => $this->_user
            )
        );
        
        return $this->run('Conference');        
    }
    
    function getConnected() {
        $this->_sql = '
            select * from conference
            where jid   = :jid
              and status= 1';
        
        $this->prepare(
            'Conference', 
            array(
                'jid' => $this->_user
            )
        );
        
        return $this->run('Conference');        
    }
    /*
    function getSubscribed() {
        $this->_sql = '
            select 
                subscription.jid, 
                subscription.server, 
                subscription.node, 
                subscription, 
                name
            from subscription
            left outer join item 
                on item.server = subscription.server 
                and item.node = subscription.node
            where subscription.jid = :jid
            group by 
                subscription.server, 
                subscription.node, 
                subscription.jid, 
                subscription, item.name
            order by 
                subscription.server';
        
        $this->prepare(
            'Subscription', 
            array(
                'jid' => $this->_user
            )
        );
        
        return $this->run('Subscription');
    }
    */
    function delete() {
        $this->_sql = '
            delete from conference
            where jid = :jid';
        
        $this->prepare(
            'Subscription', 
            array(
                'jid' => $this->_user
            )
        );
        
        return $this->run('conference');
    }
    
    function deleteNode($conference) {
        $this->_sql = '
            delete from conference
            where jid       = :jid
              and conference= :conference';
        
        $this->prepare(
            'Conference', 
            array(
                'jid' => $this->_user,
                'conference' => $conference
            )
        );
        
        return $this->run('Conference');
    }
}
