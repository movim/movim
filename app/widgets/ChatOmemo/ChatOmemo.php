<?php

use App\Bundle;
use App\BundleSession;
use Moxl\Xec\Action\OMEMO\AnnounceBundle;
use Moxl\Xec\Action\OMEMO\GetDeviceList;
use Moxl\Xec\Action\OMEMO\SetDeviceList;

class ChatOmemo extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('omemo_getbundle_handle', 'onBundle');
        $this->registerEvent('omemodevices', 'onDevices');

        $this->addjs('libsignal_protocol.js');
        $this->addjs('chatomemo.js');
        $this->addjs('chatomemo_storage.js');
        $this->addjs('chatomemo_db.js');
    }

    public function onDevices($packet)
    {
        list($from, $devices) = array_values($packet->content);

        if ($from == $this->user->id) {
            $this->rpc('ChatOmemo.ownDevicesReceived', $devices);
        }
    }

    public function onBundle($packet)
    {
        $bundle = $packet->content;
        $prekey = $this->extractPreKey($bundle);
        $this->rpc('ChatOmemo.handlePreKey', $bundle->jid, $bundle->bundleid, $prekey);
    }

    public function ajaxGetMissingSessions(string $jid, string $deviceId)
    {
        $bundles = $this->user->bundles()
            ->where('jid', $jid)
            ->whereNotIn('id', function($query) use ($deviceId) {
                $query->select('bundle_id')
                      ->from('bundle_sessions')
                      ->where('deviceid', $deviceId);
            })
            ->get();

        $this->prepareAndHandleSessions($jid, $bundles);
    }

    public function ajaxGetMissingRoomSessions(string $room, string $deviceId)
    {
        $bundles = $this->user->bundles()
            ->whereIn('jid', function ($query) use ($room) {
                $query->select('jid')
                    ->from('members')
                    ->where('conference', $room);
            })
            ->whereNotIn('id', function($query) use ($deviceId) {
                $query->select('bundle_id')
                      ->from('bundle_sessions')
                      ->where('deviceid', $deviceId);
            })
            ->get();

        $this->prepareAndHandleSessions($room, $bundles);
    }

    private function prepareAndHandleSessions(string $jid, $bundles)
    {
        if (!empty($bundles)) {
            $preKeys = [];

            foreach ($bundles as $bundle) {
                $preKeys[$bundle->bundleid] = $this->extractPreKey($bundle);
            }

            Toast::send($this->__('omemo.building_sessions'));
            $this->rpc('ChatOmemo.handlePreKeys', $jid, $preKeys);
        }
    }

    public function ajaxGetSelfMissingSessions(array $resolvedDeviceIds)
    {
        $bundles = $this->user->bundles()
            ->where('jid', $this->user->id)
            ->whereNotIn('bundleid', $resolvedDeviceIds)
            ->get();

        if ($bundles->count() > 0) {
            $preKeys = [];

            foreach ($bundles as $bundle) {
                $preKeys[$bundle->bundleid] = $this->extractPreKey($bundle);
            }

            Toast::send($this->__('omemo.building_own_sessions'));
            $this->rpc('ChatOmemo.handlePreKeys', $this->user->id, $preKeys);
        }

    }

    public function ajaxHttpSetBundleSession(string $jid, string $bundleId, string $deviceId)
    {
        $bundle = $this->user->bundles()
            ->where('jid', $jid)
            ->where('bundleid', $bundleId)
            ->with('sessions')
            ->first();

        if ($bundle && !in_array($deviceId, (array)$bundle->sessions->pluck('deviceid'))) {
            $bundleSession = new BundleSession;
            $bundleSession->bundle_id = $bundle->id;
            $bundleSession->deviceid = $deviceId;
            $bundleSession->save();
        }
    }

    public function ajaxNotifyGeneratingBundle()
    {
        Toast::send($this->__('omemo.generating_bundle'));
        $this->rpc('ChatOmemo.doGenerateBundle');
    }

    public function ajaxEnablingContactState()
    {
        Toast::send($this->__('omemo.encrypted_loading'));
    }

    public function ajaxEnableContactState()
    {
        Toast::send($this->__('omemo.enable_contact'));
    }

    public function ajaxDisableContactState()
    {
        Toast::send($this->__('omemo.disable_contact'));
    }

    public function ajaxNotifyGeneratedBundle()
    {
        Toast::send($this->__('omemo.generated_bundle'));
    }

    public function ajaxGetDevicesList($to)
    {
        Toast::send($this->__('omemo.resolving_devices'));

        $gdl = new GetDeviceList;
        $gdl->setTo($to)
            ->setNotifyBundle(true)
            ->request();
    }

    public function ajaxAnnounceBundle($bundle)
    {
        $devicesList = array_values($this->user->bundles()
                                  ->select('bundleid')
                                  ->where('jid', $this->user->id)
                                  ->pluck('bundleid')
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

    private function extractPreKey(Bundle $bundle): array
    {
        $pickedKey = array_rand($bundle->prekeys);
        return [
            'jid' => $bundle->jid,
            'identitykey' => $bundle->identitykey,
            'signedprekeypublic' => $bundle->signedprekeypublic,
            'signedprekeyid' => $bundle->signedprekeyid,
            'signedprekeysignature' => $bundle->signedprekeysignature,
            'prekey' => ['id' => $pickedKey, 'value' => $bundle->prekeys[$pickedKey]]
        ];
    }
}