<?php

use Moxl\Xec\Action\Pubsub\ConfigurePersistentStorage;
use Moxl\Xec\Action\Pubsub\CreatePersistentStorage;
use Moxl\Xec\Action\Storage\Set;

class Init extends WidgetCommon {
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

        switch($node) {
            case 'storage:bookmarks' :
                $notif = $this->__('init.bookmark');
                break;
            case 'urn:xmpp:vcard4' :
                $notif = $this->__('init.vcard4');
                break;
            case 'urn:xmpp:avatar:data' :
                $notif = $this->__('init.avatar');
                break;
            case 'http://jabber.org/protocol/geoloc' :
                $notif = $this->__('init.location');
                break;
            case 'urn:xmpp:pubsub:subscription' :
                $notif = $this->__('init.subscriptions');
                break;
            case 'urn:xmpp:microblog:0' :
                $notif = $this->__('init.microblog');
                break;
        }

        RPC::call('Init.setNode', $node);
        Notification::appendNotification($notif, 'success');
    }

    function ajaxCreatePersistentStorage($node)
    {
        $p = new CreatePersistentStorage;
        $p->setTo($this->user->getLogin())
          ->setNode($node)
          ->request();
          
        $p = new ConfigurePersistentStorage;
        $p->setTo($this->user->getLogin())
          ->setNode($node)
          ->request();
    }

    function display()
    {

    }
}
