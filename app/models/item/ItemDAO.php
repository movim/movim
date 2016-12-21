<?php

namespace modl;

class ItemDAO extends SQL
{
    function set(Item $item, $insert_only = false)
    {
        if(!$insert_only) {
            $this->_sql = '
                update item
                set name   = :name,
                    creator = :creator,
                    created = :created,
                    updated = :updated,
                    description = :description,
                    logo = :logo
                where server = :server
                    and jid  = :jid
                    and node = :node';

            $this->prepare(
                'Item',
                [
                    'name'          => $item->name,
                    'created'       => $item->created,
                    'updated'       => $item->updated,
                    'server'        => $item->server,
                    'jid'           => $item->jid,
                    'node'          => $item->node,
                    'creator'       => $item->creator,
                    'description'   => $item->description,
                    'logo'          => $item->logo
                ]
            );

            $this->run('Item');
        }

        if(!$this->_effective || $insert_only) {
            $this->_sql = '
                insert into item
                (server,
                creator,
                node,
                jid,
                name,
                created,
                updated,
                description,
                logo
                )
                values(
                    :server,
                    :creator,
                    :node,
                    :jid,
                    :name,
                    :created,
                    :updated,
                    :description,
                    :logo
                    )';

            $this->prepare(
                'Item',
                [
                    'name'          => $item->name,
                    'creator'       => $item->creator,
                    'created'       => $item->created,
                    'updated'       => $item->updated,
                    'server'        => $item->server,
                    'jid'           => $item->jid,
                    'node'          => $item->node,
                    'description'   => $item->description,
                    'logo'          => $item->logo
                ]
            );

            $this->run('Item');
        }
    }

    function getServers()
    {
        $this->_sql = '
            select server, count(node) as number
            from item
            where node not like :node
            group by server
            order by number desc';

        $this->prepare(
            'Item',
            [
                'node' => 'urn:xmpp:microblog:0:comments%'
            ]
        );

        return $this->run('Server');
    }

    function getGroupServers()
    {
        $this->_sql = '
            select item.jid as server, counter.number, caps.name from item
            left outer join caps on caps.node = item.jid
            left outer join (
                select jid,
                count(*) as number from item
                where node != \'\'
                and node not like \'urn:xmpp:microblog:0:comments%\'
                group by jid)
                as counter on item.jid = counter.jid
            where caps.category = \'pubsub\'
            and caps.type = \'service\'
            group by item.jid, counter.number, caps.name
            order by counter.number is null, counter.number desc';

        $this->prepare(
            'Item'
        );

        return $this->run('Server');
    }

    function getItems($server = false, $limitf = false, $limitr = false)
    {
        $this->_sql = '
            select *, postn.published from item
            left outer join (
                select node, count(node) as num from postn
            ';

        if($server) {
            $this->_sql .= '
                where origin = :server';
        }

        $this->_sql .= '
                group by node) as p
            on p.node = item.node
            left outer join (
                select origin, node, max(published) as published
                from postn
                group by origin, node
            ) as postn on postn.origin = item.server
              and postn.node = item.node
            left outer join (
                select node, count(node) as sub from subscription';

        if($server) {
            $this->_sql .= '
                where server = :server';
        }

        $this->_sql .= '
                group by node
            ) as sub
              on sub.node = item.node
            left outer join (select server, node, subscription from subscription where jid = :jid)
                as s on s.server = item.server
                and s.node = item.node
            where item.node != \'\'
                and item.node not like \'urn:xmpp:microblog:0:comments%\'
                and item.node != \'urn:xmpp:microblog:0\'';

        if($server) {
            $this->_sql .= '
                and item.server = :server';
        }

        $this->_sql .= '
            order by postn.published is null, postn.published desc, name, item.node
            ';

        if($limitr) {
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;
        }

        if($server) {
            $this->prepare(
                'Item',
                [
                    'subscription.jid' => $this->_user,
                    'server' => $server
                ]
            );
        } else {
            $this->prepare(
                'Item',
                [
                    'subscription.jid' => $this->_user
                ]
            );
        }

        return $this->run('Server');
    }

    function getGateways($server)
    {
        $this->_sql = '
            select * from item
            left outer join caps on caps.node = item.jid
            where server = :server
            and category = \'gateway\'';

        $this->prepare(
            'Item',
            [
                'server' => $server
            ]
        );

        return $this->run('Item');
    }

    function getConference($server)
    {
        $this->_sql = '
            select item.* from item
            join caps on caps.node = item.jid
            where server = :server
            and category = \'conference\'
            and type = \'text\'';

        $this->prepare(
            'Item',
            [
                'server' => $server
            ]
        );

        return $this->run('Item', 'item');
    }

    function getUpload($server)
    {
        $this->_sql = '
            select * from item
            left outer join caps on caps.node = item.jid
            where server = :server
            and features like \'%urn:xmpp:http:upload%\'';

        $this->prepare(
            'Item',
            [
                'server' => $server
            ]
        );

        return $this->run('Item', 'item');
    }

    function getUpdatedItems($limitf = false, $limitr = false)
    {
        $this->_sql = '
            select * from item natural join (
                select distinct node, max(updated) as num from postn
                where node not like :node
                group by node
                order by node) as post
                order by num desc
            ';

        if($limitr)
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;

        $this->prepare(
            'Item',
            [
                'node'      => 'urn:xmpp:microblog%'
            ]
        );

        return $this->run('Item');
    }

    function deleteItems($server)
    {
        $this->_sql = '
            delete from item
            where server= :server';

        $this->prepare(
            'Item',
            [
                'server' => $server
            ]
        );

        return $this->run('Item');
    }

    function deleteItem($server, $item)
    {
        $this->_sql = '
            delete from item
            where server = :server
                and node = :node';

        $this->prepare(
            'Item',
            [
                'server' => $server,
                'node' => $item
            ]
        );

        return $this->run('Item');
    }

    function getJid($jid)
    {
        $this->_sql = '
            select * from item
            where
                jid = :jid
                and node = \'\'';

        $this->prepare(
            'Item',
            [
                'jid' => $jid
            ]
        );

        return $this->run('Item', 'item');
    }

    function getItem($server, $item)
    {
        $this->_sql = '
            select * from item
            left outer join (
                select node, count(node) as num from postn
                where origin = :server
                group by node) as p
            on p.node = item.node
            left outer join (
                select node, count(node) as sub from subscription
                where server = :server
                group by node
            ) as sub
              on sub.node = item.node
            where
                item.node = :node
                and server = :server';

        $this->prepare(
            'Item',
            [
                'node' => $item,
                'server' => $server
            ]
        );

        return $this->run('Item', 'item');
    }
}
