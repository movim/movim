<?php

namespace Modl;

class SettingDAO extends SQL
{
    function get()
    {
        $this->_sql = '
            select * from setting
            where session = :session';

        $this->prepare(
            'Setting',
            ['session' => $this->_user]
        );

        return $this->run('Setting', 'item');
    }
}
