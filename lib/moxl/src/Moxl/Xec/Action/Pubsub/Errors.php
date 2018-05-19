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
