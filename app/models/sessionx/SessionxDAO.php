<?php

namespace modl;

class SessionxDAO extends SQL
{
    function init(Sessionx $s)
    {
        $this->_sql = '
            update sessionx
            set username    = :username,
                jid         = :jid,
                hash        = :hash,
                resource    = :resource,
                host        = :host,
                config      = :config,
                active      = :active,
                start       = :start,
                timestamp   = :timestamp
            where session = :session';

        $this->prepare(
            'Sessionx',
            [
                'session'   => $s->session,
                'username'  => $s->username,
                'jid'       => $s->username.'@'.$s->host,
                'hash'      => $s->hash,
                'resource'  => $s->resource,
                'host'      => $s->host,
                'config'    => $s->config,
                'active'    => $s->active,
                'start'     => $s->start,
                'timestamp' => $s->timestamp
            ]
        );

        $this->run('Sessionx');

        if(!$this->_effective) {
            $this->_sql = '
                insert into sessionx
                (session,
                 username,
                 jid,
                 hash,
                 resource,
                 host,
                 config,
                 active,
                 start,
                 timestamp)
                values
                (:session,
                 :username,
                 :jid,
                 :hash,
                 :resource,
                 :host,
                 :config,
                 :active,
                 :start,
                 :timestamp)';

            $this->prepare(
                'Sessionx',
                [
                    'session'   => $s->session,
                    'username'  => $s->username,
                    'jid'       => $s->username.'@'.$s->host,
                    'hash'      => $s->hash,
                    'resource'  => $s->resource,
                    'host'      => $s->host,
                    'config'    => $s->config,
                    'active'    => $s->active,
                    'start'     => $s->start,
                    'timestamp' => $s->timestamp
                ]
            );

            $this->run('Sessionx');
        }
    }

    function update($session, $key, $value)
    {
        $this->_sql = '
            update sessionx
            set
                '.$key.'  = :'.$key.',
                timestamp = :timestamp
            where
                session = :session';

        $this->prepare(
            'Sessionx',
            [
                'session'   => $session,
                $key        => $value,
                'timestamp' => date(SQL::SQL_DATE)
            ]
        );

        $this->run('Sessionx');
    }

    function get($session)
    {
        $this->_sql = '
            select * from sessionx
            where
                session = :session';

        $this->prepare(
            'Sessionx',
            [
                'session' => $session
            ]
        );

        return $this->run('Sessionx', 'item');
    }

    function getHash($hash)
    {
        $this->_sql = '
            select * from sessionx
            where
                hash = :hash';

        $this->prepare(
            'Sessionx',
            [
                'hash' => $hash
            ]
        );

        return $this->run('Sessionx', 'item');
    }

    function delete($session)
    {
        $this->_sql = '
            delete from sessionx
            where
                session = :session';

        $this->prepare(
            'Sessionx',
            [
                'session' => $session
            ]
        );

        return $this->run('Sessionx');
    }

    function deleteEmpty()
    {
        $this->_sql = '
            delete from sessionx
            where active = 0
                and start < :timestamp';

        $this->prepare(
            'Sessionx',
            ['timestamp' => date(SQL::SQL_DATE, time()-60)]
        );

        return $this->run('Sessionx');
    }

    function clear()
    {
        $this->_sql = '
            truncate table sessionx';

        $this->prepare(
            'Sessionx',
            []
        );

        $this->run('Sessionx');
    }

    function getAll()
    {
        $this->_sql = '
            select * from sessionx order by start desc';

        $this->prepare(
            'Sessionx',
            []
        );

        return $this->run('Sessionx');
    }
}
