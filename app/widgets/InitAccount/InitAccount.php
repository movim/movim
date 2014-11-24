<?php

use Moxl\Xec\Action\Pubsub\ConfigurePersistentStorage;
use Moxl\Xec\Action\Pubsub\CreatePersistentStorage;
use Moxl\Xec\Action\Storage\Set;

class InitAccount extends WidgetCommon {
    function load()
    {
        $this->registerEvent('configurepersistentstorage_handle', 'onConfigured');
        $this->registerEvent('nodecreated', 'onNodeCreated');
        $this->registerEvent('nodecreationerror', 'onNodeCreationError');
    }

    function onConfigured($packet)
    {
        $config = $this->user->getConfig();
        $config[$packet->content] = 'created';
        
        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($config))
          ->request();
    }
    
    function onNodeCreated() {
        $config = $this->user->getConfig();
        $config['feed'] = 'created';
        
        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($config))
          ->request();
    }

    // TODO : do we really need this handler ?
    function onNodeCreationError() {
        $config = $this->user->getConfig();
        $config['feed'] = 'error';
        
        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($config))
          ->request();
        
        Notification::appendNotification(
            $this->__('feed.no_support'), 
            'error');
    }

    private function createPersistentStorage($node)
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

    function ajaxCreateMicroblog()
    {
        $p = new Moxl\Xec\Action\Microblog\CreateNode;
        $p->setTo($this->user->getLogin())
          ->request();
    }

    function ajaxCreateBookmark()
    {
        $this->createPersistentStorage('storage:bookmarks');
    }

    function ajaxCreateVcard4()
    {
        $this->createPersistentStorage('urn:xmpp:vcard4');
    }

    function ajaxCreateAvatar()
    {
        $this->createPersistentStorage('urn:xmpp:avatar:data');
    }

    function ajaxCreateLocation()
    {
        $this->createPersistentStorage('http://jabber.org/protocol/geoloc');
    }

    function ajaxCreatePubsubSubscription()
    {
        $this->createPersistentStorage('urn:xmpp:pubsub:subscription');
    }

    function display()
    {
        $session = \Sessionx::start();
        $config = $session->config;

        $this->view->assign('config', $config);

        $cd = new Modl\CapsDAO;
        $caps = $cd->get($session->host);

        if(isset($caps) && in_array('http://jabber.org/protocol/pubsub#config-node', unserialize($caps->features))) {
            $config = $this->user->getConfig();

            $creating = false;

            // Need to keep this structure to create progressively the nodes
            if(!isset($config['storage:bookmarks'])) {
                $this->view->assign('create_bookmark',  $this->genCallAjax('ajaxCreateBookmark'));
                $creating = 1;
            } elseif(!isset($config['urn:xmpp:vcard4'])) {
                $this->view->assign('create_vcard4',    $this->genCallAjax('ajaxCreateVcard4'));
                $creating = 2;
            } elseif(!isset($config['urn:xmpp:avatar:data'])) {
                $this->view->assign('create_avatar',    $this->genCallAjax('ajaxCreateAvatar'));
                $creating = 3;
            } elseif(!isset($config['http://jabber.org/protocol/geoloc'])) {
                $this->view->assign('create_location',  $this->genCallAjax('ajaxCreateLocation'));
                $creating = 4;
            } elseif(!isset($config['urn:xmpp:pubsub:subscription'])) {
                $this->view->assign('create_pubsubsubscription',  $this->genCallAjax('ajaxCreatePubsubSubscription'));
                $creating = 5;
            } elseif(!isset($config['feed'])) {
                $this->view->assign('create_microblog',  $this->genCallAjax('ajaxCreateMicroblog'));
                $creating = 6;
            }

            if($creating != false) {
                $this->view->assign('creating', $creating);
            } else {
                $this->view->assign('creating', false);
            }

            $this->view->assign('no_pubsub', false);
        } else {
            $this->view->assign('no_pubsub', true);
        }
    }
}
