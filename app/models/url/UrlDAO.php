<?php

namespace Modl;

class UrlDAO extends SQL
{
    public function get($hash)
    {
        $this->_sql = '
            select * from url
            where hash = :hash';

        $this->prepare(
            'Url',
            [
                'hash' => $hash
            ]
        );

        return $this->run('Url', 'item');
    }
}
