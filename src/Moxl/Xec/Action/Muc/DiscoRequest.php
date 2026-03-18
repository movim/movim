<?php

namespace Moxl\Xec\Action\Muc;

use Moxl\Xec\Action\Disco\Request;

class DiscoRequest extends Request
{
    // Different event but same implementation

    public function error(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }
}
