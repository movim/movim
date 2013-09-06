<?php

namespace modl;

class NodeDAO extends ModlSQL { 
    function set(Node $node) {
        $this->_sql = '
            update node
            set config = :config,
                title = :title,
                updated = :updated
            where serverid = :serverid
                and nodeid = :nodeid';
        
        $this->prepare(
            'Node', 
            array(                
                'config' => $node->config,
                'title'  => $node->title,
                'updated'=> $node->updated,
                'serverid' => $node->serverid,
                'nodeid'   => $node->nodeid
            )
        );
        
        $this->run('Node');
        
        if(!$this->_effective) {
            $this->_sql = '
                insert into node
                (serverid,
                nodeid,
                config,
                title,
                updated
                )
                values(
                    :serverid,
                    :nodeid,
                    :config,
                    :title,
                    :updated
                    )';
            
            $this->prepare(
                'Node', 
                array(
                    'config' => $node->config,
                    'title'  => $node->title,
                    'updated'=> $node->updated,
                    'serverid' => $node->serverid,
                    'nodeid'   => $node->nodeid
                )
            );
            
            $this->run('Node');
        }
    }
    
    function getServers() {
        $this->_sql = '
            select serverid, count(nodeid) as number 
            from node 
            where nodeid not like \'urn:xmpp:microblog:0:comments/%\' 
            group by serverid
            order by number desc';
            
        $this->prepare(
            'Server'
        );
            
        return $this->run('Server'); 
    }
    
    function getNodes($serverid) {
        /*$serverid = $this->_db->real_escape_string($serverid); 
        $sql = '
            select Node.*, count(P.nodeid) as number from Node 
            left outer join (select * from Postn where Postn.from = \''.$serverid.'\' group by nodeid) as P 
            on Node.nodeid = P.node
            where serverid=\''.$serverid.'\' 
            group by nodeid
            order by number desc';

        return $this->mapper('Node', $this->_db->query($sql));
        */
        
        /*$this->_sql = '
            select node.*, count(P.nodeid) as number from node 
            left outer join (select * from postn where postn.jid = :serverid) as P 
            on node.nodeid = P.node
            where serverid= :serverid
            order by number desc';
        */
        
        $this->_sql = '
            select * from node 
            left outer join (select server, node, subscription from subscription where jid = :nodeid) 
            as s on s.server = node.serverid 
            and s.node = node.nodeid
            left outer join (select node, count(node) as num from (
            select session, node, nodeid from postn
            where session = :nodeid
            group by nodeid, node, session
            order by node) as f group by node)
            as c on c.node = node.nodeid
            where serverid= :serverid
            order by node.title, nodeid';
            
        $this->prepare(
            'Node',
            array(
                // Dirty hack, using nodeid param to inject the session key
                'nodeid' => $this->_user,
                'serverid' => $serverid
            )
        );
            
        return $this->run('Node'); 
    }

    function deleteNodes($serverid) {
        $this->_sql = '
            delete from node
            where serverid= :serverid';
            
        $this->prepare(
            'Node',
            array(
                'serverid' => $serverid
            )
        );
            
        return $this->run('Node'); 
    }

    function deleteNode($serverid, $nodeid) {
        $this->_sql = '
            delete from node
            where serverid = :serverid
                and nodeid = :nodeid';
            
        $this->prepare(
            'Node',
            array(
                'serverid' => $serverid,
                'nodeid' => $nodeid
            )
        );
            
        return $this->run('Node'); 
    }
    
    function getNode($serverid, $nodeid) {
        $this->_sql = '
            select * from node
            where 
                nodeid = :nodeid
                and serverid = :serverid';
        
        $this->prepare(
            'Node', 
            array(
                'nodeid' => $nodeid,
                'serverid' => $serverid
            )
        );
        
        return $this->run('Node', 'item');
    }
}
