<?php

namespace modl;

class SubscriptionDAO extends ModlSQL {
    /*function create() {
        $sql = '
        drop table if exists `Subscription`';
        
        $this->_db->query($sql);

        $sql = '
        create table if not exists `Subscription` (
            `jid` varchar(128) DEFAULT NULL,
            `server` varchar(128) DEFAULT NULL,
            `node` varchar(128) DEFAULT NULL,
            `subscription` varchar(128) DEFAULT NULL,
            `subid` varchar(128) DEFAULT NULL,
            `timestamp` datetime DEFAULT NULL
        ) CHARACTER SET utf8 COLLATE utf8_bin';
        $this->_db->query($sql);   
    }*/
    
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
                insert into Subscription
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
        /*
        $request = $this->_db->prepare('
            update Subscription
            set subscription = ?,
                timestamp = ?
            where jid = ?
                and server = ?
                and node = ?
                and subid = ?');
                
        $request->bind_param(
            'ssssss',
            $s->subscription,
            $s->timestamp,
            $s->jid,
            $s->server,
            $s->node,
            $s->subid);
              
        $request->execute();
        
        if($this->_db->affected_rows == 0) {
            $request = $this->_db->prepare('
                insert into Subscription
                (jid, server, node, subscription, subid, timestamp)
                values (?,?,?,?,?,?)');
                
            $request->bind_param(
                'ssssss',
                $s->jid,
                $s->server,
                $s->node,
                $s->subscription,
                $s->subid,
                $s->timestamp);
                
            $request->execute();            
        }
        
        $request->close();
        */
    }
    
    function get($server, $node) {
        /*$sql = '
            select * from Subscription
            where jid = \''.$this->_user.'\'
                and server = \''.$server.'\'
                and node = \''.$node.'\'';
                
        return $this->mapper('Subscription', $this->_db->query($sql));  */
        
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
        /*
        $sql = '
            select * from Subscription
            where jid = \''.$this->_user.'\'
            group by server, node';
                
        return $this->mapper('Subscription', $this->_db->query($sql));
        */
        
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
        /*$sql = '
            delete from Subscription
            where jid = \''.$this->_user.'\'
                and server = \''.$server.'\'
                and node = \''.$node.'\'';
                
        return $this->_db->query($sql);
        * */
        
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
        /*
        $sql = '
            delete from Subscription
            where jid = \''.$this->_user.'\'
                and server = \''.$server.'\'
                and node = \''.$node.'\'
                and subid= \''.$subid.'\'';
                
        return $this->_db->query($sql);
        */
        
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
