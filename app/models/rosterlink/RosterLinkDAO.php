<?php

namespace modl;

class RosterLinkDAO extends ModlSQL {
    /*function create() {
        $sql = '
        drop table if exists `RosterLink`';
        
        $this->_db->query($sql);

        $sql = '
        create table if not exists `RosterLink` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `key` varchar(128) DEFAULT NULL,
            `jid` varchar(128) DEFAULT NULL,
            `rostername` varchar(128) DEFAULT NULL,
            `rosterask` varchar(128) DEFAULT NULL,
            `rostersubscription` varchar(128) DEFAULT NULL,
            `realname` varchar(128) DEFAULT NULL,
            `group` varchar(128) DEFAULT NULL,
            `chaton` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) CHARACTER SET utf8 COLLATE utf8_bin';
        $this->_db->query($sql);  
    }*/
    
    function set(RosterLink $r) { 
        /*$r->key = $this->_user->getLogin();
        $this->keepTransaction('
            insert into RosterLink
            (RosterLink.key, 
            jid, 
            rostername, 
            rosterask, 
            rostersubscription,
            realname,
            RosterLink.group,
            chaton)
            values (?,?,?,?,?,?,?,?)',
            array(
                'sssssssi',
                $r->key,
                $r->jid,
                $r->rostername,
                $r->rosterask,
                $r->rostersubscription,
                $r->realname,
                $r->group,
                $r->chaton),
            $r
        );*/
        
        $this->_sql = '
            insert into rosterlink
            (
                session, 
                jid, 
                rostername, 
                rosterask, 
                rostersubscription,
                realname,
                groupname,
                chaton)
                values (
                    :session, 
                    :jid, 
                    :rostername, 
                    :rosterask, 
                    :rostersubscription,
                    :realname,
                    :groupname,
                    :chaton
                    )';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session'       => $this->_user,
                'jid'           => $r->jid,
                'rostername'    => $r->rostername,
                'rosterask'     => $r->rosterask,
                'rostersubscription'     => $r->rostersubscription,
                'realname'      => $r->realname,
                'groupname'     => $r->groupname,
                'chaton'        => $r->chaton
            )
        );
        
        return $this->run('RosterLink');
    }
    
    function update(RosterLink $r) {  
        $this->_sql = '
            update rosterlink
            set rostername  = :rostername,
                rosterask   = :rosterask,
                rostersubscription = :rostersubscription,
                realname    = :realname,
                groupname   = :groupname,
                chaton      = :chaton
            where session   = :session
                and jid     = :jid';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session'       => $this->_user,
                'jid'           => $r->jid,
                'rostername'    => $r->rostername,
                'rosterask'     => $r->rosterask,
                'rostersubscription'     => $r->rostersubscription,
                'realname'      => $r->realname,
                'groupname'     => $r->groupname,
                'chaton'        => $r->chaton
            )
        );
        
        return $this->run('RosterLink');
        /*$r->key = $this->_user->getLogin();
        $this->keepTransaction('
            update RosterLink
            set rostername = ?,
                rosterask = ?,
                rostersubscription = ?,
                realname = ?,
                RosterLink.group = ?,
                chaton = ?
            where RosterLink.key = ?
                and jid = ?',
            array(
                'sssssiss',
                $r->rostername,
                $r->rosterask,
                $r->rostersubscription,
                $r->realname,
                $r->group,
                $r->chaton,
                $r->key,
                $r->jid),
            $r
        ); */
    }
    
    function setNow(RosterLink $r) {
        /*$request = $this->prepare('
            update RosterLink
            set rostername = ?,
                rosterask = ?,
                rostersubscription = ?,
                realname = ?,
                RosterLink.group = ?,
                chaton = ?
            where RosterLink.key = ?
                and jid = ?', $r);
                
        $request->bind_param(
                'sssssiss',
                $r->rostername,
                $r->rosterask,
                $r->rostersubscription,
                $r->realname,
                $r->group,
                $r->chaton,
                $r->key,
                $r->jid);
              
        $request->execute();
        
        if($this->_db->affected_rows == 0) {
            $request = $this->prepare('
                    insert into RosterLink
                    (RosterLink.key, 
                    jid, 
                    rostername, 
                    rosterask, 
                    rostersubscription,
                    realname,
                    RosterLink.group,
                    chaton)
                    values (?,?,?,?,?,?,?,?)', $r);
            $request->bind_param(
                    'sssssssi',
                $r->key,
                $r->jid,
                $r->rostername,
                $r->rosterask,
                $r->rostersubscription,
                $r->realname,
                $r->group,
                $r->chaton);
                
            $request->execute();
        }
        
        $request->close();*/
        $this->update($r);
        
        if(!$this->_effective)
            $this->set($r);
    }
    
    function setChat($jid, $chaton) {
        /*$sql = 'update RosterLink set chaton ='.$chaton.' 
                where  RosterLink.key=\''.$this->_user->getLogin().'\'
                    and jid=\''.$this->_db->real_escape_string($jid).'\'';
        $this->_db->query($sql);  */
        
        $this->_sql = '
            update rosterlink
            set chaton      = :chaton
            where session   = :session
                and jid     = :jid';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session'       => $this->_user,
                'jid'           => $jid,
                'chaton'        => $chaton
            )
        );
        
        return $this->run('RosterLink');
    }
    
    function get($jid) {
        /*$key = $this->_user->getLogin();
        
        $sql = '
            select *
            from RosterLink
            where RosterLink.key=\''.$key.'\'
                and jid =\''.$jid.'\'';
        
        return $this->mapper('RosterLink', $this->_db->query($sql), 'item');*/
        
        $this->_sql = '
            select *
            from rosterlink
            where session=:session
                and jid = :jid';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user,
                'jid' => $jid,
            )
        );
        
        return $this->run('RosterLink', 'item');
    }
    
    function getGroups() {
        /*$key = $this->_user->getLogin();
        
        $sql = '
            select RosterLink.group
            from RosterLink 
            where RosterLink.key=\''.$this->_db->real_escape_string($key).'\'
            group by RosterLink.group';
            
        $arr = array();
        
        $resultset = $this->_db->query($sql);
        while($r = $resultset->fetch_array( MYSQL_NUM))
            array_push($arr, $r[0]);
        
        return $arr;*/
        
        $this->_sql = '
            select groupname
            from rosterlink
            where session = :session
            group by groupname';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user
            )
        );
        
        $results = $this->run('RosterLink');
        $arr = array();
        
        foreach($results as $r)
            array_push($arr, $r->groupname);
            
        return $arr;
    }
    
    function getRoster($to = null) {
        if($to != null) 
            $session = $to;
        else
            $session = $this->_user;
        /*
        $sql = '
            select *
            from RosterLink
            where RosterLink.key=\''.$this->_db->real_escape_string($key).'\'';
        
        return $this->mapper('RosterLink', $this->_db->query($sql));*/
        
        $this->_sql = '
            select *
            from rosterlink
            where session=:session';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $session
            )
        );
        
        return $this->run('RosterLink');
    }
    
    function getChats() {
        //$key = $this->_user->getLogin();
        
        /*$sql = '
            select *
            from RosterLink
            where RosterLink.key=\''.$this->_db->real_escape_string($key).'\'
            and chaton > 0';
        
        return $this->mapper('RosterLink', $this->_db->query($sql));*/
        
        $this->_sql = '
            select *
            from rosterlink
            where session=:session
            and chaton > 0';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run('RosterLink');
    }
    
    function clearRosterLink() {
        /*
        $sql = '
            delete from RosterLink 
            where RosterLink.key=\''.$this->_user->getLogin().'\'';
                
        return $this->_db->query($sql);
        */
        $this->_sql = '
            delete from rosterlink
            where session = :session';

        $this->prepare(
            'RosterLink',
            array(
                'session' => $this->_user
            )
        );
            
        return $this->run('RosterLink');
    }
    
    function delete($jid) {
        /*
        $sql = '
            delete from RosterLink 
            where RosterLink.key=\''.$this->_user->getLogin().'\'
            and jid=\''.$this->_db->real_escape_string($jid).'\'';
                
        return $this->_db->query($sql);        
        */
        $this->_sql = '
            delete from rosterlink
            where session = :session
            and jid = :jid';

        $this->prepare(
            'RosterLink',
            array(
                'session' => $this->_user,
                'jid' => $jid
            )
        );
            
        return $this->run('RosterLink');
    }
}
