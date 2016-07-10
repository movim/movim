<?php

namespace modl;

class ItemDAO extends SQL
{
    function set(Item $item, $insert_only = false) {
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
                array(
                    'name'          => $item->name,
                    'created'       => $item->created,
                    'updated'       => $item->updated,
                    'server'        => $item->server,
                    'jid'           => $item->jid,
                    'node'          => $item->node,
                    'creator'       => $item->creator,
                    'description'   => $item->description,
                    'logo'          => $item->logo
                )
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
                array(
                    'name'          => $item->name,
                    'creator'       => $item->creator,
                    'created'       => $item->created,
                    'updated'       => $item->updated,
                    'server'        => $item->server,
                    'jid'           => $item->jid,
                    'node'          => $item->node,
                    'description'   => $item->description,
                    'logo'          => $item->logo
                )
            );

            $this->run('Item');
        }
    }

    function getServers() {
        $this->_sql = '
            select server, count(node) as number
            from item
            where node not like :node
            group by server
            order by number desc';

        $this->prepare(
            'Item',
            array(
                'node' => 'urn:xmpp:microblog:0:comments%'
            )
        );

        return $this->run('Server');
    }

    function getGroupServers() {
        $this->_sql = '
            select server, count(item.node) as number, caps.name
            from item
            left outer join caps on caps.node = item.server
            where item.node not like :node
            and item.node != \'\'
            and caps.category = \'pubsub\'
            and caps.type = \'service\'
            and item.node not like \'/%\'
            group by server, caps.name
            order by number desc';

        $this->prepare(
            'Item',
            array(
                'node' => 'urn:xmpp:microblog:0:comments%',
            )
        );

        return $this->run('Server');
    }

    function getItems($server) {
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
            group by node) as sub
            on sub.node = item.node
            left outer join (select server, node, subscription from subscription where jid = :node)
                as s on s.server = item.server
                and s.node = item.node
            where item.server = :server
                and item.node != \'\'
                and item.node not like \'/%\'
            order by name, item.node
            ';

        $this->prepare(
            'Item',
            array(
                // Dirty hack, using node param to inject the session key
                'node' => $this->_user,
                'server' => $server
            )
        );

        return $this->run('Item');
    }

    function getGateways($server) {
        $this->_sql = '
            select * from item
            left outer join caps on caps.node = item.jid
            where server = :server
            and category = \'gateway\'';

        $this->prepare(
            'Item',
            array(
                'server' => $server
            )
        );

        return $this->run('Item');
    }

    function getConference($server) {
        $this->_sql = '
            select item.* from item
            join caps on caps.node = item.jid
            where server = :server
            and category = \'conference\'
            and type = \'text\'';

        $this->prepare(
            'Item',
            array(
                'server' => $server
            )
        );

        return $this->run('Item', 'item');
    }

    function getUpload($server) {
        $this->_sql = '
            select * from item
            left outer join caps on caps.node = item.jid
            where server = :server
            and features like \'%urn:xmpp:http:upload%\'';

        $this->prepare(
            'Item',
            array(
                'server' => $server
            )
        );

        return $this->run('Item', 'item');
    }

    function getUpdatedItems($limitf = false, $limitr = false) {
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
            array(
                'node'      => 'urn:xmpp:microblog%'
            )
        );

        return $this->run('Item');
    }

    function deleteItems($server) {
        $this->_sql = '
            delete from item
            where server= :server';

        $this->prepare(
            'Item',
            array(
                'server' => $server
            )
        );

        return $this->run('Item');
    }

    function deleteItem($server, $item) {
        $this->_sql = '
            delete from item
            where server = :server
                and node = :node';

        $this->prepare(
            'Item',
            array(
                'server' => $server,
                'node' => $item
            )
        );

        return $this->run('Item');
    }

    function getItem($server, $item) {
        $this->_sql = '
            select * from item
            where
                node = :node
                and server = :server';

        $this->prepare(
            'Item',
            array(
                'node' => $item,
                'server' => $server
            )
        );

        return $this->run('Item', 'item');
    }
}
