<?php

class XMPPtoForm
{
    private $xmpp;
    private $stanza;
    private $html;

    public function __construct()
    {
        $this->html = new \DOMDocument('1.0', 'UTF-8');
        $this->xmpp = '';
    }

    public function getHTML(\SimpleXMLElement $xmpp, $stanza = false)
    {
        $this->xmpp = $xmpp;
        $this->stanza = $stanza;
        $this->create();
        return $this->html->saveHTML();
    }

    public function getArray($xmpp)
    {
        $array = [];

        foreach ($xmpp->children() as $element) {
            $array[(string)$element->attributes()->var] = (string)$element->value;
        }

        return $array;
    }

    public function create()
    {
        foreach ($this->xmpp->children() as $element) {
            switch ($element->getName()) {
                case 'title':
                    $this->outTitle($element);
                    break;
                case 'instructions':
                    $this->outP($element);
                    break;
                case 'field':
                    if (
                        isset($element->media)
                        && (string)$element->media->attributes()->xmlns == 'urn:xmpp:media-element'
                        && isset($element->media->uri)
                    ) {
                        $uri = parse_url($element->media->uri);
                        switch ($uri['scheme']) {
                            case 'cid':
                                foreach ($this->stanza->xpath('//data[@cid=\'' . $uri['path'] . '\']') as $data) {
                                    $this->outImage('data:' . $data->attributes()->type . ';base64,' . (string)$data);
                                }
                                break;
                            case 'http':
                            case 'https':
                                $this->outImage($uri);
                                break;
                        }
                    }

                    if (isset($element->attributes()->type)) {
                        switch ($element->attributes()->type) {
                            case 'boolean':
                                $this->outCheckbox($element);
                                break;
                            case 'text-single':
                                if ($element['var'] == 'muc#roomconfig_pubsub') {
                                    $this->outSelectPubsubNode($element);
                                } else {
                                    $this->outInput($element, '');
                                }
                                break;
                            case 'text-multi':
                                $this->outTextarea($element);
                                break;
                            case 'text-private':
                                $this->outInput($element, 'password');
                                break;
                            case 'hidden':
                                $this->outHiddeninput($element);
                                break;
                            case 'list-multi':
                                //$this->outList($element, true);
                                break;
                            case 'list-single':
                                $this->outList($element);
                                break;
                            case 'jid-multi':
                                $this->outInput($element, 'email');
                                break;
                            case 'jid-single':
                                $this->outInput($element, 'email');
                                break;
                            case 'fixed':
                                $this->outP((string)$element->value);
                                break;
                            default:
                                $this->outInput($element, 'text');
                                break;
                        }
                    }
                    break;
                case 'url':
                    break;
                case 'username':
                case 'email':
                case 'password':
                    $this->outGeneric($element->getName());
                    break;
                default:
                    //$this->html .= '';
            }
        }
    }

    private function outGeneric($s)
    {
        $div = $this->html->createElement('div');
        $div->setAttribute('class', 'element');
        $this->html->appendChild($div);

        $input = $this->html->createElement('input');
        $input->setAttribute('type', $s);
        $input->setAttribute('id', $s);
        $input->setAttribute('name', 'generic_' . $s);
        $input->setAttribute('required', 'required');

        $div->appendChild($input);

        $label = $this->html->createElement('label', $s);
        $label->setAttribute('for', $s);
        $div->appendChild($label);
    }

    private function outTitle($s)
    {
        $title = $this->html->createElement('h3', $s);
        $this->html->appendChild($title);
    }

    private function outImage(string $src)
    {
        $div = $this->html->createElement('div');
        $img = $this->html->createElement('img');
        $img->setAttribute('src', $src);
        $div->appendChild($img);
        $this->html->appendChild($div);
    }

    private function outP($s)
    {
        $this->html->appendChild($this->html->createElement('p', $s));
    }

