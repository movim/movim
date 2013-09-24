<?php

namespace modl;

class Contact extends ModlModel {
    protected $jid;
    
    protected $fn;
    protected $name;
    protected $date;
    protected $url;
    
    protected $email;
    
    protected $adrlocality;
    protected $adrpostalcode;
    protected $adrcountry;
    
    protected $gender;
    protected $marital;
    
    protected $phototype;
    protected $photobin;
    
    protected $description;
    
    protected $protected;
    protected $privacy;
    
    // User Mood (contain serialized array) - XEP 0107
    protected $mood;
    
    // User Activity (contain serialized array) - XEP 0108
    protected $activity;
    
    // User Nickname - XEP 0172
    protected $nickname;
    
    // User Tune - XEP 0118
    protected $tuneartist;
    protected $tunelenght;
    protected $tunerating;
    protected $tunesource;
    protected $tunetitle;
    protected $tunetrack;
    
    // User Location 
    protected $loclatitude;
    protected $loclongitude;
    protected $localtitude;
    protected $loccountry;
    protected $loccountrycode;
    protected $locregion;
    protected $locpostalcode;
    protected $loclocality;
    protected $locstreet;
    protected $locbuilding;
    protected $loctext;
    protected $locuri;
    protected $loctimestamp;
    
    public function __construct() {
        $this->_struct = '
        {
            "jid" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "fn" : 
                {"type":"string", "size":128 },
            "name" : 
                {"type":"string", "size":128 },
            "date" : 
                {"type":"date",   "size":11 },
            "url" : 
                {"type":"string", "size":128 },
            "email" : 
                {"type":"string", "size":128 },
            "adrlocality" : 
                {"type":"string", "size":128 },
            "adrpostalcode" : 
                {"type":"string", "size":128 },
            "adrcountry" : 
                {"type":"string", "size":128 },
            "gender" : 
                {"type":"string", "size":1 },
            "marital" : 
                {"type":"string", "size":20 },
            "phototype" : 
                {"type":"string", "size":128 },
            "photobin" : 
                {"type":"text"},
            "description" : 
                {"type":"text"},
            "mood" : 
                {"type":"string", "size":128 },
            "activity" : 
                {"type":"string", "size":128 },
            "nickname" : 
                {"type":"string", "size":128 },
            "tuneartist" : 
                {"type":"string", "size":128 },
            "tunelenght" : 
                {"type":"int",    "size":11 },
            "tunerating" : 
                {"type":"int",    "size":11 },
            "tunesource" : 
                {"type":"string", "size":128 },
            "tunetitle" : 
                {"type":"string", "size":128 },
            "tunetrack" : 
                {"type":"string", "size":128 },
            "loclatitude" : 
                {"type":"string", "size":128 },
            "loclongitude" : 
                {"type":"string", "size":128 },
            "localtitude" : 
                {"type":"int",    "size":11 },
            "loccountry" : 
                {"type":"string", "size":128 },
            "loccountrycode" : 
                {"type":"string", "size":128 },
            "locregion" : 
                {"type":"string", "size":128 },
            "locpostalcode" : 
                {"type":"string", "size":128 },
            "loclocality" : 
                {"type":"string", "size":128 },
            "locstreet" : 
                {"type":"string", "size":128 },
            "locbuilding" : 
                {"type":"string", "size":128 },
            "loctext" : 
                {"type":"string", "size":128 },
            "locuri" : 
                {"type":"string", "size":128 },
            "loctimestamp" : 
                {"type":"date",   "size":11 }
        }';
        
        parent::__construct();
    }
    
    public function set($vcard, $jid) {
        $this->jid = \echapJid($jid);
        
        $date = strtotime((string)$vcard->vCard->BDAY);

        if($date != false && $date != '' && $date != '-3600') 
            $this->date = date(DATE_ISO8601, $date);
        //else
        //    $this->date = '0000-00-00';
        
        $this->name = (string)$vcard->vCard->NICKNAME;
        $this->fn = (string)$vcard->vCard->FN;
        $this->url = (string)$vcard->vCard->URL;

        $this->gender = (string)$vcard->vCard->{'X-GENDER'};
        $this->marital = (string)$vcard->vCard->MARITAL->STATUS;

        $this->email = (string)$vcard->vCard->EMAIL->USERID;
        
        $this->adrlocality = (string)$vcard->vCard->ADR->LOCALITY;
        $this->adrpostalcode = (string)$vcard->vCard->ADR->PCODE;
        $this->adrcountry = (string)$vcard->vCard->ADR->CTRY;
        
        $this->phototype = (string)$vcard->vCard->PHOTO->TYPE;
        $this->photobin = (string)$vcard->vCard->PHOTO->BINVAL;
        
        $this->description = (string)$vcard->vCard->DESC;
    }

    public function createThumbnails() {
        \createThumbnails(strtolower($this->jid), $this->photobin);
    }

