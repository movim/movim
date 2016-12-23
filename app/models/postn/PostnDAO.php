<?php

namespace Modl;

class PostnDAO extends SQL
{
    function set(Postn $post)
    {
        $this->_sql = '
            update postn
                set aname           = :aname,
                    aid             = :aid,
                    aemail          = :aemail,

                    title           = :title,
                    content         = :content,
                    contentraw      = :contentraw,
                    contentcleaned  = :contentcleaned,

                    commentorigin   = :commentorigin,
                    commentnodeid   = :commentnodeid,

                    open            = :open,

                    published       = :published,
                    updated         = :updated,
                    delay           = :delay,

                    reply           = :reply,

                    lat             = :lat,
                    lon             = :lon,

                    links           = :links,
                    picture         = :picture,

                    hash            = :hash

                where origin = :origin
                    and node = :node
                    and nodeid = :nodeid';

        $this->prepare(
            'Postn',
            [
                'aname'             => $post->aname,
                'aid'               => $post->aid,
                'aemail'            => $post->aemail,

                'title'             => $post->title,
                'content'           => $post->content,
                'contentraw'        => $post->contentraw,
                'contentcleaned'    => $post->contentcleaned,

                'commentorigin'     => $post->commentorigin,
                'commentnodeid'     => $post->commentnodeid,

                'open'              => $post->open,

                'published'         => $post->published,
                'updated'           => $post->updated,
                'delay'             => $post->delay,

                'reply'             => $post->reply,

                'lat'               => $post->lat,
                'lon'               => $post->lon,

                'links'             => $post->links,
                'picture'           => $post->picture,

                'hash'              => $post->hash,

                'origin'            => $post->origin,
                'node'              => $post->node,
                'nodeid'            => $post->nodeid
            ]
        );

        $this->run('Postn');

        if(!$this->_effective) {
            $this->_sql ='
                insert into postn
                (
                origin,
                node,
                nodeid,

                aname,
                aid,
                aemail,

                title,
                content,
                contentraw,
                contentcleaned,

                commentorigin,
                commentnodeid,

                open,

                published,
                updated,
                delay,

                reply,

                lat,
                lon,

                links,
                picture,

                hash)
                values(
                    :origin,
                    :node,
                    :nodeid,

                    :aname,
                    :aid,
                    :aemail,

                    :title,
                    :content,
                    :contentraw,
                    :contentcleaned,

                    :commentorigin,
                    :commentnodeid,

                    :open,

                    :published,
                    :updated,
                    :delay,

                    :reply,

                    :lat,
                    :lon,

                    :links,
                    :picture,

                    :hash
                )';

            $this->prepare(
                'Postn',
                [
                    'aname'             => $post->aname,
                    'aid'               => $post->aid,
                    'aemail'            => $post->aemail,

                    'title'             => $post->title,
                    'content'           => $post->content,
                    'contentraw'        => $post->contentraw,
                    'contentcleaned'    => $post->contentcleaned,

                    'commentorigin'     => $post->commentorigin,
                    'commentnodeid'     => $post->commentnodeid,

                    'open'              => $post->open,

                    'published'         => $post->published,
                    'updated'           => $post->updated,
                    'delay'             => $post->delay,

                    'reply'             => $post->reply,

                    'lat'               => $post->lat,
                    'lon'               => $post->lon,

                    'links'             => $post->links,
                    'picture'           => $post->picture,

                    'hash'              => $post->hash,

                    'origin'            => $post->origin,
                    'node'              => $post->node,
                    'nodeid'            => $post->nodeid
                ]
            );

            $this->run('Postn');
        }
    }

    function get($origin, $node, $nodeid, $public = false, $around = false)
    {
        $params = [
                'origin' => $origin,
                'node' => $node,
                'nodeid' => $nodeid
            ];

        $this->_sql = '
            select postn.*, contact.*, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            left outer join item
                on postn.origin = item.server
                and postn.node = item.node
            where postn.origin = :origin
                and postn.node = :node';

        if(!$around) {
            $this->_sql .= ' and postn.nodeid = :nodeid';
        } else {
            $compare = ($around == 1) ? '>' : '<';
            $order   = ($around == 1) ? 'asc' : 'desc';
            $this->_sql .= ' and postn.nodeid = (
                    select nodeid
                    from postn
                    where published '. $compare .' (
                        select published
                        from postn
                        where postn.origin = :origin
                            and postn.node = :node
                            and postn.nodeid = :nodeid
                    )
                    and postn.origin = :origin
                    and postn.node = :node
                    and (
                        (
                            postn.origin in (
                                select jid
                                from rosterlink
                                where session = :jid
                                and rostersubscription in (\'both\', \'to\')
                            )
                            and node = \'urn:xmpp:microblog:0\'
                        )
                        or (
                            postn.origin = :jid
                            and node = \'urn:xmpp:microblog:0\'
                        )
                        or (
                            (postn.origin, node) in (
                                select server, node
                                from subscription
                                where jid = :jid)
                        )
                        or postn.open = true
                    )
                    order by published '.$order.'
                    limit 1
                )
                ';

            $params['contact.jid'] = $this->_user;
        }

        if($public) $this->_sql .= ' and postn.open = true';

        $this->prepare(
            'Postn',
            $params
        );

        return $this->run('ContactPostn', 'item');
    }