    private function outCheckbox($s)
    {
        $container = $this->html->createElement('div');
        $container->setAttribute('class', 'control');
        $this->html->appendChild($container);

        $ul = $this->html->createElement('ul');
        $ul->setAttribute('class', 'list fill');
        $container->appendChild($ul);

        $li = $this->html->createElement('li');
        $ul->appendChild($li);

        $primary = $this->html->createElement('span');
        $primary->setAttribute('class', 'primary icon gray');
        $li->appendChild($primary);

        $i = $this->html->createElement('i', \varToIcons($s['var']));
        $i->setAttribute('class', 'material-icons');
        $primary->appendChild($i);

        $span = $this->html->createElement('span');
        $span->setAttribute('class', 'control');
        $li->appendChild($span);

        $div = $this->html->createElement('div');
        $div->setAttribute('class', 'checkbox');
        $span->appendChild($div);

        $input = $this->html->createElement('input');
        $input->setAttribute('type', 'checkbox');
        $input->setAttribute('id', $s['var']);
        $input->setAttribute('name', $s['var']);

        if ($s->required) {
            $input->setAttribute('required', 'required');
        }

        if ((string)$s->value === 'true' || (string)$s->value === '1') {
            $input->setAttribute('checked', 'checked');
        }

        $div->appendChild($input);

        $label = $this->html->createElement('label');
        $label->setAttribute('for', $s['var']);
        $div->appendChild($label);

        $p = $this->html->createElement('p', $s['label']);
        $p->setAttribute('class', 'normal all');
        $li->appendChild($p);
    }

    private function outTextarea($s)
    {
        $container = $this->html->createElement('div');
        $this->html->appendChild($container);

        $textarea = $this->html->createElement('textarea');
        $textarea->setAttribute('type', $s['type']);
        $textarea->setAttribute('label', $s['label']);
        $textarea->setAttribute('id', $s['var']);
        $textarea->setAttribute('name', $s['var']);

        if ($s->required) {
            $textarea->setAttribute('required', 'required');
        }

        foreach ($s->children() as $value) {
            if ($value->getName() == 'value') {
                $textarea->nodeValue .= $value . "\n";
            }
        }

        if (empty($textarea->nodeValue)) {
            $textarea->nodeValue = ' ';
        }

        $container->appendChild($textarea);

        $label = $this->html->createElement('label', $s['label']);
        $label->setAttribute('for', $s['var']);
        $label->setAttribute('title', $s['label']);
        $container->appendChild($label);
    }

    private function outInput($s, $type = false)
    {
        $container = $this->html->createElement('div');
        $this->html->appendChild($container);

        $input = $this->html->createElement('input');
        $input->setAttribute('id', $s['var']);
        $input->setAttribute('name', $s['var']);
        $input->setAttribute('type', $type);
        $input->setAttribute('title', $s->desc);

        if ($type) {
            $input->setAttribute('type', $type);
        } else {
            $input->setAttribute('type', $s['type']);
        }

        $input->setAttribute('label', $s['label']);

        if ($s->required) {
            $input->setAttribute('required', 'required');
        }

        foreach ($s->children() as $value) {
            if ($value->getName() == 'value') {
                $input->setAttribute('value', $value);
            }
        }

        if ($s['var'] == 'username') {
            $input->setAttribute('pattern', '[a-z0-9_-]*');
        }

        $container->appendChild($input);

        $label = $this->html->createElement('label', $s['label']);
        $label->setAttribute('for', $s['var']);
        $label->setAttribute('title', $s['label']);
        $container->appendChild($label);
    }

    private function outHiddeninput($s)
    {
        $input = $this->html->createElement('input');
        $input->setAttribute('name', $s['var']);
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('value', $s->value);

        $this->html->appendChild($input);
    }

