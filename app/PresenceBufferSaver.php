<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Vcard\Get;

use App\Presence;
use App\Info;
use App\Contact;
use Movim\Scheduler;

class PresenceBufferSaver
{
    private $_models = null;
    private $_calls = null;

    public function __construct()
    {
        $this->_models = collect();
        $this->_calls = collect();
    }

    public function save()
    {
        if ($this->_models->count() > 0) {
            try {
                DB::beginTransaction();

                // We delete all the presences that might already be there
                $table = DB::table('presences');
                $first = $this->_models->first();
                $table = $table->where(function ($query) use ($first) {
                    $query->where('session_id', $first['session_id'])
                          ->where('jid', $first['jid'])
                          ->where('resource', $first['resource'])
                          ->where('mucjid', $first['mucjid']);
                });

                $this->_models->skip(1)->each(function ($presence) use ($table) {
                    $table->orWhere(function ($query) use ($presence) {
                        $query->where('session_id', $presence['session_id'])
                              ->where('jid', $presence['jid'])
                              ->where('resource', $presence['resource'])
                              ->where('mucjid', $presence['mucjid']);
                    });
                });
                $table->delete();

                // And we save it
                Presence::insert($this->_models->toArray());
                DB::commit();

                /**
                 * Handle the Capabilities & Vcards
                 */
                $nodes = collect();
                $avatarHashes = collect();

                $this->_models->each(function ($presence) use (&$nodes, &$avatarHashes) {
                    // Capabilities
                    if ($presence['node']) {
                        $resource = !empty($presence['resource']) ? '/' . $presence['resource'] : '';
                        $nodes->put($presence['node'], $presence['jid'] . $resource);
                    }

                    // Vcards
                    if (isset($presence['avatarhash'])) {
                        $fullJid = !empty($presence['resource'])
                            ? $presence['jid'] . '/' . $presence['resource']
                            : $presence['jid'];

                        $jid = ($presence['muc'])
                            ? (($presence['mucjid'] != '')
                                ? $presence['mucjid']
                                : $fullJid)
                            : $presence['jid'];

                        $avatarHashes->put($presence['avatarhash'], $jid);
                    }
                });

                $infos = Info::whereIn('node', $nodes->keys())->get();

                // Remove the already saved capabilities
                $infos->each(function ($info) use (&$nodes) {
                    if ($nodes->has($info->node) && !$info->isEmptyFeatures()) {
                        $nodes->pull($info->node);
                    }
                });

                // Request the others
                $nodes->each(function ($to, $node) {
                    $d = new Request;
                    $d->setTo($to)
                        ->setNode($node)
                        ->request();
                });

                // Memory leak there
                if ($avatarHashes->count() > 0) {
                    $contactsHashes = Contact::whereIn('avatarhash', $avatarHashes->keys())
                        ->whereNotNull('avatartype')
                        ->get(['id', 'avatarhash'])->pluck('avatarhash', 'id');

                    // Remove the existing Contacts
                    $avatarHashes = $avatarHashes->reject(
                        fn ($jid, $avatarhash) =>
                        $contactsHashes->has($jid) && $contactsHashes->get($jid) == $avatarhash
                    );

                    $avatarHashes->each(function ($jid, $avatarhash) {
                        Scheduler::getInstance()->append('avatar_' . $jid . '_' . $avatarhash, function () use ($jid, $avatarhash) {
                            // Last check before firing the request, the avatar might have been received in the meantime
                            $contact = Contact::where('avatarhash', $avatarhash)->where('id', $jid)->first();

                            if (!$contact || $contact->avatartype == null) {
                                $r = new Get;
                                $r->setAvatarhash($avatarhash)
                                    ->setTo($jid)
                                    ->request();
                            }
                        });
                    });
                }
            } catch (\Exception $e) {
                DB::rollback();
                logError($e);
            }
            $this->_models = collect();
        }

        if ($this->_calls->isNotEmpty()) {
            $this->_calls->each(fn ($call) => $call());
            $this->_calls = collect();
        }
    }

    public function append(Presence $presence, $call)
    {
        $this->_models[$this->getPresenceKey($presence)] = $presence->toArray();
        $this->_calls->push($call);
    }

    private function getPresenceKey(Presence $presence)
    {
        return $presence->jid . $presence->mucjid . $presence->resource;
    }
}
