<?php

namespace Modl;

class SharedSubscriptionDAO extends SQL
{
    function set(SharedSubscription $s)
    {
        $this->_sql = '
            update sharedsubscription
            set title = :title
            where jid = :jid
                and server = :server
                and node = :node';

        $this->prepare(
            'SharedSubscription',
            [
                'title' => $s->title,
                'jid'   => $s->jid,
                'server'=> $s->server,
                'node'  => $s->node
            ]
        );

        $this->run('SharedSubscription');

        if(!$this->_effective) {
            $this->_sql = '
                insert into sharedsubscription
                (jid, server, node, title)
                values (:jid, :server, :node, :title)';

            $this->prepare(
                'SharedSubscription',
                [
                    'title' => $s->title,
                    'jid'   => $s->jid,
                    'server'=> $s->server,
                    'node'  => $s->node
                ]
            );

            $this->run('SharedSubscription');
        }
    }

    function deleteJid($jid)
    {
        $this->_sql = '
            delete from sharedsubscription
            where jid = :jid';

        $this->prepare(
            'SharedSubscription',
            [
                'jid' => $jid,
            ]
        );

        return $this->run('SharedSubscription');
    }
}
