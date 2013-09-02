<?php

namespace modl;

class PostnDAO extends ModlSQL {  
    /*function create() {
        $sql = '
        drop table if exists Postn';
        
        $this->_db->query($sql);
        
        $sql = '
        create table if not exists Postn (
            `id`       binary(40) NOT NULL,
            `key`      varchar(128) DEFAULT NULL,
            
            `from`     varchar(128) DEFAULT NULL,
            `node`     varchar(128) DEFAULT NULL,
            `nodeid`   varchar(128) DEFAULT NULL,
            
            `aname`    varchar(128) DEFAULT NULL,
            `aid`      varchar(128) DEFAULT NULL,
            
            `title`    varchar(128) DEFAULT NULL,
            `content`  text,
            
            `commentplace`varchar(128) DEFAULT NULL,
            
            `published`datetime DEFAULT NULL,
            `updated`  datetime DEFAULT NULL,
            `delay`    datetime DEFAULT NULL,

            `lat`      varchar(128) DEFAULT NULL,
            `lon`      varchar(128) DEFAULT NULL,
            
            `links`    text,
            `tags`    text,
            
            `hash`     varchar(128) DEFAULT NULL,
            PRIMARY KEY (id)
        ) CHARACTER SET utf8 COLLATE utf8_bin;';
        
        $this->_db->query($sql);
    }*/
    
    function set(Postn $post) {
        $this->_sql = '
            update postn
                set aname = :aname,
                    aid = :aid,
                    
                    title = :title,
                    content = :content,
                    
                    commentplace = :commentplace,
                    
                    published = :published,
                    updated = :updated,
                    delay = :delay,
                    
                    lat = :lat,
                    lon = :lon,
                    
                    links = :links,
                    tags = :tags,
                    
                    hash = :hash
                    
                where session = :session
                    and jid = :jid
                    and node = :node
                    and nodeid = :nodeid';
                    
            $this->prepare(
                'Postn', 
                array(
                    'aname'     => $post->aname,
                    'aid'       => $post->aid,
                    
                    'title'     => $post->title,
                    'content'   => $post->content,
                    
                    'commentplace' => $post->commentplace,
                    
                    'published' => $post->published,
                    'updated'   => $post->updated,
                    'delay'     => $post->delay,
                    
                    'lat'       => $post->lat,
                    'lon'       => $post->lon,
                    
                    'links'     => $post->links,
                    'tags'      => $post->tags,
                    
                    'hash'      => $post->hash,
                    
                    'session'   => $post->session,
                    'jid'       => $post->jid,
                    'node'      => $post->node,
                    'nodeid'    => $post->nodeid
                )
            );
            
            $this->run('Postn'); 
            
            if(!$this->_effective) {
                $this->_sql ='
                    insert into postn
                    (
                    session,
                    
                    jid,
                    node,
                    nodeid,
                    
                    aname,
                    aid,
                    
                    title,
                    content,
                    
                    commentplace,
                    
                    published,
                    updated,
                    delay,

                    lat,
                    lon,
                    
                    links,
                    tags,
                    
                    hash)
                    values(
                        :session,
                        
                        :jid,
                        :node,
                        :nodeid,
                        
                        :aname,
                        :aid,
                        
                        :title,
                        :content,
                        
                        :commentplace,
                        
                        :published,
                        :updated,
                        :delay,

                        :lat,
                        :lon,
                        
                        :links,
                        :tags,
                        
                        :hash
                    )';
                    
                $this->prepare(
                    'Postn', 
                    array(
                        'aname'     => $post->aname,
                        'aid'       => $post->aid,
                        
                        'title'     => $post->title,
                        'content'   => $post->content,
                        
                        'commentplace' => $post->commentplace,
                        
                        'published' => $post->published,
                        'updated'   => $post->updated,
                        'delay'     => $post->delay,
                        
                        'lat'       => $post->lat,
                        'lon'       => $post->lon,
                        
                        'links'     => $post->links,
                        'tags'      => $post->tags,
                        
                        'hash'      => $post->hash,
                        
                        'session'   => $post->session,
                        'jid'       => $post->jid,
                        'node'      => $post->node,
                        'nodeid'    => $post->nodeid
                    )
                );
                
                $this->run('Postn'); 
            }
/*
        $request = $this->prepare('
            update Postn
            set `aname` = ?,
                `aid` = ?,
                
                `title` = ?,
                `content` = ?,
                
                `commentplace` = ?,
                
                `published` = ?,
                `updated` = ?,
                `delay` = ?,
                
                `lat` = ?,
                `lon` = ?,
                
                `links` = ?,
                `tags` = ?,
                
                `hash` = ?
                
            where `id` = ?', $post);

                
            where `key` = ?
                and `from` = ?
                and `node` = ?
                and `nodeid` = ?', $post);
        $hash = sha1(
                $post->key.
                $post->from.
                $post->node.
                $post->nodeid
            );
    
        $request->bind_param(
            'ssssssssssssss',
            $post->aname,
            $post->aid,
            $post->title,
            $post->content,
            $post->commentplace,
            $post->published,
            $post->updated,
            $post->delay,
            $post->lat,
            $post->lon,
            $post->links,
            $post->tags,
            $post->hash,
            $hash
            );
                        
        $result = $request->execute();
        
        if($this->_db->affected_rows == 0) {
            $request = $this->prepare('
                insert into Postn
                (
                `id`,
                `key`,
                
                `from`,
                `node`,
                `nodeid`,
                
                `aname`,
                `aid`,
                
                `title`,
                `content`,
                
                `commentplace`,
                
                `published`,
                `updated`,
                `delay`,

                `lat`,
                `lon`,
                
                `links`,
                `tags`,
                
                `hash`)
                values(
                    ?,?,?,?,?,
                    ?,?,?,?,?,
                    ?,?,?,?,?, 
                    ?,?,?
                )', $post);

            $request->bind_param(
                'ssssssssssssssssss',
                $hash,
                $post->key,
                $post->from,
                $post->node,
                $post->nodeid,
                $post->aname,
                $post->aid,
                $post->title,
                $post->content,
                $post->commentplace,
                $post->published,
                $post->updated,
                $post->delay,
                $post->lat,
                $post->lon,
                $post->links,
                $post->tags,
                $post->hash
                );                
            $request->execute();
        }
        
        $request->close();*/
    }

