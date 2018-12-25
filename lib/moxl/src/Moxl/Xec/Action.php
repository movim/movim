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
        $obj->object = $this;
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
        foreach ($instances as $key => $i) {
            if ($i['time'] < (int)$t-30) {
                Utils::log('Action : Clean this request after 30 sec of no feedback '.$i['type']);
                unset($instances[$key]);
            }
        }

        return $instances;
    }

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