    public function getPhoto($size = 'l', $jid = false) {
        $jid = strtolower($jid);    
        if($jid != false) {
            $str = BASE_URI.'cache/'.strtolower($jid).'_'.$size.'.jpg';
        } elseif(
               isset($this->phototype) 
            && isset($this->photobin) 
            && $this->phototype != '' 
            && $this->photobin != ''
        ) {
            $str = BASE_URI.'cache/'.strtolower($this->jid).'_'.$size.'.jpg';
        } else {
            if(isset($this->jid))
                $out = base_convert($this->jid, 32, 8);
            else
                $out = base_convert(md5(openssl_random_pseudo_bytes(5)), 16, 8);

            if($out == false)
                $out[4] = 1;
            $str = BASE_URI.'themes/movim/img/default'.$out[4].'.svg';
        }
        
        return $str;
    }
    
    public function setLocation($stanza) {
        $this->loclatitude      = (string)$stanza->items->item->geoloc->lat;
        $this->loclongitude     = (string)$stanza->items->item->geoloc->lon;
        $this->localtitude      = (int)$stanza->items->item->geoloc->alt;
        $this->loccountry       = (string)$stanza->items->item->geoloc->country;
        $this->loccountrycode   = (string)$stanza->items->item->geoloc->countrycode;
        $this->locregion        = (string)$stanza->items->item->geoloc->region;
        $this->locpostalcode    = (string)$stanza->items->item->geoloc->postalcode;
        $this->loclocality      = (string)$stanza->items->item->geoloc->locality;
        $this->locstreet        = (string)$stanza->items->item->geoloc->street;
        $this->locbuilding      = (string)$stanza->items->item->geoloc->building;
        $this->loctext          = (string)$stanza->items->item->geoloc->text;
        $this->locuri           = (string)$stanza->items->item->geoloc->uri;
        $this->loctimestamp = date(
                            'Y-m-d H:i:s', 
                            strtotime((string)$stanza->items->item->geoloc->timestamp));
    }
    
    public function setTune($stanza) {
        $this->tuneartist = (string)$stanza->items->item->tune->artist;
        $this->tunelenght = (int)$stanza->items->item->tune->lenght;
        $this->tunerating = (int)$stanza->items->item->tune->rating;
        $this->tunesource = (string)$stanza->items->item->tune->source;
        $this->tunetitle  = (string)$stanza->items->item->tune->title;
        $this->tunetrack  = (string)$stanza->items->item->tune->track;
    }

    public function getPlace() {
        $place = '';
        
        if($this->loctext != '')
            $place .= $this->loctext.' '; 
        else {
            if($this->locbuilding != '')
                $place .= $this->locbuilding.' ';
            if($this->locstreet != '')
                $place .= $this->locstreet.'<br />';
            if($this->locpostalcode != '')
                $place .= $this->locpostalcode.' ';
            if($this->loclocality != '')
                $place .= $this->loclocality.'<br />';
            if($this->locregion != '')
                $place .= $this->locregion.' - ';
            if($this->loccountry != '')
                $place .= $this->loccountry;
        }
        
        return $place;
    }

    public function getTrueName() {
        $truename = '';
        
        if(isset($this->rostername))
            $rostername = str_replace('\40', '', $this->rostername);
        else
            $rostername = '';
        
        if(
            isset($this->rostername)
            && $rostername != ''
            && !filter_var($rostername, FILTER_VALIDATE_EMAIL)
          )
            $truename = $rostername;
        elseif(
            isset($this->fn)
            && $this->fn != ''
            && !filter_var($this->fn, FILTER_VALIDATE_EMAIL)
          )
            $truename = $this->fn;
        elseif(
            isset($this->nickname)
            && $this->nickname != ''
            && !filter_var($this->nickname, FILTER_VALIDATE_EMAIL)
          )
            $truename = $this->nickname;
        elseif(
            isset($this->name)
            && $this->name != ''
            && !filter_var($this->name, FILTER_VALIDATE_EMAIL)
          )
            $truename = $this->name;
        else
            $truename = $this->jid;

        return $truename;
    }

    function getAge() {
        if( $this->date != '0000-00-00T00:00:00+0000' 
            && $this->date != '1970-01-01T00:00:00+0000')
            return intval(substr(date('Ymd') - date('Ymd', strtotime($this->date)), 0, -4));
    }
}

class RosterContact extends Contact {
    protected $rostername;
    protected $groupname;
    protected $status;
    protected $ressource;
    protected $value;
    protected $delay;
    protected $chaton;
    protected $last;
    protected $rosterask;
    protected $node;
    protected $ver;
    protected $category;
    protected $type;
    
    public function __construct() {
        parent::__construct();
        $this->_struct = "
        {
            'rostername' : 
                {'type':'string', 'size':128 },
            'rosterask' : 
                {'type':'string', 'size':128 },
            'groupname' : 
                {'type':'string', 'size':128 },
            'ressource' : 
                {'type':'string', 'size':128, 'key':true },
            'value' : 
                {'type':'int',    'size':11, 'mandatory':true },
            'chaton' : 
                {'type':'int',    'size':11 },
            'status' : 
                {'type':'text'},
            'node' : 
                {'type':'string', 'size':128 },
            'ver' : 
                {'type':'string', 'size':128 },
            'delay' : 
                {'type':'date'},
            'last' : 
                {'type':'int',    'size':11 }
        }";
    }
    
    // This method is only use on the connection
    public function setPresence($p) {
        $this->ressource = $p->ressource;
        $this->value     = $p->value;
        $this->status    = $p->status;
        $this->delay     = $p->delay;
        $this->last      = $p->last;
    }
}
