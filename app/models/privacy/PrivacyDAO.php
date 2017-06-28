<?php

namespace Modl;

class PrivacyDAO extends SQL
{
    function get($key)
    {
        $this->_sql = '
            select * from privacy
            where
                pkey = :pkey';

        $this->prepare(
            'Privacy',
            [
                'pkey' => $key
            ]
        );

        return $this->run('Privacy', 'item');
    }
}
