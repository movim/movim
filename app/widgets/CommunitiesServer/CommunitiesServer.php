<?php

use Moxl\Xec\Action\Pubsub\GetItems;
use Moxl\Xec\Action\Disco\Items;
use Respect\Validation\Validator;
use Moxl\Xec\Action\Pubsub\Create;
use Moxl\Xec\Action\Pubsub\TestCreate;

use Cocur\Slugify\Slugify;

class CommunitiesServer extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('disco_items_handle', 'onDisco');
        $this->registerEvent('disco_items_error', 'onDiscoError');
        $this->registerEvent('pubsub_create_handle', 'onCreate');
        $this->registerEvent('pubsub_testcreate_handle', 'onTestCreate');
        $this->registerEvent('pubsub_testcreate_error', 'onTestCreateError');

        $this->addjs('communitiesserver.js');
    }

    function onCreate($packet)
    {
        Notification::append(null, $this->__('communitiesserver.created'));

        list($origin, $node) = array_values($packet->content);
        $this->ajaxDisco($origin);
    }

    function onDisco($packet)
    {
        $origin = $packet->content;

        $this->rpc('MovimTpl.fill', '#communities_server', $this->prepareCommunitiesServer($origin));
    }

    function onDiscoError($packet)
    {
        $origin = $packet->content;

        $id = new \Modl\InfoDAO;
        $id->deleteItems($origin);

        $this->rpc('MovimTpl.fill', '#communities_server', $this->prepareCommunitiesServer($origin));

        Notification::append(null, $this->__('communitiesserver.disco_error'));
    }

    function onTestCreate($packet)
    {
        $origin = $packet->content;

        $view = $this->tpl();
        $view->assign('server', $origin);

        Dialog::fill($view->draw('_communitiesserver_add', true));
    }

    function onTestCreateError($packet)
    {
        Notification::append(null, $this->__('communitiesserver.no_creation'));
    }

    function ajaxDisco($origin)
    {
        if(!$this->validateServer($origin)) {
            Notification::append(null, $this->__('communitiesserver.disco_error'));
            return;
        }

        //$this->rpc('MovimTpl.fill', '#communities_server', '');

        $r = new Items;
        $r->setTo($origin)->request();
    }

    /*
     * Seriously ? We need to put this hack because of buggy XEP-0060...
     */
    function ajaxTestAdd($origin)
    {
        if(!$this->validateServer($origin)) return;

        $t = new TestCreate;
        $t->setTo($origin)
          ->request();
    }

    function ajaxAddConfirm($origin, $form)
    {
        if(!$this->validateServer($origin)) return;

        $validate_name = Validator::stringType()->length(4, 80);
        if(!$validate_name->validate($form->name->value)) {
            Notification::append(null, $this->__('communitiesserver.name_error'));
            return;
        }

        $slugify = new Slugify();
        $uri = $slugify->slugify($form->name->value);

        if($uri == '') {
            Notification::append(null, $this->__('communitiesserver.name_error'));
            return;
        }

        $c = new Create;
        $c->setTo($origin)
          ->setNode($uri)
          ->setName($form->name->value)
          ->request();
    }

    public function prepareCommunitiesServer($origin)
    {
        $id = new \Modl\InfoDAO;

        $view = $this->tpl();
        $view->assign('item', $id->getJid($origin));
        $view->assign('nodes', $id->getItems($origin));
        $view->assign('server', $origin);

        return $view->draw('_communitiesserver', true);
    }

    /**
     * @brief Validate the server
     *
     * @param string $origin
     */
    private function validateServer($origin)
    {
        $validate_server = Validator::noWhitespace()->alnum('.-_')->length(6, 40);
        return ($validate_server->validate($origin));
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
    }
}
