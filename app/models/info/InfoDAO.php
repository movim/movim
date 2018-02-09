<?php

namespace Modl;

class InfoDAO extends SQL
{
    function setOccupantsCount($server, $node, $count)
    {
        $this->_sql = '
            update info
            set occupants = :occupants
            where
                info.server = :server
                and info.node = :node';

        $this->prepare(
            'Info',
            [
                'server' => $server,
                'node' => $node,
                'occupants' => $count
            ]
        );

        return $this->run('Info');
    }

    function get($server, $node)
    {
        $this->_sql = '
            select * from info
            left outer join (
                select node, count(node) as num from postn
                where origin = :server
                group by node) as p
            on p.node = info.node
            where
                info.server = :server
                and info.node = :node';

        $this->prepare(
            'Info',
            [
                'server' => $server,
                'node' => $node
            ]
        );

        return $this->run('Info', 'item');
    }

    function getItems($server = false, $limitf = false, $limitr = false, $withPostsOnly = false, $host = false)
    {
        $this->_sql = '
            select *, postn.published from info
            left outer join (
                select node, count(node) as num from postn
            ';

        if($server) {
            $this->_sql .= '
                where origin = :server';
        }

        $this->_sql .= '
                group by node) as p
            on p.node = info.node
            left outer join (
                select origin, node, max(published) as published
                from postn
                group by origin, node
            ) as postn on postn.origin = info.server
              and postn.node = info.node
            left outer join (
                select node, count(node) as sub from subscription';

        if($server) {
            $this->_sql .= '
                where server = :server';
        }

        $this->_sql .= '
                group by node
            ) as sub
              on sub.node = info.node';

        if($this->_user) {
            $this->_sql .= '
                left outer join (select server, node, subscription from subscription where jid = :jid)
                as s on s.server = info.server
                and s.node = info.node';
        }

        $this->_sql .= '
            where info.node != \'\'
                and info.category = \'pubsub\'
                and info.node not like \'urn:xmpp:microblog:0:comments%\'
                and info.node not like \'/%\'
                and info.node != \'urn:xmpp:microblog:0\'';

        if($server) {
            $this->_sql .= '
                and info.server = :server';
        }

        if($host) {
            $this->_sql .= ' and info.server like \'%.' . $host . '\'';
        }

        if($withPostsOnly) {
            $this->_sql .= '
                and postn.published is not null';
        }

        $this->_sql .= '
            order by postn.published is null, postn.published desc, name, info.node
            ';

        if($limitr) {
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;
        }

        $params = [];
        if($server) $params['server'] = $server;
        if($this->_user) $params['subscription.jid'] = $this->_user;

        $this->prepare('Info', $params);

        return $this->run('Server');
    }

    function getJid($server)
    {
        $this->_sql = '
            select info.*, caps.name from info
            left outer join caps on caps.node = info.server
            where
                info.server = :server
                and info.node = \'\'';

        $this->prepare(
            'Info',
            [
                'server' => $server
            ]
        );

        return $this->run('Info', 'item');
    }

    function getTopConference($limit = 10, $host = false)
    {
        $this->_sql = '
            select * from info
            where category = \'conference\'
            and server not in (
                select conference
                from conference where jid = :jid)
            and mucpublic = true
            and mucpersistent = true
            and server like \'%@%\'';

        if($host) {
            $this->_sql .= ' and server like \'%@%.' . $host . '\'';
        }

        $this->_sql .= '
            order by occupants desc';

        $this->_sql .= ' limit '.(int)$limit;

        $this->prepare(
            'Info',
            [
                'conference.jid' => $this->_user
            ]
        );

        return $this->run('Info');
    }

    function getSharedItems($jid)
    {
        $this->_sql = '
            select * from info
            where (server, node) in (
                select server, node from sharedsubscription
                where jid = :jid
            )';

        $this->prepare(
            'SharedSubscription',
            [
                'jid' => $jid
            ]
        );

        return $this->run('Info');
    }

    function getCommunitiesServers()
    {
        $this->_sql = '
            select info.server, counter.number, caps.name from info
            left outer join caps on caps.node = info.server
            left outer join (
                select server,
                count(*) as number from info
                where node != \'\'
                and node not like \'urn:xmpp:microblog:0:comments%\'
                and node not like \'/%\'
                group by server)
                as counter on info.server = counter.server
            where caps.category = \'pubsub\'
            and caps.type = \'service\'
            group by info.server, counter.number, caps.name
            order by counter.number is null, counter.number desc';

        $this->prepare(
            'Info'
        );

        return $this->run('Server');
    }

    function getConference($server)
    {
        $this->_sql = '
            select * from info
            where server = :server
            and category = \'conference\'
            and type = \'text\'';

        $this->prepare(
            'Info',
            [
                'server' => $server
            ]
        );

        return $this->run('Info', 'item');
    }

    function getGateways($server)
    {
        $this->_sql = '
            select * from info
            where server = :server
            and category = \'gateway\'';

        $this->prepare(
            'Info',
            [
                'server' => $server
            ]
        );

        return $this->run('Info');
    }

    function deleteItems($server)
    {
        $this->_sql = '
            delete from info
            where server = :server';

        $this->prepare(
            'Info',
            [
                'server' => $server
            ]
        );

        return $this->run('Info');
    }

    function delete($server, $item)
    {
        $this->_sql = '
            delete from info
            where server = :server
                and node = :node';

        $this->prepare(
            'Info',
            [
                'server' => $server,
                'node' => $item
            ]
        );

        return $this->run('Info');
    }
}
