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

        if ($prekey) {
            $this->rpc('ChatOmemo.handlePreKey', $bundle->jid, $bundle->bundleid, $prekey);
        }
    }

    public function ajaxGetMissingSessions(string $jid, array $resolvedDeviceIds)
    {
        $bundles = $this->user->bundles()
            ->where('jid', $jid)
            ->whereNotIn('bundleid', $resolvedDeviceIds)
            ->get();

        $this->prepareAndHandleSessions($jid, $bundles);
        $this->closeMissingSessions($jid, $resolvedDeviceIds);
    }

    public function ajaxGetMissingRoomSessions(string $room, $resolvedDeviceIds)
    {
        $flattenBundles = collect();
        foreach ($resolvedDeviceIds as $member => $bundlesIds) {
            foreach ($bundlesIds as $bundleId) {
                $flattenBundles->push([$member, $bundleId]);
            }
        }

        $bundles = $this->user->bundles()
            ->whereIn('jid', function ($query) use ($room) {
                $query->select('jid')
                    ->from('members')
                    ->where('conference', $room);
            })
            ->whereNotIn('id', function($query) use ($flattenBundles) {
                $query = $query->select('id')
                      ->from('bundles');

                $bundle = $flattenBundles->shift();
                if ($bundle) {
                    $query->where(function ($query) use ($bundle) {
                        $query->where('jid', $bundle[0])
                              ->where('bundleid', $bundle[1]);
                    });
                }

                foreach ($flattenBundles as $bundle) {
                    $query->orWhere(function ($query) use ($bundle) {
                        $query->where('jid', $bundle[0])
                              ->where('bundleid', $bundle[1]);
                    });
                }
            })
            ->get();

        $this->prepareAndHandleSessions($room, $bundles);
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
                $prekey = $this->extractPreKey($bundle);
                if ($prekey) {
                    $preKeys[$bundle->bundleid] = $prekey;
                }
            }

            Toast::send($this->__('omemo.building_own_sessions'));
            $this->rpc('ChatOmemo.handlePreKeys', $this->user->id, $preKeys);
        }

        $this->closeMissingSessions($this->user->id, $resolvedDeviceIds);
    }

    private function prepareAndHandleSessions(string $jid, $bundles)
    {
        if (!empty($bundles)) {
            $preKeys = [];

            foreach ($bundles as $bundle) {
                $prekey = $this->extractPreKey($bundle);
                if ($prekey) {
                    $preKeys[$bundle->bundleid] = $prekey;
                }
            }

            Toast::send($this->__('omemo.building_sessions'));
            $this->rpc('ChatOmemo.handlePreKeys', $jid, $preKeys);
        }
    }

    private function closeMissingSessions(string $jid, array $resolvedDeviceIds)
    {
        $devicesIds = array_values(
            collect($resolvedDeviceIds)->diff(
                $this->user->bundles()
                        ->where('jid', $jid)
                        ->whereIn('bundleid', $resolvedDeviceIds)
                        ->pluck('bundleid')
                        ->toArray())->toArray()
        );

        if (count($devicesIds) > 0) {
            $this->rpc('ChatOmemo.closeSessions', $jid, $devicesIds);
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

    private function extractPreKey(Bundle $bundle): ?array
    {
        if (empty($bundle->prekeys)) return null;

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