<?php

namespace App;

use Movim\Model;

class Identity extends Model
{
    public $primaryKey = ['info_id', 'category', 'type'];
    public $incrementing = false;

    public function info()
    {
        return $this->belongsTo('App\Info');
    }

    public static function saveMany($identities)
    {
        if ($identities->isNotEmpty()) {
            Identity::upsert($identities->map(function (Identity $identity) {
                return [
                    'info_id' => $identity->info_id,
                    'category' => $identity->category,
                    'type' => $identity->type,
                    'lang' => $identity->lang,
                    'name' => $identity->name
                ];
            })->all(), $identities->first()->primaryKey);
        }
    }
}
