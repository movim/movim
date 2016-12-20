<?php

namespace Modl;

class ContactDAO extends SQL
{
    function __construct()
    {
        parent::__construct();
    }

    function get($jid = null, $empty = false)
    {
        $this->_sql = '
            select *, privacy.value as privacy from contact
            left outer join privacy
                on contact.jid = privacy.pkey
            where jid = :jid';

        if($jid == null) $jid = $this->_user;

        $this->prepare(
            'Contact',
            [
                'jid' => $jid
            ]
        );

        $contact = $this->run('Contact', 'item');

        // If we cannot find the contact
        if($contact == null && $empty == false) {
            $contact = new Contact;
            $contact->jid = $jid;
            return $contact;
        }

        return $contact;
    }

    function set(Contact $contact)
    {
        if(!isset($contact->created)) {
            $contact->created = date(SQL::SQL_DATE);
        }

        $this->_sql = '
            update contact
            set fn      = :fn,
                name    = :name,
                date    = :date,
                url     = :url,

                email   = :email,

                adrlocality     = :adrlocality,
                adrpostalcode   = :adrpostalcode,
                adrcountry      = :adrcountry,

                gender          = :gender,
                marital         = :marital,

                description     = :description,

                mood            = :mood,

                activity        = :activity,

                nickname        = :nickname,

                tuneartist      = :tuneartist,
                tunelenght      = :tunelenght,
                tunerating      = :tunerating,
                tunesource      = :tunesource,
                tunetitle       = :tunetitle,
                tunetrack       = :tunetrack,

                loclatitude     = :loclatitude,
                loclongitude    = :loclongitude,
                localtitude     = :localtitude,
                loccountry      = :loccountry,
                loccountrycode  = :loccountrycode,
                locregion       = :locregion,
                locpostalcode   = :locpostalcode,
                loclocality     = :loclocality,
                locstreet       = :locstreet,
                locbuilding     = :locbuilding,
                loctext         = :loctext,
                locuri          = :locuri,
                loctimestamp    = :loctimestamp,
                twitter         = :twitter,
                skype           = :skype,
                yahoo           = :yahoo,
                avatarhash      = :avatarhash,
                created         = :created,
                updated         = :updated
            where contact.jid = :jid';

        $this->prepare(
            'Contact',
            [
                'fn'    => $contact->fn,
                'name'  => $contact->name,
                'date'  => $contact->date,
                'url'   => $contact->url,

                'email'  => $contact->email,

                'adrlocality'    => $contact->adrlocality,
                'adrpostalcode'  => $contact->adrpostalcode,
                'adrcountry'     => $contact->adrcountry,

                'gender'   => $contact->gender,
                'marital'  => $contact->marital,

                'description'    => $contact->description,

                // User Mood (contain serialized array) - XEP 0107
                'mood'  => $contact->mood,

                // User Activity (contain serialized array) - XEP 0108
                'activity'  => $contact->activity,

                // User Nickname - XEP 0172
                'nickname'  => $contact->nickname,

                // User Tune - XEP 0118
                'tuneartist'  => $contact->tuneartist,
                'tunelenght'  => $contact->tunelenght,
                'tunerating'  => $contact->tunerating,
                'tunesource'  => $contact->tunesource,
                'tunetitle'   => $contact->tunetitle,
                'tunetrack'   => $contact->tunetrack,

                // User Location
                'loclatitude'       => $contact->loclatitude,
                'loclongitude'      => $contact->loclongitude,
                'localtitude'       => $contact->localtitude,
                'loccountry'        => $contact->loccountry,
                'loccountrycode'    => $contact->loccountrycode,
                'locregion'         => $contact->locregion,
                'locpostalcode'     => $contact->locpostalcode,
                'loclocality'       => $contact->loclocality,
                'locstreet'         => $contact->locstreet,
                'locbuilding'       => $contact->locbuilding,
                'loctext'           => $contact->loctext,
                'locuri'            => $contact->locuri,
                'loctimestamp'      => $contact->loctimestamp,

                'twitter'           => $contact->twitter,
                'skype'             => $contact->skype,
                'yahoo'             => $contact->yahoo,

                'avatarhash'        => $contact->avatarhash,

                'created'           => $contact->created,
                'updated'           => date(SQL::SQL_DATE),

                'jid'  => $contact->jid
            ]
        );

        $this->run('Contact');

        if(!$this->_effective) {
            $this->_sql = '
                insert into contact
                (
                fn,
                name,
                date,
                url,

                email,

                adrlocality,
                adrpostalcode,
                adrcountry,

                gender,
                marital,

                description,

                mood,

                activity,

                nickname,

                tuneartist,
                tunelenght,
                tunerating,
                tunesource,
                tunetitle,
                tunetrack,

                loclatitude,
                loclongitude,
                localtitude,
                loccountry,
                loccountrycode,
                locregion,
                locpostalcode,
                loclocality,
                locstreet,
                locbuilding,
                loctext,
                locuri,
                loctimestamp,

                twitter,
                skype,
                yahoo,

                avatarhash,

                created,
                updated,

                jid)
                values (
                    :fn,
                    :name,
                    :date,
                    :url,

                    :email,

                    :adrlocality,
                    :adrpostalcode,
                    :adrcountry,

                    :gender,
                    :marital,

                    :description,

                    :mood,

                    :activity,

                    :nickname,

                    :tuneartist,
                    :tunelenght,
                    :tunerating,
                    :tunesource,
                    :tunetitle,
                    :tunetrack,

                    :loclatitude,
                    :loclongitude,
                    :localtitude,
                    :loccountry,
                    :loccountrycode,
                    :locregion,
                    :locpostalcode,
                    :loclocality,
                    :locstreet,
                    :locbuilding,
                    :loctext,
                    :locuri,
                    :loctimestamp,

                    :twitter,
                    :skype,
                    :yahoo,

                    :avatarhash,

                    :created,
                    :updated,

                    :jid)';


            $this->prepare(
                'Contact',
                [
                    'fn'    => $contact->fn,
                    'name'  => $contact->name,
                    'date'  => $contact->date,
                    'url'   => $contact->url,

                    'email'  => $contact->email,

                    'adrlocality'    => $contact->adrlocality,
                    'adrpostalcode'  => $contact->adrpostalcode,
                    'adrcountry'     => $contact->adrcountry,

                    'gender'   => $contact->gender,
                    'marital'  => $contact->marital,

                    'description'    => $contact->description,

                    // User Mood (contain serialized array) - XEP 0107
                    'mood'  => $contact->mood,

                    // User Activity (contain serialized array) - XEP 0108
                    'activity'  => $contact->activity,

                    // User Nickname - XEP 0172
                    'nickname'  => $contact->nickname,

                    // User Tune - XEP 0118
                    'tuneartist'  => $contact->tuneartist,
                    'tunelenght'  => $contact->tunelenght,
                    'tunerating'  => $contact->tunerating,
                    'tunesource'  => $contact->tunesource,
                    'tunetitle'   => $contact->tunetitle,
                    'tunetrack'   => $contact->tunetrack,

                    // User Location
                    'loclatitude'       => $contact->loclatitude,
                    'loclongitude'      => $contact->loclongitude,
                    'localtitude'       => $contact->localtitude,
                    'loccountry'        => $contact->loccountry,
                    'loccountrycode'    => $contact->loccountrycode,
                    'locregion'         => $contact->locregion,
                    'locpostalcode'     => $contact->locpostalcode,
                    'loclocality'       => $contact->loclocality,
                    'locstreet'         => $contact->locstreet,
                    'locbuilding'       => $contact->locbuilding,
                    'loctext'           => $contact->loctext,
                    'locuri'            => $contact->locuri,
                    'loctimestamp'      => $contact->loctimestamp,

                    'twitter'           => $contact->twitter,
                    'skype'             => $contact->skype,
                    'yahoo'             => $contact->yahoo,

                    'avatarhash'        => $contact->avatarhash,

                    'created'           => date(SQL::SQL_DATE),
                    'updated'           => date(SQL::SQL_DATE),

                    'jid'               => $contact->jid
                ]
            );

            $this->run('Contact');
        }
    }

