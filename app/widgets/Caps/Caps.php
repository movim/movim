<?php

class Caps extends \Movim\Widget\Base
{
    private $_table = [];
    private $_nslist;

    function load()
    {
        $this->addcss('caps.css');
    }

    function isImplemented($client, $key)
    {
        $class = in_array($this->_nslist[$key]['ns'], $client) ? 'yes' : 'no';

        return '
            <td
                class="'.$class.' '.$this->_nslist[$key]['category'].'"
                title="XEP-'.$key.': '.$this->_nslist[$key]['name'].'">'.
                $key.'
            </td>';
    }

    function getCapabilityName($node)
    {
        $capability = App\Capability::where('node', 'like', $node . '%')->first();

        if ($capability) {
            $parts = explode(' ', $capability->name);
            return reset($parts);
        } else {
            return $node;
        }
    }

    function display()
    {
        $clients = App\Capability::where('category', 'client')->orderBy('name')->get();
        $oldname = '';

        foreach ($clients as $c) {
            $parts = explode(' ', $c->name);
            $parts = explode('#', reset($parts));
            $clientname = reset($parts);

            if ($oldname == $clientname) continue;

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

        $presences = App\Presence::where('node', '!=', '')
                                 ->where('resource', '!=', '')
                                 ->orderBy('node')
                                 ->pluck('node')->toArray();
        $stats = [];
        $total = 0;

        foreach ($presences as $presence) {
            list($client, $version) = explode('#', $presence);
            if (!isset($stats[$client])) $stats[$client] = 0;

            $stats[$client]++;
            $total++;
        }

        arsort($stats);

        $this->view->assign('table', $this->_table);
        $this->view->assign('nslist', $this->_nslist);
        $this->view->assign('stats', $stats);
        $this->view->assign('total', $total);
    }
}
