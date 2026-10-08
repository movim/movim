<?php

namespace Moxl\Xec\Payload;

class Packet
{
    public ?string $from;
    public $content;

    public function pack($content, ?string $from = null)
    {
        $this->content = $content;

        if ($from != null) {
            $this->from = $from;
        }

        return $this;
    }
}