    function getAll()
    {
        $this->_sql =
            'select *, privacy.value as privacy from contact
            left outer join privacy
                on contact.jid = privacy.pkey';

        $this->prepare('Contact');
        return $this->run('Contact');
    }

    function searchJid($search)
    {
        $this->_sql =
            'select *, privacy.value as privacy from contact
            left outer join privacy
                on contact.jid = privacy.pkey
            where jid like :jid
            and privacy.value = 1
            order by jid';

        $this->prepare(
            'Contact',
            [
                'jid' => '%'.$search.'%'
            ]
        );
        return $this->run('Contact');
    }

    function getAllPublic($limitf = false, $limitr = false)
    {
        $this->_sql =
            'select contact.*, presence.*, privacy.value as privacy from contact
            left outer join privacy
              on contact.jid = privacy.pkey
            left outer join (
                select min(value) as value, jid, session
                from presence
                group by jid, session
                order by value
            ) as presence
                on contact.jid = presence.jid
                and contact.jid = presence.session
            where privacy.value = 1
              and contact.jid not in (select jid from rosterlink where session = :jid)
              and contact.jid != :jid
            order by presence.value is null, presence.value, contact.jid desc';

        if($limitr)
            $this->_sql = $this->_sql.' limit '.$limitr.' offset '.$limitf;

        $this->prepare(
            'Contact',
            [
                'jid' => $this->_user
            ]
        );

        return $this->run('RosterContact');
    }

