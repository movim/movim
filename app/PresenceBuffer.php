<?php

namespace App;

use App\Presence;

class PresenceBuffer
{
    protected static $instance;
    private $saver = null;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function save()
    {
        if ($this->saver) {
            $this->saver->save();
            $this->saver = null;
        }
    }

    public function append(Presence $presence, $call)
    {
        if ($this->saver == null) {
            $this->saver = new PresenceBufferSaver;
        }

        $this->saver->append($presence, $call);
    }
}
