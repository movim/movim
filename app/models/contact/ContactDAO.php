<?php

namespace modl;

class ContactDAO extends ModlSQL {
    function __construct() {
        parent::__construct();
    }
    
    function get($jid) {            
        $this->_sql = '
            select *, privacy.value as privacy from contact 
            left outer join privacy 
                on contact.jid = privacy.pkey 
            where jid = :jid';
        
        $this->prepare(
            'Contact', 
            array(
                'jid' => $jid
            )
        );
        
        return $this->run('Contact', 'item');
    }
    
    function set(Contact $contact) {
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
                
                phototype       = :phototype,
                photobin        = :photobin,
                
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
                loctimestamp    = :loctimestamp
            where contact.jid = :jid';
        
        $this->prepare(
            'Contact', 
            array(
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
                
                'phototype'  => $contact->phototype,
                'photobin'   => $contact->photobin,
                
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
                
                'jid'  => $contact->jid
            )
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
                
                phototype,
                photobin,
                
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
                    
                    :phototype,
                    :photobin,
                    
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
                    :jid)';
                    
                
            $this->prepare(
                'Contact', 
                array(
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
                    
                    'phototype'  => $contact->phototype,
                    'photobin'   => $contact->photobin,
                    
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
                    
                    'jid'  => $contact->jid
                )
            );
            
            $this->run('Contact'); 
        }
    }
    
    function getAll() {
        $this->_sql = 
            'select *, privacy.value as privacy from contact 
            left outer join privacy 
                on contact.jid = privacy.pkey';
        
        $this->prepare('Contact');
        return $this->run('Contact');
    }
    
    function getAllPublic() {       
        $this->_sql = 
            'select *, privacy.value as privacy from contact 
            left outer join privacy 
                on contact.jid = privacy.pkey
            where privacy.value = 1';
        
        $this->prepare('Contact');
        return $this->run('Contact');
    }
    
    function cleanRoster() {
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
    function getRoster() {
        $this->_sql = '
        select * from rosterlink
        left outer join presence
        on rosterlink.jid = presence.jid and rosterlink.session = presence.session
        left outer join contact
        on rosterlink.jid = contact.jid
        where rosterlink.session = :session
        order by groupname';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run('RosterContact');
    }
    
    function getRosterChat() {
        $this->_sql = '
            select * from rosterlink 
            left outer join (
                select * from presence
                order by presence.priority desc
                limit 1
                ) as presence
                on rosterlink.jid = presence.jid 
            left outer join contact
                on rosterlink.jid = contact.jid
            where rosterlink.session = :session
                and rosterlink.chaton > 0
            order by rosterlink.groupname, presence.value, rosterlink.jid';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run('RosterContact'); 
    }
    
    function getRosterItem($jid) {
        $this->_sql = '
            select * from rosterlink 
            left outer join (
                select * from presence
                where session = :session
                and jid = :jid
                order by presence.value
                limit 1
                ) as presence
                on rosterlink.jid = presence.jid 
            left outer join contact
                on rosterlink.jid = contact.jid
            where rosterlink.session = :session
                and rosterlink.jid = :jid
            order by rosterlink.groupname, presence.value, rosterlink.jid';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user,
                'jid' => $jid
            )
        );
        
        return $this->run('RosterContact', 'item'); 
    }
    
    function getRosterSubscribe() {
        /*$sql = '
        select 
            RosterLink.jid,
            contact.fn,
            contact.nickname,
            contact.name,
            contact.phototype,
            contact.photobin,
            contact.loclatitude,
            contact.loclongitude,
            contact.localtitude,
            contact.loccountry,
            contact.loccountrycode,
            contact.locregion,
            contact.locpostalcode,
            contact.loclocality,
            contact.locstreet,
            contact.locbuilding,
            contact.loctext,
            contact.locuri,
            contact.loctimestamp,
            contact.mood,
            contact.tuneartist,
            contact.tunelenght,
            contact.tunerating,
            contact.tunesource,
            contact.tunetitle,
            contact.tunetrack,
            RosterLink.rostername,
            RosterLink.group,
            RosterLink.chaton,
            Presence.status,
            Presence.ressource,
            Presence.presence,
            Presence.delay,
            Presence.last,
            Presence.node,
            Presence.ver
            from RosterLink left outer join 
            (
                select * from Presence 
                where Presence.key=\''.$this->_user.'\'
                group by jid, node, ver
                order by presence) as Presence
            on Presence.jid = RosterLink.jid
            left join contact on RosterLink.jid = contact.jid
            where RosterLink.key=\''.$this->_user.'\'
            and RosterLink.rosterask = \'subscribe\'
            group by RosterLink.jid
            order by RosterLink.groupname';
        
        return $this->mapper('RosterContact', $this->_db->query($sql));*/
        
        return null;
    }
    
    function getStatistics() {
        $this->_sql = '
            select 
            (select count(*) from postn where postn.session = :session ) as post,
            (select count(*) from rosterlink where rosterlink.session= :session ) as rosterlink,
            (select count(*) from presence where presence.session= :session ) as presence,
            (select count(*) from message where message.session = :session) as message;';
        
        $this->prepare(
            'Postn', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run(null, 'array'); 
    }
}
