<?php

namespace Modl;

class CacheDAO extends SQL
{
    function get($key)
    {
        $this->_sql = '
            select * from cache
            where
                session = :session
            and name    = :name';

        $this->prepare(
            'Cache',
            [
                'session' => $this->_user,
                'name' => $key
            ]
        );

        return $this->run('Cache', 'item');
    }
}
