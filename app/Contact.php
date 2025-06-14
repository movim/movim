<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

use Respect\Validation\Validator;
use Movim\Image;
use Movim\ImageSize;

class Contact extends Model
{
    protected $fillable = ['id', 'nickname'];
    protected $keyType = 'string';
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo('App\User', 'id');
    }

    public function scopeSuggest($query, ?string $like = null)
    {
        return $query
            ->whereIn('id', function ($query) use ($like) {
                $query->select('id')
                    ->from('users')
                    ->where('public', true)
                    ->when($like !== null, function ($query) use ($like) {
                        $query->where('id', 'like', '%' . $like . '%');
                    });
            })
            ->whereNotIn('id', function ($query) {
                $query->select('jid')
                    ->from('rosters')
                    ->where('session_id', User::me()->session->id);
            })
            ->orderByPresence()
            ->where('id', '!=', User::me()->id);
    }

    public function getColorAttribute(): string
    {
        return stringToColor($this->jid);
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

        $this->date = isset($vcard->vCard->BDAY)
            && Validator::date('Y-m-d')->isValid($vcard->vCard->BDAY)
            ? (string)$vcard->vCard->BDAY
            : null;

        $this->name = !empty($vcard->vCard->NICKNAME)
            ? (string)$vcard->vCard->NICKNAME
            : null;

        $this->fn = !empty($vcard->vCard->FN)
            ? (string)$vcard->vCard->FN
            : null;

        $this->url = !empty($vcard->vCard->URL)
            ? (string)$vcard->vCard->URL
            : null;

        $this->email = !empty($vcard->vCard->EMAIL)
            ? (string)$vcard->vCard->EMAIL->USERID
            : null;

        $this->adrlocality = $vcard->vCard->ADR && !empty($vcard->vCard->ADR->LOCALITY)
            ? (string)$vcard->vCard->ADR->LOCALITY
            : null;

        $this->adrpostalcode = $vcard->vCard->ADR && !empty($vcard->vCard->ADR->PCODE)
            ? (string)$vcard->vCard->ADR->PCODE
            : null;

        $this->adrcountry = $vcard->vCard->ADR && !empty($vcard->vCard->ADR->CTRY)
            ? (string)$vcard->vCard->ADR->CTRY
            : null;

        if (
            filter_var((string)$vcard->vCard->PHOTO, FILTER_VALIDATE_URL)
            && in_array($this->avatartype, ['vcard-temp', null])
        ) {
            $this->photobin = base64_encode(
                requestURL((string)$vcard->vCard->PHOTO, 1)
            );
            $this->avatartype = 'vcard-temp';
        } elseif (
            $vcard->vCard->PHOTO
            && in_array($this->avatartype, ['vcard-temp', null])
        ) {
            $this->photobin = (string)$vcard->vCard->PHOTO->BINVAL;
            $this->avatarhash = sha1(base64_decode($this->photobin));
            $this->avatartype = 'vcard-temp';
        }

        $this->description = !empty($vcard->vCard->DESC)
            ? (string)$vcard->vCard->DESC
            : null;
    }

    public function saveBinAvatar()
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

    public function getPicture(ImageSize $size = ImageSize::M): string
    {
        return getPicture($this->id, $this->truename, $size);
    }

    public function getBanner(ImageSize $size = ImageSize::XXL)
    {
        $banner = !empty($this->id) ? getPicture($this->id . '_banner', $this->truename, $size) : null;

        return $banner == null ? $this->getPicture($size) : $banner;
    }

    public function setLocation($item)
    {
        // Clear
        $this->loclatitude = $this->loclongitude = $this->localtitude = $this->loccountry
            = $this->loccountrycode = $this->locregion = $this->locpostalcode = $this->loclocality
            = $this->locstreet = $this->locbuilding = $this->loctext = $this->locuri
            = $this->loctimestamp = null;

        // Fill
        if ($item->geoloc->lat && isLatitude((float)$item->geoloc->lat)) {
            $this->loclatitude      = (string)$item->geoloc->lat;
        }

        if ($item->geoloc->lon && isLongitude((float)$item->geoloc->lon)) {
            $this->loclongitude     = (string)$item->geoloc->lon;
        }

        if ($item->geoloc->alt) {
            $this->localtitude      = (int)$item->geoloc->alt;
        }

        if ($item->geoloc->country) {
            $this->loccountry       = (string)$item->geoloc->country;
        }

        if ($item->geoloc->countrycode) {
            $this->loccountrycode   = (string)$item->geoloc->countrycode;
        }

        if ($item->geoloc->region) {
            $this->locregion        = (string)$item->geoloc->region;
        }

        if ($item->geoloc->postalcode) {
            $this->locpostalcode    = (string)$item->geoloc->postalcode;
        }

        if ($item->geoloc->locality) {
            $this->loclocality      = (string)$item->geoloc->locality;
        }

        if ($item->geoloc->street) {
            $this->locstreet        = (string)$item->geoloc->street;
        }

        if ($item->geoloc->building) {
            $this->locbuilding      = (string)$item->geoloc->building;
        }

        if ($item->geoloc->text) {
            $this->loctext          = (string)$item->geoloc->text;
        }

        if ($item->geoloc->uri) {
            $this->locuri           = (string)$item->geoloc->uri;
        }

        if ($item->geoloc->timestamp) {
            $this->loctimestamp = date(
                'Y-m-d H:i:s',
                strtotime((string)$item->geoloc->timestamp)
            );
        }
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
        $this->date = (isset($vcard->bday->date)
            && Validator::date('Y-m-d')->isValid($vcard->bday->date))
            ? (string)$vcard->bday->date
            : null;

        $this->nickname = !empty($vcard->nickname->text)
            ? (string)$vcard->nickname->text
            : null;
        $this->fn = !empty($vcard->fn->text)
            ? (string)$vcard->fn->text
            : null;
        $this->url = !empty($vcard->url->uri)
            ? (string)$vcard->url->uri
            : null;

        $this->url = !empty($vcard->impp->uri)
            ? $vcard->impp->uri
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

        $this->phone = null;

        if ($vcard->tel) {
            $this->phone = !empty($vcard->tel->uri)
                ? substr((string)$vcard->tel->uri, 4)
                : null;

            // Some clients uses text...
            if ($this->phone == null) {
                $this->phone = !empty($vcard->tel->text)
                    ? (string)$vcard->tel->text
                    : null;
            }
        }

        $this->description = !empty($vcard->note->text)
            ? trim((string)$vcard->note->text)
            : null;
    }

    public function getLocationDistanceAttribute(): ?float
    {
        if (
            in_array('loctimestamp', $this->attributes) && $this->attributes['loctimestamp'] != null
            && \Carbon\Carbon::now()->subDay()->timestamp < strtotime($this->attributes['loctimestamp'])
            && $this->attributes['loclatitude'] != null && $this->attributes['loclongitude'] != null
        ) {
            $me = User::me()->contact;

            if ($me->attributes['loclatitude'] != null && $me->attributes['loclongitude'] != null) {
                return getDistance(
                    $this->attributes['loclatitude'],
                    $this->attributes['loclongitude'],
                    $me->attributes['loclatitude'],
                    $me->attributes['loclongitude'],
                );
            }
        }

        return null;
    }

    public function getLocationUrlAttribute(): ?string
    {
        if (
            array_key_exists('loctimestamp', $this->attributes) && $this->attributes['loctimestamp'] != null
            && \Carbon\Carbon::now()->subDay()->timestamp < strtotime($this->attributes['loctimestamp'])
            && $this->attributes['loclatitude'] != null && $this->attributes['loclongitude'] != null
        ) {
            return 'https://www.openstreetmap.org/' .
                '?mlat=' . round($this->attributes['loclatitude'], 4) .
                '&mlon=' . round($this->attributes['loclongitude'], 4) .
                '/#map=13/';
        }

        return null;
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
        return cleanupId($this->id) . '-' .
            cleanupId($this->truename) . '-' .
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

    public function getSyndicationUrl()
    {
        return \Movim\Route::urlize(
            'blog',
            ($this->user && isset($this->user->nickname))
                ? $this->user->nickname
                : $this->id
        );
    }

    public function isBlocked(): bool
    {
        return \App\User::me()->hasBlocked($this->id, true);
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
        if (
            isset($this->date)
            && $this->date != '0000-00-00T00:00:00+0000'
            && $this->date != '1970-01-01 00:00:00'
            && $this->date != '1970-01-01 01:00:00'
            && $this->date != '1970-01-01T00:00:00+0000'
        ) {
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
        return $this->updated_at !== null
            && \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->updated_at)->addDay()->isBefore(\Carbon\Carbon::now());
    }

    public function isMe(): bool
    {
        return ($this->id == \App\User::me()->id);
    }

    public function isPublic(): bool
    {
        $user = \App\User::where('id', $this->id)->first();
        return ($user && $user->public);
    }

    public function hasLocation()
    {
        return (
            array_key_exists('loclatitude', $this->attributes)
            && array_key_exists('loclongitude', $this->attributes)
            && $this->attributes['loclatitude'] != null
            && $this->attributes['loclongitude'] != null);
    }
}
