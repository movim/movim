<?php

use Movim\Widget\Base;

class Caps extends Base
{
    private $_table = [];
    private $_nslist;

    public function load()
    {
        $this->addcss('caps.css');
    }

    public function isImplemented($client, $key)
    {
        $class = in_array($this->_nslist[$key]['ns'], $client) ? 'yes' : 'no';

        return '
            <td
                class="'.$class.' '.$this->_nslist[$key]['category'].'"
                title="XEP-'.$key.': '.$this->_nslist[$key]['name'].'">'.
                $key.'
            </td>';
    }

    public function display()
    {
        $clients = \App\Info::whereCategory('client')->orderBy('name')->get();
        $oldname = '';

        foreach ($clients as $c) {
            $parts = explode(' ', $c->name);
            $parts = explode('#', reset($parts));
            $clientname = reset($parts);

            if ($oldname == $clientname) {
                continue;
            }

            if (!isset($this->_table[$c->name])) {
                $this->_table[$c->name] = [];
            }

            foreach ($c->features as $f) {
                if (!in_array($f, $this->_table[$c->name])) {
                    array_push($this->_table[$c->name], (string)$f);
                }
            }

            $oldname = $clientname;
        }

        ksort($this->_table);

        $this->_nslist = getXepNamespace();

        $this->view->assign('table', $this->_table);
        $this->view->assign('nslist', $this->_nslist);
    }
}
