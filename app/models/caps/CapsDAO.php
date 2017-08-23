<?php

namespace Modl;

class CapsDAO extends SQL
{
    function get($node)
    {
        $this->_sql = '
            select * from caps
            where
                node = :node';

        $this->prepare(
            'Caps',
            [
                'node' => $node
            ]
        );

        return $this->run('Caps', 'item');
    }

    function getClients()
    {
        $this->_sql = '
            select * from caps
            where category = :category
            order by name';

        $this->prepare(
            'Caps',
            [
                'category' => 'client'
            ]
        );

        return $this->run('Caps');
    }

    function getServers()
    {
        $this->_sql = '
            select * from caps
            where category = :category';

        $this->prepare(
            'Caps',
            [
                'category' => 'server'
            ]
        );

        return $this->run('Caps');
    }

    function getUpload($server)
    {
        $this->_sql = '
            select * from caps
            where node like \'%'.$server.'%\'
            and features like \'%urn:xmpp:http:upload%\'';

        $this->prepare(
            'Caps'
        );

        return $this->run('Caps', 'item');
    }

    function getComments($server)
    {
        $this->_sql = '
            select * from caps
            where node = \'comments.'.$server.'\'
            and category = \'pubsub\'
            and type = \'service\'';

        $this->prepare(
            'Caps'
        );

        return $this->run('Caps', 'item');
    }

    function getAll()
    {
        $this->_sql = '
            select * from caps';

        $this->prepare(
            'Caps'
        );

        return $this->run('Caps');
    }
}