    function getNext($origin, $node, $nodeid, $public = false)
    {
        return $this->get($origin, $node, $nodeid, $public, 1);
    }

    function getPrevious($origin, $node, $nodeid, $public = false)
    {
        return $this->get($origin, $node, $nodeid, $public, 2);
    }

    function getPublicItem($origin, $node, $nodeid)
    {
        return $this->get($origin, $node, $nodeid, true);
    }

    function getIds($origin, $node, $nodeids)
    {
        $ids = (!empty($nodeids)) ? implode('\',\'', $nodeids) : '';
        $this->_sql = '
            select postn.*, contact.*, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            left outer join item
                on postn.origin = item.server
                and postn.node = item.node
            where postn.origin = :origin
                and postn.node = :node
                and postn.nodeid in (\''.$ids.'\')
            order by published';


        $this->prepare(
            'Postn',
            [
                'origin' => $origin,
                'node' => $node
            ]
        );

        return $this->run('ContactPostn');
    }

    function delete($nodeid)
    {
        $this->_sql = '
            delete from postn
            where nodeid = :nodeid';

        $this->prepare(
            'Postn',
            [
                'nodeid' => $nodeid
            ]
        );

        return $this->run('Postn');
    }

    function deleteNode($origin, $node)
    {
        $this->_sql = '
            delete from postn
            where origin = :origin
                and node = :node';

        $this->prepare(
            'Postn',
            [
                'origin' => $origin,
                'node' => $node
            ]
        );

        return $this->run('Postn');
    }

    function getNode($from, $node, $limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            left outer join item
                on postn.origin = item.server
                and postn.node = item.node
            where ((postn.origin, node) in (select server, node from subscription where jid = :jid))
                and postn.origin = :origin
                and postn.node = :node
                and postn.node != \'urn:xmpp:microblog:0\'
            order by postn.published desc';

        if($limitr !== false)
            $this->_sql = $this->_sql.' limit '.(int)$limitr.' offset '.(int)$limitf;

        $this->prepare(
            'Postn',
            [
                'subscription.jid' => $this->_user,
                'origin' => $from,
                'node' => $node
            ]
        );

        return $this->run('ContactPostn');
    }

    function getNodeUnfiltered($from, $node, $limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            left outer join item
                on postn.origin = item.server
                and postn.node = item.node
            where postn.origin = :origin
                and postn.node = :node
            order by postn.published desc';

        if($limitr !== false)
            $this->_sql = $this->_sql.' limit '.(int)$limitr.' offset '.(int)$limitf;

        $this->prepare(
            'Postn',
            [
                'origin' => $from,
                'node' => $node
            ]
        );

        return $this->run('ContactPostn');
    }

    function getPublicTag($tag, $limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            where nodeid in (select nodeid from tag where tag = :tag)
                and postn.open = true
            order by postn.published desc';

        if($limitr !== false)
            $this->_sql = $this->_sql.' limit '.(int)$limitr.' offset '.(int)$limitf;

        $this->prepare(
            'Postn',
            [
                'tag.tag' => $tag
            ]
        );

        return $this->run('ContactPostn');
    }

    function getGallery($from, $limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            where postn.aid = :aid
                and postn.picture is not null
            order by postn.published desc';

        if($limitr !== false)
            $this->_sql = $this->_sql.' limit '.(int)$limitr.' offset '.(int)$limitf;

        $this->prepare(
            'Postn',
            [
                'aid' => $from
            ]
        );

        return $this->run('ContactPostn');
    }

    function getGroupPicture($origin, $node)
    {
        $this->_sql = '
            select * from postn
            where postn.origin = :origin
                and postn.node = :node
                and postn.picture is not null
                and postn.open = true
            order by postn.published desc
            limit 1';

        $this->prepare(
            'Postn',
            [
                'origin' => $origin,
                'node' => $node
            ]
        );

        return $this->run('Postn', 'item');
    }

    function getAllPosts($jid = false, $limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            where (
                (postn.origin in (select jid from rosterlink where session = :origin and rostersubscription in (\'both\', \'to\')) and node = \'urn:xmpp:microblog:0\')
                or (postn.origin = :origin and node = \'urn:xmpp:microblog:0\')
                or ((postn.origin, node) in (select server, node from subscription where jid = :origin))
                )
                and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                and postn.node not like \'urn:xmpp:inbox\'
            order by postn.published desc
            ';

        if($limitr)
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;

        if($jid == false)
            $jid = $this->_user;

        $this->prepare(
            'Postn',
            [
                'origin' => $jid
            ]
        );

        return $this->run('ContactPostn');
    }

    function getFeed($limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            where ((postn.origin in (select jid from rosterlink where session = :origin and rostersubscription in (\'both\', \'to\')) and node = \'urn:xmpp:microblog:0\')
                or (postn.origin = :origin and node = \'urn:xmpp:microblog:0\'))
                and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                and postn.node not like \'urn:xmpp:inbox\'
            order by postn.published desc
            ';

        if($limitr)
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;

        $this->prepare(
            'Postn',
            [
                'origin' => $this->_user
            ]
        );

        return $this->run('ContactPostn');
    }

    function getNews($limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            where ((postn.origin, node) in (select server, node from subscription where jid = :origin))
            order by postn.published desc
            ';

        if($limitr)
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;

        $this->prepare(
            'Postn',
            [
                'origin' => $this->_user
            ]
        );

        return $this->run('ContactPostn');
    }


    function getMe($limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            where postn.origin = :origin and postn.node = \'urn:xmpp:microblog:0\'
            order by postn.published desc
            ';

        if($limitr)
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;

        $this->prepare(
            'Postn',
            [
                'origin' => $this->_user
            ]
        );

        return $this->run('ContactPostn');
    }

    function getPublic($origin, $node, $limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            where postn.origin = :origin
                and postn.node = :node
                and postn.open = true
            order by postn.published desc';

        if($limitr !== false)
            $this->_sql = $this->_sql.' limit '.(int)$limitr.' offset '.(int)$limitf;

        $this->prepare(
            'Postn',
            [
                'origin' => $origin,
                'node' => $node
            ]
        );

        return $this->run('ContactPostn');
    }

    // TODO: fixme
    function getComments($posts)
    {
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
            where postn.node in ('.$commentsid.')
            order by postn.published';

        $this->prepare(
            'Postn',
            []
        );

        return $this->run('ContactPostn');
    }

    function countComments($origin, $id)
    {
        $this->_sql = '
            select count(*) from postn
            where origin = :origin
                and node = :node
                and (title != \'\'
                or contentraw != \'\')
                and contentraw != :contentraw';

        $this->prepare(
            'Postn',
            [
                'origin' => $origin,
                'node'   => 'urn:xmpp:microblog:0:comments/'.$id,
                'contentraw' => '♥'
            ]
        );

        $arr = $this->run(null, 'array');
        if(is_array($arr) && isset($arr[0])) {
            $arr = array_values($arr[0]);
            return (int)$arr[0];
        }
    }

    function countLikes($origin, $id)
    {
        $this->_sql = '
            select count(*) from postn
            where origin = :origin
                and node = :node
                and contentraw = :contentraw';

        $this->prepare(
            'Postn',
            [
                'origin' => $origin,
                'node'   => 'urn:xmpp:microblog:0:comments/'.$id,
                'contentraw' => '♥'
            ]
        );

        $arr = $this->run(null, 'array');
        if(is_array($arr) && isset($arr[0])) {
            $arr = array_values($arr[0]);
            return (int)$arr[0];
        }
    }

    function clearPost() {
        $this->_sql = '
            delete from postn
            where session = :session';

        $this->prepare(
            'Postn',
            [
                'session' => $this->_user
            ]
        );

        return $this->run('Postn');
    }

    function getCountSince($date)
    {
        $this->_sql = '
            select count(*) from postn
            where (
                (postn.origin in (select jid from rosterlink where session = :origin and rostersubscription in (\'both\', \'to\')) and node = \'urn:xmpp:microblog:0\')
                or (postn.origin = :origin and node = \'urn:xmpp:microblog:0\')
                or ((postn.origin, node) in (select server, node from subscription where jid = :origin))
                )
                and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                and postn.node not like \'urn:xmpp:inbox\'
                and published > :published
                ';

        $this->prepare(
            'Postn',
            [
                'origin' => $this->_user,
                'published' => $date
            ]
        );

        $arr = $this->run(null, 'array');
        if(is_array($arr) && isset($arr[0])) {
            $arr = array_values($arr[0]);
            return (int)$arr[0];
        }
    }

    function getNotifsSince($date, $limitf = false, $limitr = false)
    {
        $this->_sql = '
            select * from postn
            left outer join contact on postn.aid = contact.jid
            where origin = :origin
            and commentnodeid is null
            and node != \'urn:xmpp:microblog:0\'
            and published > :published
            order by published desc
                ';

        if($limitr) {
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;
        }

        $this->prepare(
            'Postn',
            [
                'origin' => $this->_user,
                'published' => $date
            ]
        );

        return $this->run('ContactPostn');
    }

    function getLastDate()
    {
        $this->_sql = '
            select published from postn
            where (
                (postn.origin in (select jid from rosterlink where session = :origin and rostersubscription in (\'both\', \'to\')) and node = \'urn:xmpp:microblog:0\')
                or (postn.origin = :origin and node = \'urn:xmpp:microblog:0\')
                or ((postn.origin, node) in (select server, node from subscription where jid = :origin))
                )
                and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                and postn.node not like \'urn:xmpp:inbox\'
            order by postn.published desc
            limit 1 offset 0';

        $this->prepare(
            'Postn',
            [
                'origin' => $this->_user
            ]
        );

        $arr = $this->run(null, 'array');
        if(is_array($arr) && isset($arr[0]))
            return $arr[0]['published'];
    }

    function getLastPublished($origin = false, $limitf = false, $limitr = false)
    {
        $this->_sql = '
            select * from postn
            left outer join item on postn.origin = item.server
                and postn.node = item.node
            where
                postn.node != \'urn:xmpp:microblog:0\'
                and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                and postn.node not like \'urn:xmpp:inbox\'
                and postn.origin not like \'nsfw%\'
                and (
                    (postn.origin, postn.node) not in
                    (select server, node
                        from subscription
                        where subscription.jid = :jid
                    )
                )
                and aid is not null';

        if($origin) {
            $this->_sql .= '
                and origin = :origin
            ';
        }

        $this->_sql .= '
            order by published desc
            ';

        if($limitr) {
            $this->_sql .= ' limit '.$limitr.' offset '.$limitf;
        }

        if($origin) {
            $this->prepare(
                'Postn',
                [
                    'origin' => $origin,
                    'subscription.jid' => $this->_user
                ]
            );
        } else {
            $this->prepare('Postn', ['subscription.jid' => $this->_user]);
        }

        return $this->run('Postn');
    }

    function getLastBlogPublic($limitf = false, $limitr = false)
    {
        switch($this->_dbtype) {
            case 'mysql':
                $this->_sql = '
                    select * from postn
                    left outer join contact on postn.aid = contact.jid
                    where
                        node = \'urn:xmpp:microblog:0\'
                        and postn.origin not in (select jid from rosterlink where session = :origin)
                        and postn.open = true
                        and content != \'\'
                    group by origin
                    order by published desc
                    ';

                if($limitr)
                    $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;
            break;
            case 'pgsql':
                $this->_sql = '
                    select * from (
                        select distinct on (origin) * from postn
                        left outer join contact on postn.aid = contact.jid
                        where
                            node = \'urn:xmpp:microblog:0\'
                            and postn.origin not in (select jid from rosterlink where session = :origin)
                            and postn.open = true
                            and content != \'\'
                    ) p
                    order by published desc
                    ';

                if($limitr)
                    $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;
            break;
        }

        $this->prepare(
            'Postn',
            [
                'origin' => $this->_user
            ]
        );

        return $this->run('ContactPostn');
    }

    function search($key)
    {
        $this->_sql = '
            select *, postn.aid from postn
            left outer join contact on postn.aid = contact.jid
            where (
                (postn.origin in (select jid from rosterlink where session = :origin and rostersubscription in (\'both\', \'to\')) and node = \'urn:xmpp:microblog:0\')
                or (postn.origin = :origin and node = \'urn:xmpp:microblog:0\')
                or ((postn.origin, node) in (select server, node from subscription where jid = :origin))
                )
                and postn.node not like \'urn:xmpp:microblog:0:comments/%\'
                and postn.node not like \'urn:xmpp:inbox\'
                and upper(title) like upper(:title)
            order by postn.published desc
            limit 6 offset 0
            ';

        $this->prepare(
            'Postn',
            [
                'origin' => $this->_user,
                'title'  => '%'.$key.'%'
            ]
        );

        return $this->run('ContactPostn');
    }

    function exists($origin, $node, $id)
    {
        $this->_sql = '
            select count(*) from postn
            where origin = :origin
            and node = :node
            and nodeid = :nodeid
            ';

        $this->prepare(
            'Postn',
            [
                'origin'    => $origin,
                'node'      => $node,
                'nodeid'    => $id
            ]
        );

        $arr = $this->run(null, 'array');
        if(is_array($arr) && isset($arr[0])) {
            $arr = array_values($arr[0]);
            return (bool)$arr[0];
        }
    }
}
