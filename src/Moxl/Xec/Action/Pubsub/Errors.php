<?php
/*
 * Errors.php
 *
 * Copyright 2012 edhelas <edhelas@edhelas-laptop>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 */

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;

class Errors extends Action
{
    public function request() {}
    public function handle($stanza, $parent = false) {}

    // Generic Pubsub errors handlers
    public function errorItemNotFound($error)
    {
        $this->event('pubsuberror', "The node does not exist");
    }

    public function errorFeatureNotImplemented($error)
    {
        $this->event('pubsuberror', "Your server doesn't support this feature");
    }

    public function errorNotAuthorized($error)
    {
        $this->event('pubsuberror', "You are not autorized to do this action");
    }

    public function errorServiceUnavailable($error)
    {
        $this->event('pubsuberror', "This service is unavailable");
    }

    public function errorForbidden($error)
    {
        $this->event('pubsuberror', "You are not autorized to do this action");
    }

    public function errorRemoteServerNotFound($error)
    {
        $this->event('pubsuberror', "The server does not exist");
    }

    public function errorUnexpectedRequest($error)
    {
        $this->event('pubsuberror', "Unexpected request");
    }

    public function errorNotAcceptable($error)
    {
        $this->event('pubsuberror', "The server cannot accept this action");
    }
}