    function delete($nodeid) {
        $this->_sql = '
            delete from postn
            where nodeid = :nodeid';

        $this->prepare(
            'Postn',
            array(
                'nodeid' => $nodeid
            )
        );
            
        return $this->run('Message');
        
        /*$from = $this->_db->real_escape_string($from); 
        $nodeid = $this->_db->real_escape_string($nodeid); 
        $sql = '
            delete from Postn
            where nodeid=\''.$nodeid.'\'';
        return $this->_db->query($sql);     */
    }
    
    function getNode($from, $node, $limitf = false, $limitr = false) {
        /*$sql = '
            select *, Postn.aid, Privacy.value as privacy from Postn
            left outer join Contact on Postn.aid = Contact.jid
            left outer join Privacy on Postn.nodeid = Privacy.key
            where Postn.key = \''.$this->_user->getLogin().'\'
                and Postn.from = \''.$from.'\'
                and Postn.node = \''.$node.'\'
            order by Postn.published desc';
            
        if($limitr)
            $sql = $sql.' limit '.$limitf.','.$limitr;

        return $this->mapper('ContactPostn', $this->_db->query($sql));*/
        
        $this->_sql = '
            select *, postn.aid, privacy.value as privacy from postn
            left outer join contact on postn.aid = contact.jid
            left outer join privacy on postn.nodeid = privacy.pkey
            where postn.session = :session
                and postn.jid = :jid
                and postn.node = :node
            order by postn.published desc';

        if($limitr) 
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;
        
        $this->prepare(
            'Postn', 
            array(
                'session' => $this->_user,
                'jid' => $from,
                'node' => $node
            )
        );
        
        return $this->run('ContactPostn');
    }

