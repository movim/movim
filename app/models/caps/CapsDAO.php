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
}
