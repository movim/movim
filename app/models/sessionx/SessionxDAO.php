<?php

namespace modl;

class SessionxDAO extends SQL {
    function init(Sessionx $s) {\movim_log($s->start);
        $this->_sql = '
            update sessionx
            set username    = :username,
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
            array(
                'session'   => $s->session,
                'username'  => $s->username,
                'hash'      => $s->hash,
                'resource'  => $s->resource,
                'host'      => $s->host,
                'config'    => $s->config,
                'active'    => $s->active,
                'start'     => $s->start,
                'timestamp' => $s->timestamp
                )
        );

        $this->run('Sessionx');

        if(!$this->_effective) {
            $this->_sql = '
                insert into sessionx
                (session,
                 username,
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
                 :hash,
                 :resource,
                 :host,
                 :config,
                 :active,
                 :start,
                 :timestamp)';

            $this->prepare(
                'Sessionx',
                array(
                    'session'   => $s->session,
                    'username'  => $s->username,
                    'hash'      => $s->hash,
                    'resource'  => $s->resource,
                    'host'      => $s->host,
                    'config'    => $s->config,
                    'active'    => $s->active,
                    'start'     => $s->start,
                    'timestamp' => $s->timestamp
                )
            );

            $this->run('Sessionx');
        }
    }

    function update($session, $key, $value) {
        $this->_sql = '
            update sessionx
            set
                '.$key.'  = :'.$key.',
                timestamp = :timestamp
            where
                session = :session';

        $this->prepare(
            'Sessionx',
            array(
                'session'   => $session,
                $key        => $value,
                'timestamp' => date(DATE_ISO8601)
            )
        );

        $this->run('Sessionx');
    }

    function get($session) {
        $this->_sql = '
            select * from sessionx
            where
                session = :session';

        $this->prepare(
            'Sessionx',
            array(
                'session' => $session
            )
        );

        return $this->run('Sessionx', 'item');
    }

    function getHash($hash) {
        $this->_sql = '
            select * from sessionx
            where
                hash = :hash';

        $this->prepare(
            'Sessionx',
            array(
                'hash' => $hash
            )
        );

        return $this->run('Sessionx', 'item');
    }

    function delete($session) {
        $this->_sql = '
            delete from sessionx
            where
                session = :session';

        $this->prepare(
            'Sessionx',
            array(
                'session' => $session
            )
        );

        return $this->run('Sessionx');
    }

    function deleteEmpty() {
        $this->_sql = '
            delete from sessionx
            where active = 0
                and start < :timestamp';

        $this->prepare(
            'Sessionx',
            ['timestamp' => date(DATE_ISO8601, time()-60)]
        );

        return $this->run('Sessionx');
    }

    function clear() {
        $this->_sql = '
            truncate table sessionx';

        $this->prepare(
            'Sessionx',
            array(
            )
        );

        $this->run('Sessionx');
    }

    function getAll() {
        $this->_sql = '
            select * from sessionx';

        $this->prepare(
            'Sessionx',
            []
        );

        return $this->run('Sessionx');
    }
}
