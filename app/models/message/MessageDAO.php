<?php

namespace modl;

class MessageDAO extends SQL {  
    function set(Message $message) {
        $this->_sql = '
            insert into message
            (
            session,
            jidto,
            jidfrom,
            resource,
            type,
            subject,
            thread,
            body,
            html,
            published,
            delivered)
            values(
                :session,
                :jidto,
                :jidfrom,
                :resource,
                :type,
                :subject,
                :thread,
                :body,
                :html,
                :published,
                :delivered
                )';
            
        $this->prepare(
            'Message',
            array(
                'session'   => $message->session,
                'jidto'     => $message->jidto,
                'jidfrom'   => $message->jidfrom,
                'resource'  => $message->resource,
                'type'      => $message->type,
                'subject'   => $message->subject,
                'thread'    => $message->thread,
                'body'      => $message->body,
                'html'      => $message->html,
                'published' => $message->published,
                'delivered' => $message->delivered
            )
        );
            
        return $this->run('Message');
    }
    
    function getContact($jid, $limitf = false, $limitr = false) {
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

    function deleteContact($jid) {
        $this->_sql = '
            delete from message
            where session = :session
                and (jidfrom = :jidfrom
                or jidto   = :jidto)';

        $this->prepare(
            'Message',
            array(
                'jidfrom'   => $jid,
                'jidto'     => $jid,
                'session' => $this->_user
            )
        );
            
        return $this->run('Message');
    }

    function getRoomSubject($room) {
        $this->_sql = '
            select * from message 
            where jidfrom = :jidfrom
              and subject != \'\'
              order by published desc
              limit 1';

        $this->prepare(
            'Message',
            array(
                'jidfrom'   => $room
            )
        );
            
        return $this->run('Message', 'item');
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
    }
    
    function getStatistics() {
        $this->_sql = '
            select count(*) as count, extract(month from published) as month, extract(year from published) as year 
            from message
            where session = :session
            group by month, year order by year, month';
        
        $this->prepare(
            'Message', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run(null, 'array'); 
    }
}
