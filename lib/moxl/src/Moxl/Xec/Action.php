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

        // We serialize the current object
        $obj = new \StdClass;
        $obj->type   = get_class($this);
        $obj->object = serialize($this);
        $obj->time   = time();

        //$_instances = $this->clean($_instances);

        $sess->set($id, $obj);
    }

    /*
     * Clean old IQ requests
     */
    private function clean($instances)
    {
        $t = time();
        foreach($instances as $key => $i) {
            if($i['time'] < (int)$t-30) {
                Utils::log('Action : Clean this request after 30 sec of no feedback '.$i['type']);
                unset($instances[$key]);
            }
        }

        return $instances;
    }

    abstract public function request();
}
