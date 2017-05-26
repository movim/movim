<?php

namespace Modl;

class InviteDAO extends SQL
{
    function set(Invite $i)
    {
        $this->_sql = '
            insert into invite
            (code, jid, resource, created)
            values (:code,:jid,:resource,:created)';

        $this->prepare(
            'Invite',
            [
                'code'      => $i->code,
                'jid'       => $i->jid,
                'resource'  => $i->resource,
                'created'   => date(SQL::SQL_DATE),
            ]
        );

        $this->run('Invite');
    }

    function getCode($jid, $resource)
    {
        $this->_sql = '
            select * from invite
            where
                jid = :jid
            and resource = :resource';

        $this->prepare(
            'Invite',
            [
                'jid'       => $jid,
                'resource'  => $resource
            ]
        );

        return $this->run('Invite', 'item');
    }

    function get($code)
    {
        $this->_sql = '
            select * from invite
            where
                code = :code';

        $this->prepare(
            'Invite',
            [
                'code' => $code
            ]
        );

        return $this->run('Invite', 'item');
    }
}
