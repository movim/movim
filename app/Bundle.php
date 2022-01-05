<?php

namespace App;

use Movim\Model;

class Bundle extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['user_id', 'jid', 'bundleid'];

    public function capability()
    {
        return $this->hasOne('App\Info', 'node', 'node')
                    ->whereNull('server');
    }

    public function set(string $jid, string $bundleId, $bundle)
    {
        $this->user_id = \App\User::me()->id;
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

    public function sameAs(Bundle $bundle)
    {
        return (
            isset($this->attributes['prekeys'])
            && $this->attributes['prekeys'] == $bundle->attributes['prekeys']
            && $this->attributes['signedprekeypublic'] == $bundle->attributes['signedprekeypublic']
            && $this->attributes['signedprekeyid'] == $bundle->attributes['signedprekeyid']
            && $this->attributes['signedprekeysignature'] == $bundle->attributes['signedprekeysignature']
            && $this->attributes['identitykey'] == $bundle->attributes['identitykey']
        );
    }

    public function getFingerprintAttribute()
    {
        $buffer = base64_decode($this->identitykey);
        $hex = unpack('H*', $buffer);
        return implode(' ', str_split(substr($hex[1], 2), 8));
    }

    public function getPrekeysAttribute()
    {
        return unserialize($this->attributes['prekeys']);
    }
}