    private function outSelectPubsubNode($s)
    {
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

        $subscriptions = \App\User::me()->subscriptions()
            ->where('node', 'not like', 'urn:xmpp:microblog:0:comments/%')
            ->orderBy('server')->orderBy('node')
            ->get();

        $server = null;

        $option = $this->html->createElement('option', '-');
        $option->setAttribute('value', '');
        $select->appendChild($option);

        foreach ($subscriptions as $subscription) {
            if ($subscription->server != $server) {
                $optgroup = $this->html->createElement('optgroup');
                $optgroup->setAttribute('label', $subscription->server);
                $select->appendChild($optgroup);
            }

            $value = 'xmpp:' . $subscription->server . '?;node=' . $subscription->node;
            $option = $this->html->createElement('option', $subscription->node);
            $option->setAttribute('value', $value);

            if ($value == $s->value) {
                $option->setAttribute('selected', 'selected');
            }

            $optgroup->appendChild($option);

            $server = $subscription->server;
        }

        $div->appendChild($select);

        $label = $this->html->createElement('label', __('input.muc_pubsub_node'));
        $label->setAttribute('for', $s['var']);
        $label->setAttribute('title', __('input.muc_pubsub_node'));
        $container->appendChild($label);
    }

    private function outList($s, bool $multi = false)
    {
        $container = $this->html->createElement('div');
        $this->html->appendChild($container);

        $div = $this->html->createElement('div');
        $div->setAttribute('class', $multi ? 'select multi' : 'select');

        $container->appendChild($div);

        $select = $this->html->createElement('select');
        $select->setAttribute('type', $s['type']);
        $select->setAttribute('label', $s['label']);
        $select->setAttribute('id', $s['var']);
        $select->setAttribute('name', $s['var']);

        if ($multi) {
            $select->setAttribute('multiple', 'multiple');
        }

        if ($s->required) {
            $select->setAttribute('required', 'required');
        }

        $div->appendChild($select);

        if (count($s->xpath('option')) > 0) {
            if (count($s->xpath('option')) == 1) {
                $select->setAttribute('disabled', 'disabled');
            }

            foreach ($s->option as $option) {
                if (isset($option->attributes()->label)) {
                    $opt = $this->html->createElement('option', $option->attributes()->label);
                } else {
                    $opt = $this->html->createElement('option', $option->value);
                }

                $opt->setAttribute('value', $option->value);
                if (
                    in_array(
                        (string)$option->value,
                        array_map(
                            function ($sxml) {
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
        } else {
            foreach ($s->value as $option) {
                $label = $option['label'];
                $option = $this->html->createElement('option', $option);
                $option->setAttribute('value', $label);
                $option->setAttribute('selected', 'selected');
                $select->appendChild($option);
            }
        }

        $label = $this->html->createElement('label', $s['label']);
        $label->setAttribute('for', $s['var']);
        $label->setAttribute('title', $s['label']);
        $container->appendChild($label);
    }
}

class FormtoXMPP
{
    private $_form;
    private $_inputs;

    public function __construct($inputs)
    {
        $this->_form = new \DOMDocument('1.0', 'UTF-8');
        $this->_inputs = $inputs;
    }

    public function appendToX(\DomDocument $dom)
    {
        $fields = $this->_form->getElementsByTagName('field');
        $list = $dom->getElementsByTagName('x');

        foreach ($fields as $field) {
            $field = $dom->importNode($field, true);
            $list[0]->appendChild($field);
        }
    }

    public function create()
    {
        foreach ($this->_inputs as $key => $value) {
            $container = $this->_form->createElement('container');
            $this->_form->appendChild($container);

            $field = $this->_form->createElement('field');
            $container->appendChild($field);

            $val = $this->_form->createElement('value');
            $field->appendChild($val);

            if (is_bool($value->value)) {
                $val->nodeValue = ($value->value) ? '1' : '0';
            } else {
                if ($value->value === 'true') {
                    $val->nodeValue = '1';
                }

                if ($value->value === 'false') {
                    $val->nodeValue = '0';
                } elseif (empty($val->nodeValue)) {
                    $val->nodeValue = trim($value->value);
                }
            }

            $field->setAttribute('var', trim($key));
        }

        return $this->_form->saveXML();
    }
}
