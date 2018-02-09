<?php

namespace Modl;

class SettingDAO extends SQL
{
    function set($s)
    {
        $this->_sql = '
            update setting
            set language   = :language,
                cssurl     = :cssurl,
                nsfw       = :nsfw,
                nightmode  = :nightmode
            where session  = :session';

        $this->prepare(
            'Setting',
            [
                'language'  => $s->language,
                'cssurl'    => $s->cssurl,
                'nsfw'      => $s->nsfw,
                'nightmode' => $s->nightmode,
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
                    nightmode,
                    session
                )
                values
                (
                    :language,
                    :cssurl,
                    :nsfw,
                    :nightmode,
                    :session
                )
                ';

            $this->prepare(
                'Setting',
                [
                    'language'  => $s->language,
                    'cssurl'    => $s->cssurl,
                    'nsfw'      => $s->nsfw,
                    'nightmode' => $s->nightmode,
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
