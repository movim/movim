<?php

namespace App;

use Movim\Model;

class Bundle extends Model
{
    public $incrementing = false;
    public const OMEMO_BUNDLE = 'eu.siacs.conversations.axolotl.bundles:';
    protected $primaryKey = ['user_id', 'jid', 'bundleid'];

    public function set(string $jid, string $bundleId, $bundle)
    {
        $this->user_id = me()->id;
        $this->jid = $jid;
        $this->bundleid = $bundleId;

        $this->signedprekeypublic = (string)$bundle->signedPreKeyPublic;
        $this->signedprekeyid = (int)$bundle->signedPreKeyPublic->attributes()->signedPreKeyId;
        $this->signedprekeysignature = (string)$bundle->signedPreKeySignature;

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

    public function extractPreKey(): ?array
    {
        $preKeys = unserialize($this->attributes['prekeys']);

        if (empty($preKeys)) return null;

        $pickedKey = array_rand($preKeys);

        return [
            'jid' => $this->jid,
            'identitykey' => $this->identitykey,
            'signedprekeypublic' => $this->signedprekeypublic,
            'signedprekeyid' => $this->signedprekeyid,
            'signedprekeysignature' => $this->signedprekeysignature,
            'prekey' => ['id' => $pickedKey, 'value' => $this->prekeys[$pickedKey]]
        ];
    }
}
