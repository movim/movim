<?php

namespace modl;

use Respect\Validation\Validator;

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
                {"type":"string", "size":64, "key":true },
            "fn" :
                {"type":"string", "size":64 },
            "name" :
                {"type":"string", "size":64 },
            "date" :
                {"type":"date",   "size":11 },
            "url" :
                {"type":"string", "size":128 },
            "email" :
                {"type":"string", "size":128 },
            "adrlocality" :
                {"type":"string", "size":128 },
            "adrpostalcode" :
                {"type":"string", "size":12 },
            "adrcountry" :
                {"type":"string", "size":64 },
            "gender" :
                {"type":"string", "size":1 },
            "marital" :
                {"type":"string", "size":16 },
            "description" :
                {"type":"text"},
            "mood" :
                {"type":"string", "size":64 },
            "activity" :
                {"type":"string", "size":128 },
            "nickname" :
                {"type":"string", "size":64 },
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
                {"type":"string", "size":32 },
            "loclongitude" :
                {"type":"string", "size":32 },
            "localtitude" :
                {"type":"int",    "size":11 },
            "loccountry" :
                {"type":"string", "size":128 },
            "loccountrycode" :
                {"type":"string", "size":2 },
            "locregion" :
                {"type":"string", "size":128 },
            "locpostalcode" :
                {"type":"string", "size":32 },
            "loclocality" :
                {"type":"string", "size":128 },
            "locstreet" :
                {"type":"string", "size":128 },
            "locbuilding" :
                {"type":"string", "size":32 },
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
                {"type":"date", "mandatory":true },
            "updated" :
                {"type":"date", "mandatory":true }
        }';

        parent::__construct();
    }

    public function set($vcard, $jid) {
        $this->__set('jid', \echapJid($jid));

        $validate_date = Validator::date('Y-m-d');
        if(isset($vcard->vCard->BDAY)
        && $validate_date->validate($vcard->vCard->BDAY))
            $this->__set('date', (string)$vcard->vCard->BDAY);

        $this->__set('date', date(DATE_ISO8601, strtotime($this->date)));

        $this->__set('name', (string)$vcard->vCard->NICKNAME);
        $this->__set('fn', (string)$vcard->vCard->FN);
        $this->__set('url', (string)$vcard->vCard->URL);

        $this->__set('gender', (string)$vcard->vCard->{'X-GENDER'});
        $this->__set('marital', (string)$vcard->vCard->MARITAL->STATUS);

        $this->__set('email', (string)$vcard->vCard->EMAIL->USERID);

        $this->__set('adrlocality', (string)$vcard->vCard->ADR->LOCALITY);
        $this->__set('adrpostalcode', (string)$vcard->vCard->ADR->PCODE);
        $this->__set('adrcountry', (string)$vcard->vCard->ADR->CTRY);

        if(filter_var((string)$vcard->vCard->PHOTO, FILTER_VALIDATE_URL)) {
            $this->__set('photobin', base64_encode(
                requestUrl((string)$vcard->vCard->PHOTO, 1)));
        } else {
            $this->__set('photobin', (string)$vcard->vCard->PHOTO->BINVAL);
        }

        $this->__set('description', (string)$vcard->vCard->DESC);
    }

    public function createThumbnails() {
        $p = new \Picture;
        $p->fromBase($this->photobin);
        $p->set($this->jid);

        if(isset($this->email) && $this->email != '') {
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
            return BASE_URI.'cache/'.strtolower($this->jid).'_email.png';
        } else {
            $sizes = array(
                'wall'  => array(1920, 1080),
                'xxl'   => array(1280, 300),
                'xl'    => array(512 , false),
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
        $this->__set('tuneartist', (string)$stanza->items->item->tune->artist);
        $this->__set('tunelenght', (int)$stanza->items->item->tune->lenght);
        $this->__set('tunerating', (int)$stanza->items->item->tune->rating);
        $this->__set('tunesource', (string)$stanza->items->item->tune->source);
        $this->__set('tunetitle', (string)$stanza->items->item->tune->title);
        $this->__set('tunetrack', (string)$stanza->items->item->tune->track);
    }

    public function setVcard4($vcard) {
        $validate_date = Validator::date('Y-m-d');
        if(isset($vcard->bday->date)
        && $validate_date->validate($vcard->bday->date))
            $this->__set('date', (string)$vcard->bday->date);

        $this->__set('name', (string)$vcard->nickname->text);
        $this->__set('fn', (string)$vcard->fn->text);
        $this->__set('url', (string)$vcard->url->uri);

        if(isset($vcard->gender))
            $this->__set('gender ', (string)$vcard->gender->sex->text);
        if(isset($vcard->marital))
            $this->__set('marital', (string)$vcard->marital->status->text);

        $this->__set('adrlocality', (string)$vcard->adr->locality);
        $this->__set('adrcountry', (string)$vcard->adr->country);
        $this->__set('adrpostalcode', (string)$vcard->adr->code);

        if(isset($vcard->impp)) {
            foreach($vcard->impp->children() as $c) {
                list($key, $value) = explode(':', (string)$c);

                switch($key) {
                    case 'twitter' :
                        $this->__set('twitter', str_replace('@', '', $value));
                        break;
                    case 'skype' :
                        $this->__set('skype', (string)$value);
                        break;
                    case 'ymsgr' :
                        $this->__set('yahoo', (string)$value);
                        break;
                }
            }
        }

        $this->__set('email', (string)$vcard->email->text);
        $this->__set('description', trim((string)$vcard->note->text));
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
        else {
            $truename = explodeJid($this->jid);
            $truename = $truename['username'];
        }

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

    function getAlbum()
    {
        $uri = str_replace(
            ' ',
            '%20',
            'http://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=80c1aa3abfa9e3d06f404a2e781e38f9&artist='.
                $this->tuneartist.
                '&album='.
                $this->tunesource.
                '&format=json'
            );

        $json = json_decode(requestURL($uri, 2));

        if(isset($json->album)) {
            $json->album->url = $json->album->image[2]->{'#text'};
            return $json->album;
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
                                        gmdate("H"),
                                        gmdate("i")-10,
                                        gmdate("s"),
                                        gmdate("m"),
                                        gmdate("d"),
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

class RosterContact extends Contact
{
    protected $rostername;
    protected $groupname;
    protected $status;
    protected $resource;
    protected $value;
    protected $delay;
    protected $last;
    protected $publickey;
    protected $muc;
    protected $rosterask;
    protected $rostersubscription;
    protected $node;
    protected $ver;
    protected $category;
    //protected $type;

    public function __construct()
    {
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
    public function setPresence($p)
    {
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

    public function getCaps()
    {
        if(!empty($this->node)
        && !empty($this->ver)) {
            $node = $this->node.'#'.$this->ver;

            $cad = new \Modl\CapsDAO();
            return $cad->get($node);
        }
    }
}