    function countAllPublic()
    {
        $this->_sql =
            'select count(*) from contact
            left outer join privacy
                 on contact.jid = privacy.pkey
            where privacy.value = 1
              and contact.jid not in (select jid from rosterlink where session = :jid)
              and contact.jid != :jid';

        $this->prepare(
            'Contact',
            [
                'jid' => $this->_user
            ]
        );

        $results = $this->run(null, 'array');
        $results = array_values($results[0]);

        return (int)$results[0];
    }

    function getRoster($jid = false)
    {
        $this->_sql = '
            select *
            from rosterlink
            left outer join (
                select min(value) as value, jid, session
                from presence
                group by jid, session
                order by value
            ) as presence
                on rosterlink.jid = presence.jid
                and rosterlink.session = presence.session
            left outer join contact
            on rosterlink.jid = contact.jid
            where rosterlink.session = :session';

        if($jid) {
            $this->_sql .= '
            and rosterlink.jid = :jid';
        }

        $this->_sql .= '
            order by rostername';

        if($jid) {
            $this->prepare(
                'RosterLink', ['session' => $this->_user, 'jid' => $jid]
            );
        } else {
            $this->prepare(
                'RosterLink', ['session' => $this->_user]
            );
        }

        return $this->run('RosterContact');
    }

    // Get the roster without the presences
    function getRosterSimple()
    {
        $this->_sql = '
        select
            rosterlink.jid,
            contact.fn,
            contact.name,
            contact.nickname,
            contact.tuneartist,
            contact.tunetitle,
            rosterlink.rostername,
            rosterlink.rostersubscription,
            rosterlink.groupname
        from rosterlink
        left outer join contact
        on rosterlink.jid = contact.jid
        where rosterlink.session = :session
        order by groupname, rosterlink.jid';

        $this->prepare(
            'RosterLink',
            [
                'session' => $this->_user
            ]
        );

        return $this->run('RosterContact');
    }

