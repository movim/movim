<?php

namespace Moxl\Stanza;

class Form
{
    private $_data;
    private $_type;

    public function __construct($data, $type = 'submit')
    {
        $this->_data = (array)$data;
        $this->_type = $type;
    }

    public function __toString()
    {
        $xml = '<x xmlns="jabber:x:data" type="submit"></x>';
        $node = new \SimpleXMLElement($xml);

        foreach ($this->_data as $key => $value) {
            $field = $node->addChild('field');

            if ($value == 'true') {
                $value = '1';
            }
            if ($value == 'false') {
                $value = '0';
            }

            $field->addChild('value', trim($value->value));
            /*if (isset($value->attributes->required))
                $field->addChild('required', '');*/
            $field->addAttribute('var', $value->attributes->name);
            //$field->addAttribute('type', $value->attributes->type);
            //$field->addAttribute('label', $value->attributes->label);
        }

        $xml = $node->asXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $doc->formatOutput = true;

        return substr($doc->saveXML(), strpos($doc->saveXML(), "\n")+1);
    }
}
