<?php

namespace Modl;

class ConferenceDAO extends SQL
{
    function get($conference)
    {
        $this->_sql = '
            select * from conference
            where jid       = :jid
              and conference= :conference';

        $this->prepare(
            'Conference',
            [
                'jid' => $this->_user,
                'conference' => $conference,
            ]
        );

        return $this->run('Conference', 'item');
    }

    function getAll()
    {
        $this->_sql = '
            select * from conference
            where jid = :jid
            order by conference';

        $this->prepare(
            'Conference',
            [
                'jid' => $this->_user
            ]
        );

        return $this->run('Conference');
    }

    function delete()
    {
        $this->_sql = '
            delete from conference
            where jid = :jid';

        $this->prepare(
            'Subscription',
            [
                'jid' => $this->_user
            ]
        );

        return $this->run('Conference');
    }

    function deleteNode($conference)
    {
        $this->_sql = '
            delete from conference
            where jid       = :jid
              and conference= :conference';

        $this->prepare(
            'Conference',
            [
                'jid' => $this->_user,
                'conference' => $conference
            ]
        );

        return $this->run('Conference');
    }
}
