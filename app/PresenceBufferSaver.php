<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Vcard\Get;

use App\Presence;
use App\Info;
use App\Contact;

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
                $table = $table->where([
                    ['session_id', $first['session_id']],
                    ['jid', $first['jid']],
                    ['resource', $first['resource']],
                ]);

                $this->_models->skip(1)->each(function ($presence) use ($table) {
                    $table->orWhere([
                        ['session_id', $presence['session_id']],
                        ['jid', $presence['jid']],
                        ['resource', $presence['resource']],
                    ]);
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
                    $resource = !empty($presence['resource']) ? '/' . $presence['resource'] : '';
                    $nodes->put($presence['node'], $presence['jid'] . $resource);

                    // Vcards
                    if (isset($presence['avatarhash'])) {
                        $fullJid = !empty($presence['resource'])
                            ? $presence['jid'].'/'.$presence['resource']
                            : $presence['jid'];

                        $jid = ($presence['muc'])
                            ? (($presence['mucjid'])
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
                                        ->get(['id', 'avatarhash'])->pluck('avatarhash', 'id');

                    // Remove the existing Contacts
                    $avatarHashes = $avatarHashes->reject(fn ($avatarhash, $jid) =>
                        $contactsHashes->has($jid) && $contactsHashes->get($jid) == $avatarhash

                        // If the contact stored is actually the one we received the presence from

                        // It's another contact that has the same avatar and we are in a MUC
                        /*elseif (strpos($avatarHashes->get($contact->avatarhash), '/') != false) {
                            $p = new Picture;
                            $p->fromKey($contact->id);
                            $p->set($avatarHashes->get($contact->avatarhash));
                        }*/

                    );

                    // Request the others, take 100 max for the moment to prevent spamming issues
                    $avatarHashes->take(100)->each(function ($jid, $avatarhash) {
                        $r = new Get;
                        $r->setAvatarhash($avatarhash)
                          ->setTo($jid)
                          ->request();
                    });
                }

            } catch (\Exception $e) {
                DB::rollback();
                \Utils::error($e->getMessage());
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
        return $presence->muc ? $presence->jid.$presence->mucjid : $presence->jid.$presence->resource;
    }
}