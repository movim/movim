<?php

namespace Modl;

class SettingDAO extends SQL
{
    function set(Setting $s)
    {
        $this->_sql = '
            update setting
            set language   = :language,
                cssurl     = :cssurl,
                nsfw       = :nsfw
            where session  = :session';

        $this->prepare(
            'Setting',
            [
                'language'  => $s->language,
                'cssurl'    => $s->cssurl,
                'nsfw'      => $s->nsfw,
                'session'   => $this->_user
            ]
        );

        $this->run('Config');

        if(!$this->_effective) {
            $this->_sql = '
                insert into setting
                (
                    language,
                    cssurl,
                    nsfw,
                    session
                )
                values
                (
                    :language,
                    :cssurl,
                    :nsfw,
                    :session
                )
                ';

            $this->prepare(
                'Setting',
                [
                    'language'  => $s->language,
                    'cssurl'    => $s->cssurl,
                    'nsfw'      => $s->nsfw,
                    'session'   => $this->_user
                ]
            );

            $this->run('Setting');
        }
    }

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