    function search($key)
    {
        $this->_sql = '
        select
            rosterlink.jid,
            contact.fn,
            contact.name,
            contact.nickname,
            contact.tuneartist,
            contact.tunetitle,
            rosterlink.rostername,
            rosterlink.rostersubscription,
            rosterlink.groupname,
            presence.value,
            presence.delay,
            presence.last
        from rosterlink
        left outer join contact
        left outer join (
            select a.*
            from presence a
            join (
                select jid, min( id ) as id
                from presence
                where session = :session
                group by jid
                ) as b on ( a.id = b.id )
            ) presence on contact.jid = presence.jid
        on rosterlink.jid = contact.jid
        where rosterlink.session = :session
          and (rosterlink.jid like :jid
            or rosterlink.rostername like :rostername)
        order by groupname, rosterlink.jid
        limit 4 offset 0';

        $this->prepare(
            'RosterLink',
            [
                'session' => $this->_user,
                'jid' => '%'.$key.'%',
                'rostername' => '%'.$key.'%'
            ]
        );

        return $this->run('RosterContact');
    }

    function getRosterFrom()
    {
        $this->_sql = '
            select * from rosterlink
            left outer join contact
                on rosterlink.jid = contact.jid
            where rosterlink.session = :session
              and rosterlink.rostersubscription = :rostersubscription';

        $this->prepare(
            'RosterLink',
            [
                'session'            => $this->_user,
                'rostersubscription' => 'from'
            ]
        );

        return $this->run('RosterContact');
    }

    function getRosterItem($jid, $item = false)
    {
        $this->_sql = '
        select
            rosterlink.jid,
            contact.fn,
            contact.name,
            contact.nickname,
            contact.tuneartist,
            contact.tunetitle,
            rosterlink.rostername,
            rosterlink.rostersubscription,
            rosterlink.groupname,
            presence.status,
            presence.resource,
            presence.value,
            presence.delay,
            presence.node,
            presence.ver,
            presence.last
        from rosterlink
        left outer join presence
        on rosterlink.jid = presence.jid and rosterlink.session = presence.session
        left outer join contact
        on rosterlink.jid = contact.jid
        where rosterlink.session = :session
            and rosterlink.jid = :jid
        order by groupname, rosterlink.jid, presence.value';

        $this->prepare(
            'RosterLink',
            [
                'session' => $this->_user,
                'jid' => $jid
            ]
        );

        if($item)
            return $this->run('RosterContact');
        else
            return $this->run('RosterContact', 'item');
    }

    function getPresence($jid, $resource)
    {
        $this->_sql = '
            select * from contact
            right outer join presence on contact.jid = presence.mucjid
            where presence.session = :session
            and presence.jid = :jid
            and presence.resource = :resource
            order by mucaffiliation desc';

        $this->prepare(
            'Presence',
            [
                'session' => $this->_user,
                'jid' => $jid,
                'resource' => $resource
            ]
        );

        return $this->run('PresenceContact', 'item');
    }

    function getPresences($jid)
    {
        $this->_sql = '
            select * from contact
            right outer join presence on contact.jid = presence.mucjid
            where presence.session = :session
            and presence.jid = :jid
            order by mucaffiliation desc';

        $this->prepare(
            'Presence',
            [
                'session' => $this->_user,
                'jid' => $jid
            ]
        );

        return $this->run('PresenceContact');
    }

    function getTop($limit = 6, $filter = false)
    {
        $filter = ($filter != false) ? implode('\',\'', $filter) : '';
        $this->_sql = '
            select *, jidfrom from (
                select jidfrom, count(*) as count from message
                where jidfrom not like :session
                    and session = :session
                    and type != \'groupchat\'
                group by jidfrom
                order by count desc
            ) as top
            join (
                select *
                from rosterlink
                where session = :session
                and jid not in (\''.$filter.'\')
                ) as rosterlink on jidfrom = rosterlink.jid
            left outer join contact on jidfrom = contact.jid
            left outer join (
                select a.*
                from presence a
                join (
                    select jid, min( id ) as id
                    from presence
                    where session = :session
                    group by jid
                    ) as b on ( a.id = b.id )
                ) presence on jidfrom = presence.jid
            order by presence.value, count desc
            limit :tunelenght';

        $this->prepare(
            'Contact',
            [
                'rosterlink.session' => $this->_user,
                'tunelenght' => $limit // And an another hack…
            ]
        );

        return $this->run('RosterContact');
    }
}
