<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;

class Errors extends Action
{
    public function request()
    {
    }
    public function handle($stanza, $parent = false)
    {
    }

    // Generic Pubsub errors handlers
    public function errorItemNotFound($error)
    {
        $this->deliver();
    }

    public function errorFeatureNotImplemented($error)
    {
        $this->deliver();
    }

    public function errorNotAuthorized($error)
    {
        $this->deliver();
    }

    public function errorServiceUnavailable($error)
    {
        $this->deliver();
    }

    public function errorForbidden($error)
    {
        $this->deliver();
    }

    public function errorRemoteServerNotFound($error)
    {
        $this->deliver();
    }

    public function errorUnexpectedRequest($error)
    {
        $this->deliver();
    }

    public function errorNotAcceptable($error)
    {
        $this->deliver();
    }
}
