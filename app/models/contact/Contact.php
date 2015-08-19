<?php

namespace modl;

class Contact extends Model {
    public $jid;

    protected $fn;
    protected $name;
    protected $date;
    protected $url;

    public    $email;

    protected $adrlocality;
    protected $adrpostalcode;
    protected $adrcountry;

    protected $gender;
    protected $marital;

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

    // Accounts
    protected $twitter;
    protected $skype;
    protected $yahoo;

    protected $avatarhash;

    // Datetime
    public $created;
    public $updated;

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
                {"type":"text" },
            "locuri" :
                {"type":"string", "size":128 },
            "loctimestamp" :
                {"type":"date",   "size":11 },
            "twitter" :
                {"type":"string", "size":128 },
            "skype" :
                {"type":"string", "size":128 },
            "yahoo" :
                {"type":"string", "size":128 },
            "avatarhash" :
                {"type":"string", "size":128 },
            "created" :
                {"type":"date" },
            "updated" :
                {"type":"date" }
        }';

        parent::__construct();
    }

    public function set($vcard, $jid) {
        $this->jid = \echapJid($jid);

        if(isset($vcard->vCard->BDAY)
        && (string)$vcard->vCard->BDAY != '')
            $this->date = (string)$vcard->vCard->BDAY;
        else
            $this->date = null;

        $this->date = date(DATE_ISO8601, strtotime($this->date));

        $this->name = (string)$vcard->vCard->NICKNAME;
        $this->fn = (string)$vcard->vCard->FN;
        $this->url = (string)$vcard->vCard->URL;

        $this->gender = (string)$vcard->vCard->{'X-GENDER'};
        $this->marital = (string)$vcard->vCard->MARITAL->STATUS;

        $this->email = (string)$vcard->vCard->EMAIL->USERID;

        $this->adrlocality = (string)$vcard->vCard->ADR->LOCALITY;
        $this->adrpostalcode = (string)$vcard->vCard->ADR->PCODE;
        $this->adrcountry = (string)$vcard->vCard->ADR->CTRY;

        if(filter_var((string)$vcard->vCard->PHOTO, FILTER_VALIDATE_URL)) {
            $this->photobin = base64_encode(
                requestUrl((string)$vcard->vCard->PHOTO, 1));
        } else {
            $this->photobin = (string)$vcard->vCard->PHOTO->BINVAL;
        }

        $this->description = (string)$vcard->vCard->DESC;
    }

    public function createThumbnails() {
        $p = new \Picture;
        $p->fromBase($this->photobin);
        $p->set($this->jid);

        if(isset($this->email)) {
            \createEmailPic(strtolower($this->jid), $this->email);
        }
    }

    public function isPhoto($jid = false, $x = false, $y = false) {
        if(!$jid) return false;

        $p = new \Picture;
        $url = $p->get($jid, $sizes[$size][0], $sizes[$size][1]);
        if($url) return $url;

        return false;
    }

    public function getPhoto($size = 'l', $jid = false) {
        if($size == 'email') {
            return BASE_URI.'cache/'.strtolower($this->jid).'_email.jpg';
        } else {
            $sizes = array(
                'wall'  => array(1920, 1080),
                'xxl'   => array(1280, 300),
                'l'     => array(210 , false),
                'm'     => array(120 , false),
                's'     => array(50  , false),
                'xs'    => array(28  , false),
                'xxs'   => array(24  , false)
            );


            $p = new \Picture;
            return $p->get($this->jid, $sizes[$size][0], $sizes[$size][1]);
        }
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

    public function setVcard4($vcard) {
        if(isset($vcard->bday->date))
            $this->date    = (string)$vcard->bday->date;
        if($empty($this->date))
            $this->date    = null;

        $this->name    = (string)$vcard->nickname->text;
        $this->fn      = (string)$vcard->fn->text;
        $this->url     = (string)$vcard->url->uri;

        if(isset($vcard->gender))
            $this->gender  = (string)$vcard->gender->sex->text;
        if(isset($vcard->marital))
            $this->marital = (string)$vcard->marital->status->text;

        $this->adrlocality     = (string)$vcard->adr->locality;
        $this->adrcountry      = (string)$vcard->adr->country;
        $this->adrpostalcode   = (string)$vcard->adr->code;

        if(isset($vcard->impp)) {
            foreach($vcard->impp->children() as $c) {
                list($key, $value) = explode(':', (string)$c);

                switch($key) {
                    case 'twitter' :
                        $this->twitter = str_replace('@', '', $value);
                        break;
                    case 'skype' :
                        $this->skype = (string)$value;
                        break;
                    case 'ymsgr' :
                        $this->yahoo = (string)$value;
                        break;
                }
            }
        }

        $this->email           = (string)$vcard->email->text;

        $this->description     = trim((string)$vcard->note->text);
    }

    public function getPlace() {
        $place = null;

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
        if(isset($this->date)
            && $this->date != '0000-00-00T00:00:00+0000'
            && $this->date != '1970-01-01 00:00:00'
            && $this->date != '1970-01-01 01:00:00'
            && $this->date != '1970-01-01T00:00:00+0000') {
            $age = intval(substr(date('Ymd') - date('Ymd', strtotime($this->date)), 0, -4));
            if($age != 0)
                return $age;
        }
    }

    function getGender() {
        $gender = getGender();

        if($this->gender != null && $this->gender != 'N') {
            return $gender[$this->gender];
        }
    }

    function getMarital() {
        $marital = getMarital();

        if($this->marital != null && $this->marital != 'none') {
            return $marital[$this->marital];
        }
    }

    function toRoster() {
        return array(
            'jid'        => $this->jid,
            'rostername' => $this->rostername,
            'rostername' => $this->rostername,
            'groupname'  => $this->groupname,
            'status'     => $this->status,
            'resource'   => $this->resource,
            'value'      => $this->value
            );
    }

    function isEmpty() {
        if($this->fn == null
        && $this->name == null
        && $this->date == null
        && $this->url == null
        && $this->email == null
        && $this->description == null) {
            return true;
        } else {
            return false;
        }
    }

    function isOld() {
        if(strtotime($this->updated) < mktime( // We update the 1 day old vcards
                                        0,
                                        0,
                                        0,
                                        gmdate("m"),
                                        gmdate("d")-1,
                                        gmdate("Y")
                                    )
            ) {
            return true;
        } else {
            return false;
        }
    }
}

