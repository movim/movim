<?php
class XMPPtoForm{
    private $fieldset;
    private $xmpp;
    private $html;

    public function __construct(){
        $this->fieldset = 0;
        $this->html = new \DOMDocument('1.0', 'UTF-8');
        $this->xmpp = '';
    }

    public function getHTML($xmpp){
        $this->setXMPP($xmpp);
        $this->create();
        return $this->html->saveXML();
    }

    public function setXMPP($xmpp){
        $this->xmpp = $xmpp;
    }

    public function create(){
        $this->xmpp = str_replace('xmlns=', 'ns=', $this->xmpp);
        $x = new SimpleXMLElement($this->xmpp);

        foreach($x->children() as $element){
            switch($element->getName()){
                case "title":
                    $this->outTitle($element);
                    break;
                case "instructions":
                    $this->outP($element);
                    break;
                case "field":
                    //if($element['type'] != 'hidden' && $element['type'] != 'fixed')
                    //    $this->html .='<div>';
                    switch($element['type']){
                        case "boolean":
                            $this->outCheckbox($element);
                            break;
                        //case "fixed":
                        //    $this->outBold($element);
                        //    break;
                        case "text-single":
                            $this->outInput($element, "", "");
                            break;
                        case "text-multi":
                            $this->outTextarea($element);
                            break;
                        case "text-private":
                            $this->outInput($element, "password", "");
                            break;
                        case "hidden":
                            $this->outHiddeninput($element);
                            break;
                        case "list-multi":
                            $this->outList($element, "multiple");
                            break;
                        case "list-single":
                            $this->outList($element, "");
                            break;
                        case "jid-multi":
                            $this->outInput($element, "email", "multiple");
                            break;
                        case "jid-single":
                            $this->outInput($element, "email", "");
                            break;
                        default:
                            //$this->html .= "";
                    }
                    //if($element['type'] != 'hidden')
                    //    $this->html .='</div>';
                    break;
                case 'url':

                    break;
                /*XML without <x> element*/
                case 'username':
                case 'email':
                case 'password':
                    //$this->html .='<div class="element">';
                        $this->outGeneric($element->getName());
                    //$this->html .='</div>';
                    break;
                default:
                    //$this->html .= "";
            }
        }
        /*if($this->fieldset>0){
            $this->html .= '</fieldset>';
        }*/
    }

    private function outGeneric($s){
        $div = $this->html->createElement('div');
        $div->setAttribute('class', 'element');
        $this->html->appendChild($div);

        $input = $this->html->createElement('input');
        $input->setAttribute('type', $s);
        $input->setAttribute('id', $s);
        $input->setAttribute('name', 'generic_'.$s);
        $input->setAttribute('required', 'required');
        
        $div->appendChild($input);

        $label = $this->html->createElement('label', $s);
        $label->setAttribute('for', $s);
        $div->appendChild($label);
    }
    private function outTitle($s){
        $ul = $this->html->createElement('ul');
        $ul->setAttribute('class', 'thin simple');
        $this->html->appendChild($ul);

        $li = $this->html->createElement('li');
        $li->appendChild($this->html->createElement('span', $s));

        $ul->appendChild($li);
    }

    private function outP($s){
        $ul = $this->html->createElement('ul');
        $ul->setAttribute('class', 'thin simple');
        $this->html->appendChild($ul);

        $li = $this->html->createElement('li');
        $ul->appendChild($li);

        $li->appendChild($this->html->createElement('p', $s));
    }

    private function outUrl($s) {
        $a = $this->html->createElement('a', $s->getName());
        $a->setAttribute('href', $s->getName());
        $this->html->appendChild($a);
    }
    /*
    private function outBold($s){
        if($this->fieldset > 0){
            $this->html .= '</fieldset>';
        }
        $this->html .= '<fieldset><legend>'.$s->value.'</legend><br />';
        $this->fieldset ++;
    }
    */
    private function outCheckbox($s){
        $container = $this->html->createElement('div');
        $this->html->appendChild($container);

        $div = $this->html->createElement('div');
        $div->setAttribute('class', 'select');
        $container->appendChild($div);

        $select = $this->html->createElement('select');
        $select->setAttribute('type', $s['type']);
        $select->setAttribute('label', $s['label']);
        $select->setAttribute('id', $s['var']);
        $select->setAttribute('name', $s['var']);

        if($s->required)
            $select->setAttribute('required', 'required');
        
        $div->appendChild($select);

        $option = $this->html->createElement('option', __('button.bool_yes'));
        $option->setAttribute('value', 'true');
        if(isset($s->value) || $s->value == "true" || $s->value == "1")
            $option->setAttribute('selected', 'selected');
        $select->appendChild($option);

        $option = $this->html->createElement('option', __('button.bool_no'));
        $option->setAttribute('value', 'false');
        if(!isset($s->value) || $s->value == "false" || $s->value == "0")
            $option->setAttribute('selected', 'selected');
        $select->appendChild($option);

        $label = $this->html->createElement('label', $s['label']);
        $label->setAttribute('for', $s['var']);
        $container->appendChild($label);
    }

