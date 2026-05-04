<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['conference', 'jid'];

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

    public static function saveMany(array $members)
    {
        $now = \Carbon\Carbon::now();
        $members = collect($members)->map(function (array $data) use ($now) {
            return array_merge([
                'created_at' => $now,
                'updated_at' => $now,
            ], $data);
        })->all();

        return Member::insert($members);
    }

    public function contact()
    {
        return $this->hasOne(Contact::class, 'id', 'jid');
    }

    public function getTruenameAttribute(): string
    {
        if ($this->contact && $this->contact->truename) {
            return $this->contact->truename;
        }

        return explodeJid($this->jid)['username'] ?? $this->jid;
    }

    public function getColorAttribute(): string
    {
        return stringToColor($this->jid);
    }
}
