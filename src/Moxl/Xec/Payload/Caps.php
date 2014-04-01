<?php
/*
 * @file Post.php
 * 
 * @brief Handle incoming Entity Capabilities (XEP 0115 Entity Capabilities)
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

namespace Moxl;

use Moxl\Xec\Action\Disco\Request;

class Caps extends Payload
{
    public function handle($stanza, $parent = false) {
        $node = $stanza->attributes()->node.'#'.$stanza->attributes()->ver;
        $to = (string)$parent->attributes()->from;
        
        $cd = new \modl\CapsDAO();
        $c = $cd->get($node);

        if(!$c) {
            $d = new Request;
            $d->setTo($to)
              ->setNode($node)
              ->request();
        }
    }
}
