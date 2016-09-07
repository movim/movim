<?php

namespace modl;

class RosterLinkDAO extends SQL {
    function set(RosterLink $r) {
        $this->_sql = '
            insert into rosterlink
            (
                session,
                jid,
                rostername,
                rosterask,
                rostersubscription,
                groupname)
                values (
                    :session,
                    :jid,
                    :rostername,
                    :rosterask,
                    :rostersubscription,
                    :groupname
                    )';

        $this->prepare(
            'RosterLink',
            array(
                'session'       => $this->_user,
                'jid'           => $r->jid,
                'rostername'    => $r->rostername,
                'rosterask'     => $r->rosterask,
                'rostersubscription'     => $r->rostersubscription,
                'groupname'     => $r->groupname
            )
        );

        return $this->run('RosterLink');
    }

    function setList($arr) {
        $this->_sql = '
            insert into rosterlink
            (
                session,
                jid,
                rostername,
                rosterask,
                rostersubscription,
                groupname)
                values
            ';

        $i = 0;
        $params = [];

        foreach($arr as $r) {
            $this->_sql .= "
                (
                :session_$i,
                :jid_$i,
                :rostername_$i,
                :rosterask_$i,
                :rostersubscription_$i,
                :groupname_$i
                ),";

            $params = array_merge(
                $params,
                array(
                    "session_$i"       => $this->_user,
                    "jid_$i"           => $r->jid,
                    "rostername_$i"    => $r->rostername,
                    "rosterask_$i"     => $r->rosterask,
                    "rostersubscription_$i"     => $r->rostersubscription,
                    "groupname_$i"     => $r->groupname
                )
            );

            $i++;
        }

        $this->_sql = substr($this->_sql, 0, -1);

        $this->prepare(
            'RosterLink',
            $params
        );

        return $this->run('RosterLink');
    }

    function update(RosterLink $r) {
        $this->_sql = '
            update rosterlink
            set rostername  = :rostername,
                rosterask   = :rosterask,
                rostersubscription = :rostersubscription,
                groupname   = :groupname
            where session   = :session
                and jid     = :jid';

        $this->prepare(
            'RosterLink',
            array(
                'session'       => $this->_user,
                'jid'           => $r->jid,
                'rostername'    => $r->rostername,
                'rosterask'     => $r->rosterask,
                'rostersubscription'     => $r->rostersubscription,
                'groupname'     => $r->groupname
            )
        );

        return $this->run('RosterLink');
    }

    function setNow(RosterLink $r) {
        $this->update($r);

        if(!$this->_effective)
            $this->set($r);
    }

    function get($jid) {
        $this->_sql = '
            select *
            from rosterlink
            where session=:session
                and jid = :jid';

        $this->prepare(
            'RosterLink',
            array(
                'session' => $this->_user,
                'jid' => $jid,
            )
        );

        return $this->run('RosterLink', 'item');
    }

    function getGroups() {
        $this->_sql = '
            select groupname
            from rosterlink
            where session = :session
            group by groupname';

        $this->prepare(
            'RosterLink',
            array(
                'session' => $this->_user
            )
        );

        $results = $this->run('RosterLink');

        if(is_array($results)) {
            $arr = [];

            foreach($results as $r)
                array_push($arr, $r->groupname);

            return $arr;
        } else {
            return false;
        }
    }

    function getRoster($to = null) {
        if($to != null)
            $session = $to;
        else
            $session = $this->_user;

        $this->_sql = '
            select *
            from rosterlink
            where session=:session';

        $this->prepare(
            'RosterLink',
            array(
                'session' => $session
            )
        );

        return $this->run('RosterLink');
    }

    function clearRosterLink() {
        $this->_sql = '
            delete from rosterlink
            where session = :session';
        $this->prepare(
            'RosterLink',
            array(
                'session' => $this->_user
            )
        );

        return $this->run('RosterLink');
    }

    function delete($jid) {
        $this->_sql = '
            delete from rosterlink
            where session = :session
            and jid = :jid';

        $this->prepare(
            'RosterLink',
            array(
                'session' => $this->_user,
                'jid' => $jid
            )
        );

        return $this->run('RosterLink');
    }
}
