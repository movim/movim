<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    public function save(array $options = [])
    {
        try {
            parent::save($options);

        } catch (\Exception $e) {
            /*
             * When a member is received by two accounts simultaenously
             * in different processes they can be saved using the insert state
             * in the DB causing an error
             */
        }
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'jid');
    }

    public function getTruenameAttribute()
    {
        if ($this->contact && $this->contact->truename) {
            return $this->contact->truename;
        }

        return explodeJid($this->jid)['username'] ?? $this->jid;
    }
}
