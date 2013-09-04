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
            select jid, server, node, subscription from subscription
            where jid = :jid
            group by server, node, jid, subscription';
        
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
