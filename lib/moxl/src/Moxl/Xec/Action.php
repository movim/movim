<?php

namespace Moxl\Xec;

use Moxl\Utils;
use Moxl\Xec\Payload\Payload;

use Movim\Session;

abstract class Action extends Payload
{
    final public function store()
    {
        $sess = Session::start();

        // Generating the iq key.
        $id = \generateKey(6);

        $sess->set('id', $id);
        $sess->set($id, $this);
    }

    /*
     * Clean old IQ requests
     */
    /*private function clean($instances)
    {
        $t = time();
        foreach ($instances as $key => $i) {
            if ($i['time'] < (int)$t-30) {
                \Utils::info('Action : Clean this request after 30 sec of no feedback '.$i['type']);
                unset($instances[$key]);
            }
        }

        return $instances;
    }*/

    public function __call($name, $args)
    {
        if (substr($name, 0, 3) == 'set') {
            $property = '_' . strtolower(substr($name, 3));
            $this->$property = $args[0];

            return $this;
        }
    }

    abstract public function request();
}