    function getFeed($limitf = false, $limitr = false) {
        /*
         * $sql = '
            select *, Postn.aid as jid, Privacy.value as privacy from Postn
            left outer join Contact on Postn.aid = Contact.jid
            left outer join Privacy on Postn.nodeid = Privacy.key
            where Postn.key = \''.$this->_user->getLogin().'\'
				and Postn.node like \'urn:xmpp:microblog:0\'
                and (Postn.from in (select RosterLink.jid from RosterLink where RosterLink.key = \''.$this->_user->getLogin().'\')
                    or Postn.from = \''.$this->_user->getLogin().'\')
            order by Postn.published desc';
            
        if($limitr) 
            $sql = $sql.' limit '.$limitf.','.$limitr;
                
        return $this->mapper('ContactPostn', $this->_db->query($sql));
        */
        
        $this->_sql = '
            select *, postn.aid as jid, privacy.value as privacy from postn
            left outer join contact on postn.aid = contact.jid
            left outer join privacy on postn.nodeid = privacy.pkey
            where postn.session = :session
				and postn.node like \'urn:xmpp:microblog:0\'
                and (postn.jid in (select rosterlink.jid from rosterlink where rosterlink.session = :session)
                    or postn.jid = :session)
            order by postn.published desc';

        if($limitr) 
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;
        
        $this->prepare(
            'Postn', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run('ContactPostn');
    }
    
    function getNews($limitf = false, $limitr = false) {
        /*$sql = '
            select *, Postn.aid as jid from Postn
            left outer join Contact on Postn.aid = Contact.jid
            left outer join Subscription on 
                Postn.key = Subscription.jid and 
                Postn.from = Subscription.server and
                Postn.node = Subscription.node
            where Postn.key = \''.$this->_user->getLogin().'\'
				and Postn.node not like \'urn:xmpp:microblog:0:comments/%\'
				and Postn.node not like \'urn:xmpp:inbox\'
                and subscription is not null
            order by Postn.published desc';
            
        if($limitr) 
            $sql = $sql.' limit '.$limitf.','.$limitr;
                
        return $this->mapper('ContactPostn', $this->_db->query($sql));*/    
        
        $this->_sql = '
            select *, postn.aid as jid from postn
            left outer join contact on postn.aid = contact.jid
            left outer join subscription on 
                postn.session = subscription.jid and 
                postn.jid = subscription.server and
                postn.node = subscription.node
            where postn.session = :session
				and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
				and postn.node not like \'urn:xmpp:inbox\'
                and subscription is not null
            order by postn.published desc';

        if($limitr) 
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;
        
        $this->prepare(
            'Postn', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run('ContactPostn');
    }
    
    
    function getPublic($jid, $node) {
        /*if($node != false)
            $n = 'and Postn.node = \''.$node.'\' ';
        
        $sql = '
            select *, Postn.aid as jid, Privacy.value as privacy from Postn
            left outer join Contact on Postn.aid = Contact.jid
            join Privacy on Postn.nodeid = Privacy.key
            where Postn.from = \''.$jid.'\'
            '.$n.'
                and Postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                and Privacy.value = 1
            group by nodeid
            order by Postn.published desc
            limit 0, 20';
            
        return $this->mapper('ContactPostn', $this->_db->query($sql));   */
        
        /*if($node != false)
            $n = 'and Postn.node = :node ';
        else
            $n = '';*/

        /*$this->_sql = '
            select *, postn.aid as jid, privacy.value as privacy from postn
            left outer join contact on postn.aid = contact.jid
            join privacy on postn.nodeid = privacy.pkey
            where postn.jid = :jid
                and Postn.node = :node
                and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                and privacy.value = 1
            group by nodeid
            order by postn.published desc';*/
            //limit 0, 20';
            
        $this->_sql = '
            select *, postn.aid, privacy.value as privacy from postn
            left outer join contact on postn.aid = contact.jid
            left outer join privacy on postn.nodeid = privacy.pkey
            where postn.jid = :jid
                and postn.session = :jid
                and postn.node = :node
                and privacy.value = 1
            order by postn.published desc';
        
        $this->prepare(
            'Postn', 
            array(
                'jid' => $jid,
                'node' => $node
            )
        );
        
        return $this->run('ContactPostn');
    }
    
    function getComments($posts) {
        $commentsid = '';
        if(is_array($posts)) {
            $i = 0;            
            foreach($posts as $post) {
                if($i == 0)
                    $commentsid = "'urn:xmpp:microblog:0:comments/".$post->nodeid."'";
                else
                    $commentsid .= ",'urn:xmpp:microblog:0:comments/".$post->nodeid."'";
                $i++;
            }
        } else {
            $commentsid = "'urn:xmpp:microblog:0:comments/".$posts->nodeid."'";
        }

        // We request all the comments relative to our messages
        $this->_sql = '
            select *, postn.aid as jid from postn
            left outer join contact on postn.aid = contact.jid
            where postn.session = :session
                and postn.node in ('.$commentsid.')
            order by postn.published';
            
        $this->prepare(
            'Postn', 
            array(
                'session' => $this->_user
            )
        );
            
        return $this->run('ContactPostn'); 
    }
    
    function clearPost() {
        /*$sql = '
            delete from Postn
            where Postn.key=\''.$this->_user->getLogin().'\'';
                
        return $this->_db->query($sql);*/
        
        $this->_sql = '
            delete from postn
            where session = :session';

        $this->prepare(
            'Postn',
            array(
                'session' => $this->_user
            )
        );
            
        return $this->run('Postn');
    }
}
