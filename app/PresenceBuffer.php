<?php

namespace App;

use App\Presence;

class PresenceBuffer
{
    protected static $instance;
    private $saver = null;

    private function __construct(private ?User $me = null)
    {
    }

    public static function getInstance(?User $me = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($me);
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
            $this->saver = new PresenceBufferSaver($this->me);
        }

        $this->saver->append($presence, $call);
    }
}
