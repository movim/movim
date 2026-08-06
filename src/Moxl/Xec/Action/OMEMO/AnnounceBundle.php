<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;

class AnnounceBundle extends Action
{
    private string $_id;
    private string $_signedPreKeyPublic;
    private string $_signedPreKeySignature;
    private string $_identityKey;
    private array $_preKeys;

    public function request()
    {
        $this->store();
        $this->iq(OMEMO::announceBundle(
            $this->_id,
            $this->_signedPreKeyPublic,
            $this->_signedPreKeySignature,
            $this->_identityKey,
            $this->_preKeys
        ), type: 'set');
    }

    public function setId(string $id)
    {
        $this->_id = $id;
        return $this;
    }

    public function setSignedPreKeyPublic(string $signedPreKeyPublic)
    {
        $this->_signedPreKeyPublic = $signedPreKeyPublic;
        return $this;
    }

    public function setSignedPreKeySignature(string $signedPreKeySignature)
    {
        $this->_signedPreKeySignature = $signedPreKeySignature;
        return $this;
    }

    public function setIdentityKey(string $identityKey)
    {
        $this->_identityKey = $identityKey;
        return $this;
    }

    public function setPreKeys(array $preKeys)
    {
        $this->_preKeys = $preKeys;
        return $this;
    }
}
