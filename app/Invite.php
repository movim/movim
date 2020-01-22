<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    public $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['code'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function room()
    {
        return $this->hasOne('App\Contact', 'id', 'resource');
    }

    public static function set($jid, $resource)
    {
        $invitation = \App\Invite::where('user_id', $jid)
                                 ->where('resource', $resource)
                                 ->first();

        if (!$invitation) {
            $invitation = new \App\Invite;
            $invitation->code = generateKey(8);
            $invitation->user_id = $jid;
            $invitation->resource = $resource;
            $invitation->save();
        }

        return $invitation;
    }
}
