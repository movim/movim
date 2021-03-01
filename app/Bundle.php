<?php

namespace App;

use Movim\Model;

class Bundle extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['user_id', 'jid', 'bundle_id'];

    public function set(string $jid, string $bundleId, $bundle)
    {
        $this->user_id = \App\User::me()->id;
        $this->jid = $jid;
        $this->bundle_id = $bundleId;

        $this->prekeypublic = (string)$bundle->signedPreKeyPublic;
        $this->prekeysignature = (string)$bundle->signedPreKeySignature;
        $this->identitykey = (string)$bundle->identityKey;

        $prekeys = [];

        foreach ($bundle->prekeys->preKeyPublic as $prekey) {
            $prekeys[(string)$prekey->attributes()->preKeyId] = (string)$prekey;
        }

        $this->prekeys = serialize($prekeys);
    }

    public function getPrekeysAttribute()
    {
        return unserialize($this->attributes['prekeys']);
    }
}