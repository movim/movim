<?php

namespace Modl;

class SharedSubscriptionDAO extends SQL
{
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
