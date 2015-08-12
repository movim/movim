<?php
/*
 * Set.php
 * 
 * Copyright 2013 edhelas <edhelas@edhelas-laptop>
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
 
namespace Moxl\Xec\Action\Vcard4;

use Moxl\Xec\Action;
use Moxl\Stanza\Vcard4;

class Set extends Action
{
    private $_data;
    
    public function request() 
    {
        $this->store();
        Vcard4::set($this->_data);
    }
    
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }
    
    public function handle($stanza, $parent = false) {
        $cd = new \Modl\ContactDAO();
        $this->pack($cd->get());
        $this->deliver();
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('myvcard4invalid', 'vcardfeaturenotimpl');
    }
    
    public function errorBadRequest($stanza) {
        $evt = new \Event();
        $evt->runEvent('myvcard4invalid', 'vcardbadrequest');
    }

    public function errorNotAllowed($stanza) {
        $evt = new \Event();
        $evt->runEvent('myvcard4invalid', 'vcardfeaturenotimpl');
    }
}
