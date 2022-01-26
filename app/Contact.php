<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

use Respect\Validation\Validator;
use Movim\Image;

class Contact extends Model
{
    protected $fillable = ['id', 'nickname', 'mood'];
    protected $keyType = 'string';
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo('App\User', 'id');
    }

    public function scopePublic($query, $like = false)
    {
        return $query->whereIn('id', function ($query) use ($like) {
            $query->select('id')
                  ->from('users')
                  ->where('public', true)
                  ->when($like !== false, function ($query) use ($like) {
                      $query->where('id', 'like', '%'. $like . '%');
                  });
        });
    }

    public function scopeNotInRoster($query, $sessionId)
    {
        return $query->whereNotIn('id', function ($query) use ($sessionId) {
            $query->select('jid')
                  ->from('rosters')
                  ->where('session_id', $sessionId);
        });
    }

    public function scopeOrderByPresence($query)
    {
        return $query->leftJoin(DB::raw('(
            select min(value) as value, jid
            from presences
            group by jid) as presences
            '), 'presences.jid', '=', 'contacts.id')
        ->orderBy('presences.value');
    }

    public function save(array $options = [])
    {
        try {
            unset($this->photobin);
            parent::save($options);
        } catch (\Exception $e) {
            /*
            * Multi processes simultanous save
            */
        }
    }

    public function set($vcard, $jid)
    {
        $this->id = $jid;

        $validate_date = Validator::date('Y-m-d');
        if (isset($vcard->vCard->BDAY)
        && $validate_date->validate($vcard->vCard->BDAY)) {
            $this->date = (string)$vcard->vCard->BDAY;
        }

        if ($vcard->vCard->NICKNAME) {
            $this->name = (string)$vcard->vCard->NICKNAME;
        }

        if ($vcard->vCard->FN) {
            $this->fn = (string)$vcard->vCard->FN;
        }

        if ($vcard->vCard->URL) {
            $this->url = (string)$vcard->vCard->URL;
        }

        if ($vcard->vCard->EMAIL) {
            $this->email = (string)$vcard->vCard->EMAIL->USERID;
        }

        if ($vcard->vCard->ADR) {
            $this->adrlocality = (string)$vcard->vCard->ADR->LOCALITY;
            $this->adrpostalcode = (string)$vcard->vCard->ADR->PCODE;
            $this->adrcountry = (string)$vcard->vCard->ADR->CTRY;
        }

        if (filter_var((string)$vcard->vCard->PHOTO, FILTER_VALIDATE_URL)) {
            $this->photobin = base64_encode(
                requestURL((string)$vcard->vCard->PHOTO, 1)
            );
        } elseif ($vcard->vCard->PHOTO) {
            $this->photobin = (string)$vcard->vCard->PHOTO->BINVAL;
            $this->avatarhash = sha1(base64_decode($this->photobin));
        }

        if ($vcard->vCard->DESC) {
            $this->description = (string)$vcard->vCard->DESC;
        }
    }

    public function createThumbnails()
    {
        if (!$this->photobin) {
            return;
        }

        $p = new Image;
        $p->setKey($this->id);
        $p->fromBase($this->photobin);
        $p->save();

        unset($this->photobin);
    }

    public function getPhoto($size = 'm')
    {
        return !empty($this->id) ? getPhoto($this->id, $size) : null;
    }

    public function setLocation($stanza)
    {
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
            strtotime((string)$stanza->items->item->geoloc->timestamp)
        );
    }

    public function setTune($stanza)
    {
        $this->tuneartist = (string)$stanza->items->item->tune->artist;
        $this->tunelenght = (int)$stanza->items->item->tune->lenght;
        $this->tunerating = (int)$stanza->items->item->tune->rating;
        $this->tunesource = (string)$stanza->items->item->tune->source;
        $this->tunetitle = (string)$stanza->items->item->tune->title;
        $this->tunetrack = (string)$stanza->items->item->tune->track;
    }

    public function setVcard4($vcard)
    {
        if (isset($vcard->bday->date)
        && Validator::date('Y-m-d')->validate($vcard->bday->date)) {
            $this->date = (string)$vcard->bday->date;
        }

        $this->nickname = !empty($vcard->nickname->text)
            ? (string)$vcard->nickname->text
            : null;
        $this->fn = !empty($vcard->fn->text)
            ? (string)$vcard->fn->text
            : null;
        $this->url = !empty($vcard->url->uri)
            ? (string)$vcard->url->uri
            : null;

        $this->adrlocality = !empty($vcard->adr->locality)
            ? (string)$vcard->adr->locality
            : null;
        $this->adrcountry = !empty($vcard->adr->locality)
            ? (string)$vcard->adr->country
            : null;
        $this->adrpostalcode = !empty($vcard->adr->code)
            ? (string)$vcard->adr->code
            : null;

        $this->email = !empty($vcard->email->text)
            ? (string)$vcard->email->text
            : null;
        $this->description = !empty($vcard->note->text)
            ? trim((string)$vcard->note->text)
            : null;
    }

    public function getPlace(): string
    {
        $place = '';

        if ($this->loctext != '') {
            $place .= $this->loctext.' ';
        } else {
            if ($this->locbuilding != '') {
                $place .= $this->locbuilding.' ';
            }
            if ($this->locstreet != '') {
                $place .= $this->locstreet.'<br />';
            }
            if ($this->locpostalcode != '') {
                $place .= $this->locpostalcode.' ';
            }
            if ($this->loclocality != '') {
                $place .= $this->loclocality.'<br />';
            }
            if ($this->locregion != '') {
                $place .= $this->locregion.' - ';
            }
            if ($this->loccountry != '') {
                $place .= $this->loccountry;
            }
        }

        return $place;
    }

    public function getTruenameAttribute(): string
    {
        if ($this->fn) {
            return $this->fn;
        }
        if ($this->nickname) {
            return $this->nickname;
        }
        if ($this->name) {
            return $this->name;
        }

        return explodeJid($this->id)['username'] ?? $this->id;
    }

    public function getJidAttribute(): ?string
    {
        return $this->id;
    }

    public function getAge()
    {
        if ($this->isValidDate()) {
            $age = intval(substr(date('Ymd') - date('Ymd', strtotime($this->date)), 0, -4));
            if ($age != 0) {
                return $age;
            }
        }
    }

    public function getDate()
    {
        if ($this->date == null) {
            return null;
        }

        $dt = new \DateTime($this->date);
        return $dt->format('Y-m-d');
    }

    public function getSearchTerms()
    {
        return cleanupId($this->id).'-'.
            cleanupId($this->truename).'-'.
            cleanupId($this->groupname);
    }

    public function getBlogUrl()
    {
        return \Movim\Route::urlize(
            'blog',
            ($this->user && isset($this->user->nickname))
                ? $this->user->nickname
                : $this->id
        );
    }

    public function isEmpty(): bool
    {
        $this->isValidDate();

        return ($this->fn == null
            && $this->name == null
            && $this->date == null
            && $this->url == null
            && $this->email == null
            && $this->description == null);
    }

    public function isValidDate(): bool
    {
        if (isset($this->date)
            && $this->date != '0000-00-00T00:00:00+0000'
            && $this->date != '1970-01-01 00:00:00'
            && $this->date != '1970-01-01 01:00:00'
            && $this->date != '1970-01-01T00:00:00+0000') {
            return true;
        }
        $this->date = null;
        return false;
    }

    public function isFromMuc(): bool
    {
        return strpos($this->jid, '/') !== false;
    }

    public function isOld(): bool
    {
        return $this->updated !== null
        && (strtotime($this->updated) < mktime( // We update the 1 day old vcards
                                    gmdate("H"),
            gmdate("i")-10,
            gmdate("s"),
            gmdate("m"),
            gmdate("d"),
            gmdate("Y")
                                )
        );
    }

    public function isMe(): bool
    {
        return ($this->id == \App\User::me()->id);
    }
}
