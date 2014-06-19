<?php

namespace modl;

class PostnDAO extends SQL {  
    function set(Postn $post) {
        $this->_sql = '
            update postn
                set aname           = :aname,
                    aid             = :aid,
                    aemail          = :aemail,
                    
                    title           = :title,
                    content         = :content,
                    contentcleaned  = :contentcleaned,
                    
                    commentplace    = :commentplace,
                    
                    published       = :published,
                    updated         = :updated,
                    delay           = :delay,
                    
                    lat             = :lat,
                    lon             = :lon,
                    
                    links           = :links,
                    tags            = :tags,
                    
                    hash            = :hash
                    
                where session = :session
                    and jid = :jid
                    and node = :node
                    and nodeid = :nodeid';
                    
        $this->prepare(
            'Postn', 
            array(
                'aname'             => $post->aname,
                'aid'               => $post->aid,
                'aemail'            => $post->aemail,
                        
                'title'             => $post->title,
                'content'           => $post->content,
                'contentcleaned'    => $post->contentcleaned,
                
                'commentplace'      => $post->commentplace,
                
                'published'         => $post->published,
                'updated'           => $post->updated,
                'delay'             => $post->delay,
                        
                'lat'               => $post->lat,
                'lon'               => $post->lon,
                        
                'links'             => $post->links,
                'tags'              => $post->tags,
                        
                'hash'              => $post->hash,
                        
                'session'           => $post->session,
                'jid'               => $post->jid,
                'node'              => $post->node,
                'nodeid'            => $post->nodeid
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
                aemail,
                
                title,
                content,
                contentcleaned,
                
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
                    :aemail,
                    
                    :title,
                    :content,
                    :contentcleaned,
                    
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
                    'aname'             => $post->aname,
                    'aid'               => $post->aid,
                    'aemail'            => $post->aemail,
                    
                    'title'             => $post->title,
                    'content'           => $post->content,
                    'contentcleaned'    => $post->contentcleaned,
                    
                    'commentplace'      => $post->commentplace,
                    
                    'published'         => $post->published,
                    'updated'           => $post->updated,
                    'delay'             => $post->delay,
                            
                    'lat'               => $post->lat,
                    'lon'               => $post->lon,
                            
                    'links'             => $post->links,
                    'tags'              => $post->tags,
                            
                    'hash'              => $post->hash,
                            
                    'session'           => $post->session,
                    'jid'               => $post->jid,
                    'node'              => $post->node,
                    'nodeid'            => $post->nodeid
                )
            );
            
            $this->run('Postn'); 
        }
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
    }

    function deleteNode($jid, $node) {
        $this->_sql = '
            delete from postn
            where jid = :jid
                and node = :node';

        $this->prepare(
            'Postn',
            array(
                'jid' => $jid,
                'node' => $node
            )
        );
            
        return $this->run('Message');
    }
    
    function getNode($from, $node, $limitf = false, $limitr = false) {
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
        $this->_sql = '
            select *, postn.aid as jid, privacy.value as privacy from postn
            left outer join contact on postn.aid = contact.jid
            left outer join privacy on postn.nodeid = privacy.pkey
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
    
    function getStatistics() {
        $this->_sql = '
            select count(*) as count, extract(month from published) as month, extract(year from published) as year 
            from postn 
            where session = :session
            group by month, year order by year desc, month desc';
        
        $this->prepare(
            'Postn', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run(null, 'array'); 
    }

    function getCountSince($date) {
        $this->_sql = '
            select count(*) from postn
            left outer join subscription on 
            postn.session = subscription.jid and 
            postn.jid = subscription.server and
            postn.node = subscription.node
            where postn.session = :session
                    and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                    and postn.node not like \'urn:xmpp:inbox\'
            and subscription is not null
            and published > :published';
        
        $this->prepare(
            'Postn', 
            array(
                'session' => $this->_user,
                'published' => $date
            )
        );
        
        $arr = $this->run(null, 'array');
        if(is_array($arr) && isset($arr[0])) {
            $arr = array_values($arr[0]);
            return (int)$arr[0];
        }
    }

    function getLastDate() {
        $this->_sql = '
            select published from postn
            left outer join subscription on 
            postn.session = subscription.jid and 
            postn.jid = subscription.server and
            postn.node = subscription.node
            where postn.session = :session
                    and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                    and postn.node not like \'urn:xmpp:inbox\'
            and subscription is not null
            order by postn.published desc
            limit 1 offset 0';
        
        $this->prepare(
            'Postn', 
            array(
                'session' => $this->_user
            )
        );
        
        $arr = $this->run(null, 'array');
        if(is_array($arr) && isset($arr[0]))
            return $arr[0]['published'];
    }

    function exist($id) {
        $this->_sql = '
            select count(*) from postn
            where postn.session = :session
                    and postn.nodeid = :nodeid
            ';
        
        $this->prepare(
            'Postn', 
            array(
                'session'   => $this->_user,
                'nodeid'    => $id
            )
        );

        $arr = $this->run(null, 'array');
        if(is_array($arr) && isset($arr[0])) {
            $arr = array_values($arr[0]);
            return (bool)$arr[0];
        }
    }
}
