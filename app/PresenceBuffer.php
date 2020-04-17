<?php

namespace App;

use App\Presence;

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
        if ($this->_models->isNotEmpty()) {
            try {
                Presence::insert($this->_models->toArray());
            } catch (\Exception $e) {
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
        // Only presences that can be inserted, not updated
        if ($presence->created_at == null) {
            $this->_models[$presence->muc ? $presence->jid.$presence->mucjid : $presence->jid.$presence->resource] = $presence->toArray();
            $this->_calls->push($call);
        } else {
            $presence->save();
            $call();
        }
    }
}
