<?php

use Moxl\Xec\Action\Pubsub\ConfigurePersistentStorage;
use Moxl\Xec\Action\Pubsub\CreatePersistentStorage;
use Moxl\Xec\Action\Storage\Set;

class Init extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('init.js');
        $this->registerEvent('pubsub_configurepersistentstorage_handle', 'onConfigured');
        $this->registerEvent('pubsub_createpersistentstorage_handle', 'onCreated');
        $this->registerEvent('pubsub_createpersistentstorage_errorconflict', 'onCreated');
    }

    function onCreated($package)
    {
        $node = $package->content;
    }

    function onConfigured($package)
    {
        $node = $package->content;
        $this->rpc('Init.setNode', $node);
    }

    private function createPersistentStorage($node)
    {
        $p = new CreatePersistentStorage;
        $p->setTo($this->user->jid)
          ->setNode($node)
          ->request();
    }

    function ajaxCreatePersistentStorage($node, $max = false)
    {
        $this->createPersistentStorage($node);

        $p = new ConfigurePersistentStorage;
        $p->setTo($this->user->jid)
          ->setNode($node);

        if (is_int($max)) {
            $p->setMaxItems($max);
        }

        $p->request();
    }

    function ajaxCreatePersistentPresenceStorage($node)
    {
        $this->createPersistentStorage($node);

        $p = new ConfigurePersistentStorage;
        $p->setTo($this->user->jid)
          ->setNode($node)
          ->setAccessPresence()
          ->setMaxItems(10000)
          ->request();
    }

    function ajaxCreatePersistentPEPStorage($node)
    {
        $this->createPersistentStorage($node);

        $p = new ConfigurePersistentStorage;
        $p->setTo($this->user->jid)
          ->setNode($node)
          ->setAccessPresence()
          ->setMaxItems(1)
          ->request();
    }
}
