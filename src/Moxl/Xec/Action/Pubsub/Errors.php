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
    public function error($error) {
        $evt = new \Event();
        $evt->runEvent('pubsuberror', t("Error"));
    }
    
    public function errorItemNotFound($error) {
        $evt = new \Event();
        $evt->runEvent('pubsuberror', t("The node does not exist"));
    }
    
    public function errorFeatureNotImplemented($error) {
        $evt = new \Event();
        $evt->runEvent('pubsuberror', t("Your server doesn't support this feature"));
    }
    
    public function errorNotAuthorized($error) {
        $evt = new \Event();
        $evt->runEvent('pubsuberror', t("You are not autorized to do this action"));
    }
    
    public function errorServiceUnavailable($error) {
        $evt = new \Event();
        $evt->runEvent('pubsuberror', t("This service is unavailable"));  
    }
    
    public function errorForbidden($error) {
        $evt = new \Event();
        $evt->runEvent('pubsuberror', t("You are not autorized to do this action"));
    }

    public function errorRemoteServerNotFound($error) {
        $evt = new \Event();
        $evt->runEvent('pubsuberror', t("The server does not exist"));
    }

    public function errorUnexpectedRequest($error) {
        $evt = new \Event();
        $evt->runEvent('pubsuberror', t("Unexpected request"));
    }

    public function errorNotAcceptable($error) {
        $evt = new \Event();
        $evt->runEvent('pubsuberror', t("The server cannot accept this action"));
    }
}
