<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class AnnounceBundle extends Action
{
    private $_id;
    private $_signedPreKeyPublic;
    private $_signedPreKeySignature;
    private $_identityKey;
    private $_preKeys;

    public function request()
    {
        $this->store();
        OMEMO::announceBundle(
            $this->_id,
            $this->_signedPreKeyPublic,
            $this->_signedPreKeySignature,
            $this->_identityKey,
            $this->_preKeys
        );
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function setSignedPreKeyPublic($signedPreKeyPublic)
    {
        $this->_signedPreKeyPublic = $signedPreKeyPublic;
        return $this;
    }

    public function setSignedPreKeySignature($signedPreKeySignature)
    {
        $this->_signedPreKeySignature = $signedPreKeySignature;
        return $this;
    }

    public function setIdentityKey($identityKey)
    {
        $this->_identityKey = $identityKey;
        return $this;
    }

    public function setPreKeys($preKeys)
    {
        $this->_preKeys = $preKeys;
        return $this;
    }
}
