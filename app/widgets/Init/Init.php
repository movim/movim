<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Pubsub\ConfigurePersistentStorage;
use Moxl\Xec\Action\Pubsub\CreatePersistentStorage;

class Init extends Base
{
    public function load()
    {
        $this->addjs('init.js');
        $this->registerEvent('pubsub_configurepersistentstorage_handle', 'onConfigured');
        $this->registerEvent('pubsub_createpersistentstorage_handle', 'onCreated');
        $this->registerEvent('pubsub_createpersistentstorage_errorconflict', 'onCreated');
    }

    public function onCreated($package)
    {
        $node = $package->content;
    }

    public function onConfigured($package)
    {
        $node = $package->content;
        $this->rpc('Init.setNode', $node);
    }

    private function createPersistentStorage($node)
    {
        $p = new CreatePersistentStorage;
        $p->setTo($this->user->id)
          ->setNode($node)
          ->request();
    }

    public function ajaxCreatePersistentStorage($node, $max = false)
    {
        $this->createPersistentStorage($node);

        $p = new ConfigurePersistentStorage;
        $p->setTo($this->user->id)
          ->setNode($node);

        if (is_int($max)) {
            $p->setMaxItems($max);
        }

        $p->request();
    }

    public function ajaxCreatePersistentPresenceStorage($node)
    {
        $this->createPersistentStorage($node);

        $p = new ConfigurePersistentStorage;
        $p->setTo($this->user->id)
          ->setNode($node)
          ->setAccessPresence()
          ->setMaxItems(10000)
          ->request();
    }

    public function ajaxCreatePersistentPEPStorage($node)
    {
        $this->createPersistentStorage($node);

        $p = new ConfigurePersistentStorage;
        $p->setTo($this->user->id)
          ->setNode($node)
          ->setAccessPresence()
          ->setMaxItems(1)
          ->request();
    }

    public function display()
    {
        $this->view->assign('hasPubsub', $this->user->hasPubsub());
    }
}
