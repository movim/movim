<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    protected $primaryKey = 'message_mid';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function message()
    {
        return $this->belongsTo('App\Message');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'jidfrom');
    }

    public function getTruenameAttribute()
    {
        if ($this->contact) {
            return $this->contact->truename;
        }

        if (str_contains($this->jidfrom, '@')) {
            return explodeJid($this->jidfrom)['username'];
        } else {
            return $this->jidfrom;
        }
    }
}
