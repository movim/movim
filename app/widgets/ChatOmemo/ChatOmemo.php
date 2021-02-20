<?php

use Moxl\Xec\Action\OMEMO\AnnounceBundle;
use Moxl\Xec\Action\OMEMO\GetDeviceList;
use Moxl\Xec\Action\OMEMO\SetDeviceList;
use Moxl\Xec\Action\OMEMO\Message;

class ChatOmemo extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('omemo_getbundle_handle', 'onBundle');

        $this->addjs('libsignal_protocol.js');
        $this->addjs('chatomemo.js');
        $this->addjs('chatomemo_storage.js');
        $this->addjs('chatomemo_db.js');
    }

    public function onBundle($packet)
    {
        $bundle = $packet->content;

        $prekey = [
            'identitykey' => $bundle->identitykey,
            'prekeypublic' => $bundle->prekeypublic,
            'prekeysignature' => $bundle->prekeysignature,
            'prekey' => ['id' => array_key_first($bundle->prekeys), 'value' => $bundle->prekeys[array_key_first($bundle->prekeys)]]
        ];

        $this->rpc('ChatOmemo.handlePreKey', $bundle->jid, $bundle->bundle_id, $prekey);
    }

    public function ajaxSendMessage(string $to, int $sid, object $keys, string $iv, string $payload)
    {
        \Utils::debug($sid);
        \Utils::debug(serialize($keys));
        \Utils::debug($iv);
        \Utils::debug($payload);

        $m = new Message;
        $m->setTo($to)
          ->setSid($sid)
          ->setKeys($keys)
          ->setIv($iv)
          ->setPayload($payload)
          ->request();
    }

    public function ajaxGetDevicesList($to)
    {
        $gdl = new GetDeviceList;
        $gdl->setTo($to)
            ->request();
    }

    public function ajaxAnnounceBundle($bundle)
    {
        $preKeys = [];
        foreach ($bundle->preKeys as $preKey) {
            array_push($preKeys, (string)$preKey->key);
        }

        $ab = new AnnounceBundle;
        $ab->setId($bundle->deviceId)
           ->setSignedPreKeyPublic($bundle->signedPreKey->publicKey)
           ->setSignedPreKeySignature($bundle->signedPreKey->signature)
           ->setIdentityKey($bundle->identityKey)
           ->setPreKeys($preKeys)
           ->request();

        $sdl = new SetDeviceList;
        $sdl->setList([$bundle->deviceId])
            ->request();
    }
}