class PresenceContact extends Contact {
    // General presence informations
    protected $resource;
    protected $value;
    protected $priority;
    protected $status;

    // Client Informations
    protected $node;
    protected $ver;

    // Delay - XEP 0203
    protected $delay;

    // Last Activity - XEP 0256
    protected $last;

    // Current Jabber OpenPGP Usage - XEP-0027
    protected $publickey;
    protected $muc;
    protected $mucjid;
    protected $mucaffiliation;
    protected $mucrole;

    public function __construct() {
        parent::__construct();

        $this->_struct = '
        {
            "resource" :
                {"type":"string", "size":64, "key":true },
            "value" :
                {"type":"int",    "size":11, "mandatory":true },
            "priority" :
                {"type":"int",    "size":11 },
            "status" :
                {"type":"text"},
            "node" :
                {"type":"string", "size":128 },
            "ver" :
                {"type":"string", "size":128 },
            "delay" :
                {"type":"date"},
            "last" :
                {"type":"int",    "size":11 },
            "publickey" :
                {"type":"text"},
            "muc" :
                {"type":"int",    "size":1 },
            "mucjid" :
                {"type":"string", "size":64 },
            "mucaffiliation" :
                {"type":"string", "size":32 },
            "mucrole" :
                {"type":"string", "size":32 }
        }';
    }

}

class RosterContact extends Contact {
    protected $rostername;
    protected $groupname;
    protected $status;
    protected $resource;
    protected $value;
    protected $delay;
    protected $chaton;
    protected $last;
    protected $publickey;
    protected $muc;
    protected $rosterask;
    protected $rostersubscription;
    protected $node;
    protected $ver;
    protected $category;
    //protected $type;

    public function __construct() {
        parent::__construct();
        $this->_struct = "
        {
            'rostername' :
                {'type':'string', 'size':128 },
            'rosterask' :
                {'type':'string', 'size':128 },
            'rostersubscription' :
                {'type':'string', 'size':8 },
            'groupname' :
                {'type':'string', 'size':128 },
            'resource' :
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
                {'type':'int',    'size':11 },
            'publickey' :
                {'type':'text'},
            'muc' :
                {'type':'int',    'size':1 },
            'mucaffiliation' :
                {'type':'string', 'size':32 },
            'mucrole' :
                {'type':'string', 'size':32 }
        }";
    }

    // This method is only use on the connection
    public function setPresence($p) {
        $this->resource         = $p->resource;
        $this->value            = $p->value;
        $this->status           = $p->status;
        $this->delay            = $p->delay;
        $this->last             = $p->last;
        $this->publickey        = $p->publickey;
        $this->muc              = $p->muc;
        $this->mucaffiliation   = $p->mucaffiliation;
        $this->mucrole          = $p->mucrole;
    }
}
