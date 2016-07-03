<?php

class Caps extends \Movim\Widget\Base
{
    private $_table = [];
    private $_nslist;

    function load() {
        $this->addcss('caps.css');
    }

    function isImplemented($client, $key) {
        if(in_array($this->_nslist[$key]['ns'], $client)) {
            return '
                <td
                    class="yes '.$this->_nslist[$key]['category'].'"
                    title="XEP-'.$key.': '.$this->_nslist[$key]['name'].'">'.
                    $key.'
                </td>';
        } else {
            return '
                <td
                    class="no  '.$this->_nslist[$key]['category'].'"
                    title="XEP-'.$key.': '.$this->_nslist[$key]['name'].'">'.
                    $key.'
                </td>';
        }
    }

    function display()
    {
        $cd = new \modl\CapsDAO();
        $clients = $cd->getClients();

        foreach($clients as $c) {
            if(!isset($this->_table[$c->name])) {
                $this->_table[$c->name] = [];
            }

            $features = unserialize($c->features);
            foreach($features as $f) {
                if(!in_array($f, $this->_table[$c->name])) {
                    array_push($this->_table[$c->name], (string)$f);
                }
            }
        }

        ksort($this->_table);

        $this->_nslist = getXepNamespace();

        $this->view->assign('table', $this->_table);
        $this->view->assign('nslist', $this->_nslist);
    }
}
