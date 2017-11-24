<?php

namespace Modl;

class SharedSubscriptionDAO extends SQL
{
    function getAll($server, $node)
    {
        $this->_sql = '
            select * from contact
            where jid in (
                select jid from sharedsubscription
                where server = :server
                    and node = :node
            )';

        $this->prepare(
            'Subscription',
            [
                'server' => $server,
                'node' => $node
            ]
        );

        return $this->run('Contact');
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
