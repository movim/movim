<?php

namespace Movim\Librairies;

use Movim\Route;

class XMPPtoForm
{
    private $xmpp;
    private $formType;
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
        $this->formType = $xmpp->attributes()->type;
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
        $reported = false;
        $items = [];

        foreach ($this->xmpp->children() as $element) {
            switch ($element->getName()) {
                case 'title':
                    $this->outTitle($element);
                    break;
                case 'instructions':
                    $this->outP($element);
                    break;
                case 'reported':
                    $reported = $element;
                case 'item':
                    array_push($items, $element);
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

                    $type = isset($element->attributes()->type) ? $element->attributes()->type : 'text-single';
                    if ($this->formType == 'result') {
                        $this->outLabel($this->html, $element);
                        switch ($type) {
                            case 'hidden':
                                break;
                            case 'boolean':
                                $this->outCheckbox($element);
                                break;
                            case 'jid-single':
                            case 'jid-multi':
                                $this->outJidLinks($element);
                                break;
                            case 'text-multi':
                                $this->outMultilineText($element);
                                break;
                            default:
                                $this->outMultiP($element->value);
                        }
                    } else {
                        switch ($type) {
                            case 'boolean':
                                $this->outCheckbox($element);
                                break;
                            case 'text-single':
                                if ($element['var'] == 'pubsub#max_items') {
                                    $this->outInput($element, false, 'max');
                                } elseif ($element['var'] == 'muc#roomconfig_pubsub') {
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
                                $this->outLabel($this->html, $element);
                                $this->outMultiP($element->value);
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

        if ($reported) {
            $cols = [];
            $colType = [];

            $table = $this->html->createElement('table');
            $table->setAttribute("class", "table");
            $header = $this->html->createElement('tr');
            foreach ($reported->children() as $element) {
                if ($element->getName() != 'field') {
                    continue;
                }
                array_push($cols, (string)$element->attributes()->var);
                $type = isset($element->attributes()->type) ? $element->attributes()->type : 'text-single';
                array_push($colType, $type);
                $header->appendChild($this->html->createElement('th', (string)$element->attributes()->label));
            }
            $table->appendChild($header);

            foreach ($items as $item) {
                // fields in item are not required to be in the same order
                // so order them by the order in reported
                $cells = [];
                foreach ($item->children() as $element) {
                    if ($element->getName() != 'field') {
                        continue;
                    }
                    $idx = array_search((string)$element->attributes()->var, $cols);
                    if ($colType[$idx] == 'jid-single') {
                        $link = $this->html->createElement('a', (string)$element->value);
                        $link->setAttribute('href', Route::urlize('contact', $element->value));
                        $cells[$idx] = $this->html->createElement('td');
                        $cells[$idx]->appendChild($link);
                    } else {
                        $cells[$idx] = $this->html->createElement('td', (string)$element->value);
                    }
                }

                $row = $this->html->createElement('tr');
                for ($i = 0; $i < count($cols); $i++) {
                    if (isset($cells[$i])) {
                        $row->appendChild($cells[$i]);
                    } else {
                        $row->appendChild($this->html->createElement('td'));
                    }
                }
                $table->appendChild($row);
            }

            $this->html->appendChild($table);
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

    private function outMultiP($arr)
    {
        foreach ((array)$arr as $value) {
            $this->outP((string)$value);
        }
    }

    private function outMultilineText($element) {
        $p = $this->html->createElement('p');
        foreach ((array)$element->value as $value) {
            $p->appendChild($this->html->createTextNode(htmlspecialchars_decode((string)$value)));
            $p->appendChild($this->html->createElement('br'));
        }
        $this->html->appendChild($p);
    }

    private function outJidLinks($element) {
        foreach ((array)$element->value as $value) {
            $p = $this->html->createElement('p');
            $link = $this->html->createElement('a', $value);
            $link->setAttribute('href', Route::urlize('contact', $value));
            $p->appendChild($link);
            $this->html->appendChild($p);
        }
    }

    private function outCheckbox($s)
    {
        $container = $this->html->createElement('div');
        $container->setAttribute('class', $s['var'] == 'muc#roomconfig_passwordprotectedroom'
            ? 'control disabled'
            : 'control');

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
        $i->setAttribute('class', 'material-symbols');
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

        if ($this->formType == 'result') {
            $input->setAttribute('disabled', 'disabled');
        }

        if ((string)$s->value === 'true' || (string)$s->value === '1') {
            $input->setAttribute('checked', 'checked');
        }

        $div->appendChild($input);

        $label = $this->html->createElement('label');
        $label->setAttribute('for', $s['var']);
        $div->appendChild($label);

        $p = $this->html->createElement('p', $s['label'] ?? $s['var']);
        $p->setAttribute('class', 'normal all');
        $li->appendChild($p);
    }

    private function outTextarea($s)
    {
        $container = $this->html->createElement('div');
        $this->html->appendChild($container);

        $textarea = $this->html->createElement('textarea');
        $textarea->setAttribute('type', $s['type']);
        $textarea->setAttribute('label', $s['label'] ?? $s['var']);
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

    private function outLabel($container, $s, $forceLabel = false)
    {
        if (!$forceLabel && !$s['label']) {
            return;
        }

        $txt = $s['label'] ?? $s['var'];
        $label = $this->html->createElement('label', $txt);
        $label->setAttribute('for', $s['var']);
        $label->setAttribute('title', $txt);
        $container->appendChild($label);
    }

    private function outInput($s, $type = false, $forceValue = null)
    {
        $container = $this->html->createElement('div');
        $this->html->appendChild($container);

        if ($s['var'] == 'muc#roomconfig_roomsecret') {
            $container->setAttribute('class', 'disabled');
        }

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

        $input->setAttribute('label', $s['label'] ?? $s['var']);

        if ($s->required) {
            $input->setAttribute('required', 'required');
        }

        if ($forceValue) {
            $input->setAttribute('disabled', 'disabled');
            $input->setAttribute('value', $forceValue);
        } else {
            foreach ($s->children() as $value) {
                if ($value->getName() == 'value') {
                    $input->setAttribute('value', $value);
                }
            }
        }

        if ($s['var'] == 'username') {
            $input->setAttribute('pattern', '[a-z0-9_-]*');
        }

        $container->appendChild($input);

        $this->outLabel($container, $s, true);
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
        $select->setAttribute('label', $s['label'] ?? $s['var']);
        $select->setAttribute('id', $s['var']);
        $select->setAttribute('name', $s['var']);

        $subscriptions = me()->subscriptions()
            ->notComments()
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
        $select->setAttribute('label', $s['label'] ?? $s['var']);
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

        $label = $this->html->createElement('label', $s['label'] ?? $s['var']);
        $label->setAttribute('for', $s['var']);
        $label->setAttribute('title', $s['label'] ?? $s['var']);
        $container->appendChild($label);
    }
}
