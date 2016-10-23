<?php
/*
 * @file Jingle.php
 *
 * @brief Handle Jingle stanza
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

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Ack\Send;

class Jingle extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $from = (string)$parent->attributes()->from;
        $to   = (string)$parent->attributes()->to;
        $id   = (string)$parent->attributes()->id;

        $action = (string)$stanza->attributes()->action;

        $ack = new Send;
        $ack->setTo($from)
            ->setId($id)
            ->request();

        $evt = new \Event;

        switch($action) {
            case 'session-initiate' :
                $evt->runEvent('jingle_sessioninitiate' , [$stanza, $from]);
                break;
            case 'transport-info' :
                $evt->runEvent('jingle_transportinfo'   , $stanza);
                break;
            case 'session-terminate' :
                $evt->runEvent('jingle_sessionterminate', $stanza);
                break;
            case 'session-accept' :
                $evt->runEvent('jingle_sessionaccept'   , $stanza);
                break;
        }
    }
}
