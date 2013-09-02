<?php

namespace modl;

class MessageDAO extends ModlSQL {  
    /*function create() {
        $sql = '
        drop table if exists Message';
        
        $this->_db->query($sql);
        
        $sql = '
        create table if not exists Message (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`key` varchar(128) DEFAULT NULL,
			`to` varchar(128) DEFAULT NULL,
			`from` varchar(128) DEFAULT NULL,
			`ressource` varchar(128) DEFAULT NULL,
			`type` varchar(20) DEFAULT NULL,
			`subject` text,
			`thread` varchar(128) DEFAULT NULL,
			`body` text,
			`published` datetime DEFAULT NULL,
			`delivered` datetime DEFAULT NULL,
			PRIMARY KEY (`id`)
        ) AUTO_INCREMENT=10 CHARACTER SET utf8 COLLATE utf8_bin';
        
        $this->_db->query($sql);
    }*/
    
    function set(Message $message) {
        $this->_sql = '
            insert into Message
            (
            session,
            jidto,
            jidfrom,
            ressource,
            type,
            subject,
            thread,
            body,
            published,
            delivered)
            values(
                :session,
                :jidto,
                :jidfrom,
                :ressource,
                :type,
                :subject,
                :thread,
                :body,
                :published,
                :delivered
                )';
            
        $this->prepare(
            'Message',
            array(
                'session'   => $this->_user,
                'jidto'     => $message->jidto,
                'jidfrom'   => $message->jidfrom,
                'ressource' => $message->ressource,
                'type'      => $message->type,
                'subject'   => $message->subject,
                'thread'    => $message->thread,
                'body'      => $message->body,
                'published' => $message->published,
                'delivered' =>$message->delivered
            )
        );
            
        return $this->run('Message');
        
        /*$request = $this->_db->prepare('
            insert into Message
            (`key`,
            `to`,
            `from`,
            `ressource`,
            `type`,
            `subject`,
            `thread`,
            `body`,
            `published`,
            `delivered`)
            values(
                ?,?,?,?,?,
                ?,?,?,?,?
                )');
                
        $request->bind_param(
            'ssssssssss',
            $message->key,
            $message->to,
            $message->from,
            $message->ressource,
            $message->type,
            $message->subject,
            $message->thread,
            $message->body,
            $message->published,
            $message->delivered
            );
        $request->execute();
        
        $request->close();*/
    }
    
    function getContact($jid, $limitf = false, $limitr = false) {
        /*
        $sql = '
            select * from Message 
            where Message.key = \''.$this->_user->getLogin().'\'
                and (Message.from = \''.$this->_db->real_escape_string($jid).'\'
                or Message.to = \''.$this->_db->real_escape_string($jid).'\')
            order by Message.published desc';
                
        if($limitr)
            $sql = $sql.' limit '.$limitf.','.$limitr;
                
        return array_reverse($this->mapper('Message', $this->_db->query($sql)));
        */
        
        $this->_sql = '
            select * from message 
            where session = :session
                and (jidfrom = :jidfrom
                or jidto = :jidto)
            order by published desc';
            
        if($limitr) 
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;
            
        $this->prepare(
            'Message',
            array(
                'session' => $this->_user,
                'jidfrom' => $jid,
                'jidto' => $jid
            )
        );
            
        return $this->run('Message');
    }
    
    function clearMessage() {
        $this->_sql = '
            delete from message 
            where session = :session';

        $this->prepare(
            'Message',
            array(
                'session' => $this->_user
            )
        );
            
        return $this->run('Message');
        /*
        $sql = '
            delete from Message 
            where Message.key=\''.$this->_user->getLogin().'\'';
                
        return $this->_db->query($sql);*/
    }
}