    private function outTextarea($s){
        $container = $this->html->createElement('div');
        $this->html->appendChild($container);

        $textarea = $this->html->createElement('textarea');
        $textarea->setAttribute('type', $s['type']);
        $textarea->setAttribute('label', $s['label']);
        $textarea->setAttribute('id', $s['var']);
        $textarea->setAttribute('name', $s['var']);

        if($s->required)
            $textarea->setAttribute('required', 'required');

        foreach($s->children() as $value){
            if($value->getName() == "value"){
                $textarea->nodeValue .= $value . "\n";
            }
        }

        if(empty($textarea->nodeValue)) {
            $textarea->nodeValue = ' ';
        }
        
        $container->appendChild($textarea);

        $label = $this->html->createElement('label', $s['label']);
        $label->setAttribute('for', $s['var']);
        $container->appendChild($label);
    }

    private function outInput($s, $type, $multiple){
        $container = $this->html->createElement('div');
        $this->html->appendChild($container);
        
        $input = $this->html->createElement('input');
        $input->setAttribute('id', $s['var']);
        $input->setAttribute('name', $s['var']);
        $input->setAttribute('type', $type);
        $input->setAttribute('title', $s->desc);
        $input->setAttribute('type', $s['type']);
        $input->setAttribute('label', $s['label']);

        if($s->required)
            $input->setAttribute('required', 'required');

        foreach($s->children() as $value){
            if($value->getName() == "value"){
                $input->setAttribute('value', $value);
            }
        }

        if($s['var'] == 'username')
            $input->setAttribute('pattern', '[a-z0-9_-]*');
        
        $container->appendChild($input);

        $label = $this->html->createElement('label', $s['label']);
        $label->setAttribute('for', $s['var']);
        $container->appendChild($label);
    }

    private function outHiddeninput($s){
        $input = $this->html->createElement('input');
        $input->setAttribute('name', $s['var']);
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('value', $s->value);

        $this->html->appendChild($input);
    }

    private function outList($s, $multiple){
        $container = $this->html->createElement('div');
        $this->html->appendChild($container);

        $div = $this->html->createElement('div');
        $div->setAttribute('class', 'select');
        $container->appendChild($div);

        $select = $this->html->createElement('select');
        $select->setAttribute('type', $s['type']);
        $select->setAttribute('label', $s['label']);
        $select->setAttribute('id', $s['var']);
        $select->setAttribute('name', $s['var']);

        if($s->required)
            $select->setAttribute('required', 'required');
        
        $div->appendChild($select);

        if(count($s->xpath('option')) > 0){
            foreach($s->option as $option){
                if(isset($option->attributes()->label)) {
                    $opt = $this->html->createElement('option', $option->attributes()->label);
                } else {
                    $opt = $this->html->createElement('option', $option->value);
                }

                $opt->setAttribute('value', $option->value);
                if(
                    in_array(
                        (string)$opt->nodeValue,
                        array_map(
                            function($sxml) {
                                return (string)$sxml;
                            },
                            $s->xpath('value')
                        )
                    )
                ) {
                    $opt->setAttribute('selected', 'selected');
                }
                $select->appendChild($opt);
            }
        }
        else{
            foreach($s->value as $option){
                $option = $this->html->createElement('option', $option);
                $option->setAttribute('value', $option['label']);
                $option->setAttribute('selected', 'selected');
                $select->appendChild($option);
            }
        }

        $label = $this->html->createElement('label', $s['label']);
        $label->setAttribute('for', $s['var']);
        $container->appendChild($label);
    }
}

class FormtoXMPP
{
    private $_form;
    private $_inputs;

    public function __construct(array $inputs)
    {
        $this->_form = new \DOMDocument('1.0', 'UTF-8');
        $this->_inputs = $inputs;
    }

    public function appendToX(\DomDocument $dom)
    {
        $fields = $this->_form->getElementsByTagName('field');
        $list = $dom->getElementsByTagName('x');
            
        foreach($fields as $field) {
            $field = $dom->importNode($field, true);
            $list[0]->appendChild($field);
        }
    }

    public function create()
    {
        foreach($this->_inputs as $key => $value) {
            $container = $this->_form->createElement('container');
            $this->_form->appendChild($container);
           
            $field = $this->_form->createElement('field');
            $container->appendChild($field);

            $val = $this->_form->createElement('value');
            $field->appendChild($val);

            if($value === 'true') {
                $val->nodeValue = '1';
            }
            
            if($value === 'false') {
                $val->nodeValue = '0';
            }

            if(is_bool($value)) {
                $val->nodeValue = ($value) ? '1' : '0';
            }

            if(empty($val->nodeValue)
            && $value !== 'false' // WTF PHP !!!
            ) {
                $val->nodeValue = trim($value);
            }
            
            $field->setAttribute('var', trim($key));
        }

        return $this->_form->saveXML();
    }
}
?>
