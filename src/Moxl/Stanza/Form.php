<?php
/*
 * @file Form.php
 *
 * @desc Generate XMPP Data Forms http://xmpp.org/extensions/xep-0004.html
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

namespace Moxl\Stanza;

class Form {
    private $_data;
    private $_type;
    
    public function __construct($data, $type = 'submit')
    {
        $this->_data = (array)$data;
        $this->_type = $type;
    }

    public function __toString() {
        $xml = '<x xmlns="jabber:x:data"></x>';
        $node = new \SimpleXMLElement($xml);
        
        foreach($this->_data as $key => $value) {
            $field = $node->addChild('field');
            if($value == 'true')
                $value = '1';
            if($value == 'false')
                $value = '0';
                
            $field->addChild('value', trim($value->value));
            if(isset($value->attributes->required))
                $field->addChild('required', '');
            $field->addAttribute('var', $value->attributes->name);
            $field->addAttribute('type', $value->attributes->type);                
            $field->addAttribute('label', $value->attributes->label);     
        }

        $xml = $node->asXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $doc->formatOutput = true;
        
        return substr($doc->saveXML() , strpos($doc->saveXML(), "\n")+1 );
    }
}
