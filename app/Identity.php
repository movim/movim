<?php

namespace App;

use Movim\Model;

class Identity extends Model
{
    protected $primaryKey = ['info_id', 'category', 'type'];
    public $incrementing = false;

    public function info()
    {
        return $this->belongsTo('App\Info');
    }

    public function save(array $options = [])
    {
        try {
            parent::save($options);
        } catch (\Exception $e) {
            /**
             * Existing info are saved in the DB
             */
        }
    }
}
