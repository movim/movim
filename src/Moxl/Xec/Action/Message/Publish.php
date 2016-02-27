<?php
/*
 * Publish.php
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

namespace Moxl\Xec\Action\Message;

use Moxl\Xec\Action;
use Moxl\Stanza\Message;
use Moxl\Stanza\Muc;

class Publish extends Action
{
    private $_to;
    private $_content;
    private $_html;
    private $_muc = false;
    private $_encrypted = false;
    private $_id = false;
    private $_replace = false;

    public function request()
    {
        $this->store();
        if($this->_muc)
            Muc::message($this->_to, $this->_content, $this->_html, $this->_id);
        elseif($this->_encrypted)
            Message::encrypted($this->_to, $this->_content, $this->_html, $this->_id, $this->_replace);
        else
            Message::message($this->_to, $this->_content, $this->_html, $this->_id, $this->_replace);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setMuc()
    {
        $this->_muc = true;
        return $this;
    }

    public function getMuc()
    {
        return $this->_muc;
    }

    public function setEncrypted($bool)
    {
        $this->_encrypted = $bool;
        return $this;
    }

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function setHTML($html)
    {
        $this->_html = $html;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function setReplace($replace)
    {
        $this->_replace = $replace;
        return $this;
    }

    public function handle($stanza, $parent = false) {
        if($this->_muc) {
            $m = new \Moxl\Xec\Payload\Message;
            $m->handle($stanza, $parent);
        }
    }
}
