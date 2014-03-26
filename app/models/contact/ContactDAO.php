<?php

namespace modl;

class ContactDAO extends SQL {
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
                yahoo           = :yahoo
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
        order by groupname, rosterlink.jid, presence.value';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run('RosterContact');
    }
    // limit 1
    function getRosterChat() {
        $this->_sql = '
            select * from rosterlink 
            left outer join (
                select * from presence
                order by presence.priority desc
                ) as presence
                on rosterlink.jid = presence.jid 
            left outer join contact
                on rosterlink.jid = contact.jid
            where rosterlink.session = :session
                and rosterlink.chaton > 0
            order by rosterlink.groupname, rosterlink.jid, presence.value';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user
            )
        );
        
        return $this->run('RosterContact'); 
    }
    
    function getRosterItem($jid, $item = false) {
        $this->_sql = '
        select * from rosterlink
        left outer join presence
        on rosterlink.jid = presence.jid and rosterlink.session = presence.session
        left outer join contact
        on rosterlink.jid = contact.jid
        where rosterlink.session = :session
            and rosterlink.jid = :jid
        order by groupname, rosterlink.jid, presence.value';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user,
                'jid' => $jid
            )
        );
        
        if($item)
            return $this->run('RosterContact'); 
        else
            return $this->run('RosterContact', 'item');
    }

    function getMe($item = false) {
        $this->_sql = '
            select * from contact
            left outer join presence on contact.jid = presence.jid
            where contact.jid = :jid
            and presence.session = :session';
        
        $this->prepare(
            'RosterLink', 
            array(
                'session' => $this->_user,
                'jid' => $this->_user
            )
        );
        
        if($item)
            return $this->run('RosterContact'); 
        else
            return $this->run('RosterContact', 'item');

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
