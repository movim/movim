<?php

namespace App\Widgets\ChatOmemo;

use App\Widgets\Toast\Toast;
use Moxl\Xec\Action\OMEMO\AnnounceBundle;
use Moxl\Xec\Action\OMEMO\GetBundle;
use Moxl\Xec\Action\OMEMO\GetDeviceList;
use Moxl\Xec\Action\OMEMO\SetDeviceList;

class ChatOmemo extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('omemo_getbundle_handle', 'onBundle');
        $this->registerEvent('omemo_getbundle_last', 'onLastBundle');
        $this->registerEvent('omemodevices', 'onDevices');
        $this->registerEvent('omemo_getdevicelist_error', 'onDeviceListError');

        $this->addjs('chatomemo.js');
        $this->addjs('chatomemo_storage.js');
        $this->addjs('chatomemo_db.js');
    }

    public function onDevices($packet)
    {
        list($from, $devices) = array_values($packet->content);

        $this->rpc(
            $from == $this->user->id
                ? 'ChatOmemo.ownDevicesReceived'
                : 'ChatOmemo.devicesReceived',
            $from,
            $devices
        );
    }

    public function onBundle($packet)
    {
        $bundle = $packet->content;
        $prekey = $bundle->extractPreKey();

        if ($prekey) {
            $this->rpc('ChatOmemo.handlePreKey', $bundle->jid, $bundle->bundleid, $prekey);
        }
    }

    public function onLastBundle($packet)
    {
        $this->rpc('ChatOmemo.bundlesRefreshed', $packet->content);
    }

    public function onDeviceListError($packet)
    {
        if ($packet->content == $this->user->id) {
            $this->rpc('ChatOmemo.initiateBundle', []);
        }

        $this->rpc('ChatOmemo.bundlesRefreshError', $packet->content);
    }

    public function ajaxGetBundle(string $jid, string $bundleId)
    {
        $gb = new GetBundle;
        $gb->setTo($jid)
            ->setId($bundleId)
            ->request();
    }

    public function ajaxEnableContactState()
    {
        Toast::send($this->__('omemo.enable_contact'));
    }

    public function ajaxEnableRoomState()
    {
        Toast::send($this->__('omemo.enable_room'));
    }

    public function ajaxDisableContactState()
    {
        Toast::send($this->__('omemo.disable_contact'));
    }

    public function ajaxNotifyGeneratedBundle()
    {
        Toast::send($this->__('omemo.generated_bundle'));
    }

    public function ajaxGetDevicesList(string $to)
    {
        Toast::send($this->__('omemo.resolving_devices'));

        $gdl = new GetDeviceList;
        $gdl->setTo($to)
            ->request();
    }

    public function ajaxAnnounceBundle($bundle, array $devicesIds)
    {
        if (!array_key_exists($bundle->deviceId, $devicesIds)) {
            array_push($devicesIds, $bundle->deviceId);
        }

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
        $sdl->setList($devicesIds)
            ->request();
    }

    public function ajaxRefreshDeviceList()
    {
        $devicesList = array_values($this->user->bundles()
            ->select('bundleid')
            ->where('jid', $this->user->id)
            ->pluck('bundleid')
            ->toArray());

        $sdl = new SetDeviceList;
        $sdl->setList($devicesList)
            ->request();
    }
}
