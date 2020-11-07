<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Vcard\Get;

use App\Presence;
use App\Info;
use App\Contact;

class PresenceBuffer
{
    protected static $instance;
    private $_models = null;
    private $_calls = null;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        global $loop;

        $this->_models = collect();
        $this->_calls = collect();

        $loop->addPeriodicTimer(1, function () {
            $this->save();
        });
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
                 * Handle the Capabilities
                 */
                $nodes = collect();
                $this->_models->each(function ($presence) use (&$nodes) {
                    $resource = !empty($presence['resource']) ? '/' . $presence['resource'] : '';
                    $nodes->put($presence['node'], $presence['jid'] . $resource);
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

                /**
                 * Handle the Vcards
                 */
                $avatarHashes = collect();
                $this->_models->each(function ($presence) use (&$avatarHashes) {
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

                if ($avatarHashes->count() > 0) {
                    $contacts = Contact::whereIn('avatarhash', $avatarHashes->keys())
                                        ->get();

                    // Remove the existing Contacts
                    $contacts->each(function ($contact) use (&$avatarHashes) {
                        // If the contact stored is actually the one we received the presence from
                        if ($avatarHashes->get($contact->avatarhash) == $contact->id) {
                            $avatarHashes->pull($contact->avatarhash);
                        }
                        // It's another contact that has the same avatar and we are in a MUC
                        /*elseif (strpos($avatarHashes->get($contact->avatarhash), '/') != false) {
                            $p = new Picture;
                            $p->fromKey($contact->id);
                            $p->set($avatarHashes->get($contact->avatarhash));
                        }*/
                    });

                    // Request the others
                    $avatarHashes->each(function ($jid, $avatarhash) {
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
            $this->_calls->each(function ($call) {
                $call();
            });
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
