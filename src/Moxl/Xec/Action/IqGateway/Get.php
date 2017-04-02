<?php
/*
 * Request.php
 *
 * Copyright 2017 Stephen Paul Weber <singpolyma@singpolyma.net>
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

namespace Moxl\Xec\Action\IqGateway;

use Moxl\Xec\Action;
use Moxl\Stanza\IqGateway;

class Get extends Action
{
    private $_to;

    public function request()
    {
        $this->store();
        IqGateway::get($this->_to);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $this->prepare($stanza, $parent);
        $this->pack($stanza->query);
        $this->deliver();
    }
}
