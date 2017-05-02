<?php

namespace Modl;

class KeyDAO extends SQL
{
    function get($id)
    {
        $this->_sql = '
            select * from key
            where
                session = :session
            and id    = :id';

        $this->prepare(
            'Key',
            [
                'session' => $this->_user,
                'id' => $id
            ]
        );

        return $this->run('Key', 'item');
    }

    function set(Key $key)
    {
        $this->_sql = '
            update key
                set data = :data,
                    timestamp = :timestamp
                where session = :session
                and id = :id';

        $this->prepare(
            'Key',
            [
                'session'   => $this->_user,
                'data'      => $key->data,
                'timestamp' => date(\Modl\SQL::SQL_DATE),
                'id'        => $key->id
            ]
        );

        $this->run('Key');

        if(!$this->_effective) {
            $this->_sql = '
                insert into key
                (session, id, data, timestamp)
                values (:session, :id, :data, :timestamp)';

            $this->prepare(
                'Key',
                [
                    'session'   => $this->_user,
                    'id'        => $key->id,
                    'data'      => $key->data,
                    'timestamp' => date(\Modl\SQL::SQL_DATE)
                ]
            );

            return $this->run('Key');
        }
    }

    function delete()
    {
        $this->_sql = '
            delete from key
            where session = :session';

        $this->prepare(
            'Key',
            [
                'session' => $this->_user
            ]
        );

        return $this->run('Key');
    }
}
