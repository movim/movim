<?php

namespace Modl;

class EncryptedPassDAO extends SQL
{
    function get($id)
    {
        $this->_sql = '
            select * from encryptedpass
            where
                session = :session
            and id    = :id';

        $this->prepare(
            'EncryptedPass',
            [
                'session' => $this->_user,
                'id' => $id
            ]
        );

        return $this->run('EncryptedPass', 'item');
    }

    function set($key)
    {
        $this->_sql = '
            update encryptedpass
                set data = :data,
                    timestamp = :timestamp
                where session = :session
                and id = :id';

        $this->prepare(
            'EncryptedPass',
            [
                'session'   => $this->_user,
                'data'      => $key->data,
                'timestamp' => date(\Modl\SQL::SQL_DATE),
                'id'        => $key->id
            ]
        );

        $this->run('EncryptedPass');

        if(!$this->_effective) {
            $this->_sql = '
                insert into encryptedpass
                (session, id, data, timestamp)
                values (:session, :id, :data, :timestamp)';

            $this->prepare(
                'EncryptedPass',
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
            delete from encryptedpass
            where session = :session';

        $this->prepare(
            'EncryptedPass',
            [
                'session' => $this->_user
            ]
        );

        return $this->run('EncryptedPass');
    }
}
