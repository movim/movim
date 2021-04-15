<?php

use App\Bundle;
use Moxl\Xec\Action\OMEMO\AnnounceBundle;
use Moxl\Xec\Action\OMEMO\GetDeviceList;
use Moxl\Xec\Action\OMEMO\SetDeviceList;

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
        $prekey = $this->extractPreKey($bundle);
        $this->rpc('ChatOmemo.handlePreKey', $bundle->jid, $bundle->bundle_id, $prekey);
    }

    public function ajaxNotifyGeneratingBundle()
    {
        Toast::send($this->__('omemo.generating_bundle'));
        $this->rpc('ChatOmemo.doGenerateBundle');
    }

    public function ajaxNotifyGeneratedBundle()
    {
        Toast::send($this->__('omemo.generated_bundle'));
    }

    public function ajaxGetBundles(string $jid, array $exceptBundleIds = [])
    {
        $bundles = $this->user->bundles()->where('jid', $jid);

        if (!empty($exceptBundleIds)) {
            $bundles = $bundles->whereNotIn('bundle_id', $exceptBundleIds);
        }

        $bundles = $bundles->get();

        foreach ($bundles as $bundle) {
            $prekey = $this->extractPreKey($bundle);
            $this->rpc('ChatOmemo.handlePreKey', $bundle->jid, $bundle->bundle_id, $prekey);
        }
    }

    public function ajaxGetDevicesList($to)
    {
        $gdl = new GetDeviceList;
        $gdl->setTo($to)
            ->request();
    }

    public function ajaxAnnounceBundle($bundle)
    {
        $devicesList = array_values($this->user->bundles()
                                  ->select('bundle_id')
                                  ->where('jid', $this->user->id)
                                  ->pluck('bundle_id')
                                  ->toArray());

        if (($key = array_search($bundle->deviceId, $devicesList)) !== false) {
            unset($devicesList[$key]);
        }

        array_push($devicesList, $bundle->deviceId);

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
        $sdl->setList($devicesList)
            ->request();
    }

    private function extractPreKey(Bundle $bundle): array
    {
        $pickedKey = array_rand($bundle->prekeys);
        return [
            'identitykey' => $bundle->identitykey,
            'prekeypublic' => $bundle->prekeypublic,
            'prekeysignature' => $bundle->prekeysignature,
            'prekey' => ['id' => $pickedKey, 'value' => $bundle->prekeys[$pickedKey]]
        ];
    }
}