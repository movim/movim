<?php

namespace modl;

class SubscriptionDAO extends ModlSQL {
    function set(Subscription $s) {
        $this->_sql = '
            update subscription
            set subscription = :subscription,
                timestamp = :timestamp,
                subid = :subid
            where jid = :jid
                and server = :server
                and node = :node';
        
        $this->prepare(
            'Subscription', 
            array(                
                'subscription' => $s->subscription,
                'timestamp' => $s->timestamp,
                'jid'   => $s->jid,
                'server'=> $s->server,
                'node'  => $s->node,
                'subid' => $s->subid
            )
        );
        
        $this->run('Subscription');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into subscription
                (jid, server, node, subscription, subid, timestamp)
                values (:jid, :server, :node, :subscription, :subid, :timestamp)';
            
            $this->prepare(
                'Subscription', 
                array(
                    'subscription' => $s->subscription,
                    'timestamp' => $s->timestamp,
                    'jid'   => $s->jid,
                    'server'=> $s->server,
                    'node'  =>$s->node,
                    'subid' => $s->subid
                )
            );
            
            $this->run('Subscription');
        }
    }
    
    function get($server, $node) {
        $this->_sql = '
            select * from subscription
            where jid = :jid
                and server = :server
                and node = :node';
        
        $this->prepare(
            'Subscription', 
            array(
                'jid' => $this->_user,
                'server' => $server,
                'node' => $node
            )
        );
        
        return $this->run('Subscription');
    }
    
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
                subscription, item.name';
        
        $this->prepare(
            'Subscription', 
            array(
                'jid' => $this->_user
            )
        );
        
        return $this->run('Subscription');
    }
    
    function deleteNode($server, $node) {
        $this->_sql = '
            delete from subscription
            where jid = :jid
                and server = :server
                and node = :node';
        
        $this->prepare(
            'Subscription', 
            array(
                'jid' => $this->_user,
                'server' => $server,
                'node' => $node
            )
        );
        
        return $this->run('Subscription');
    }
    
    function deleteNodeSubid($server, $node, $subid) {
        $this->_sql = '
            delete from subscription
            where jid = :jid
                and server = :server
                and node = :node
                and subid = :subid';
        
        $this->prepare(
            'Subscription', 
            array(
                'jid' => $this->_user,
                'server' => $server,
                'node' => $node,
                'subid' => $subid,
            )
        );
        
        return $this->run('Subscription');
    }
}
