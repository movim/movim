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

        list($server, $node) = array_values($packet->content);
        $this->ajaxDisco($server);
    }

    function onDisco($packet)
    {
        $server = $packet->content;

        RPC::call('MovimTpl.fill', '#communities_server', $this->prepareCommunitiesServer($server));
    }

    function onDiscoError($packet)
    {
        $server = $packet->content;

        $id = new \Modl\ItemDAO();
        $id->deleteItems($server);

        RPC::call('MovimTpl.fill', '#communities_server', $this->prepareCommunitiesServer($server));

        Notification::append(null, $this->__('communitiesserver.disco_error'));
    }

    function onTestCreate($packet)
    {
        $server = $packet->content;

        $view = $this->tpl();
        $view->assign('server', $server);

        Dialog::fill($view->draw('_communitiesserver_add', true));
    }

    function onTestCreateError($packet)
    {
        Notification::append(null, $this->__('communitiesserver.no_creation'));
    }

    function ajaxDisco($server)
    {
        if(!$this->validateServer($server)) {
            Notification::append(null, $this->__('communitiesserver.disco_error'));
            return;
        }

        RPC::call('MovimTpl.fill', '#communities_server', '');

        $r = new Items;
        $r->setTo($server)->request();
    }

    /*
     * Seriously ? We need to put this hack because of buggy XEP-0060...
     */
    function ajaxTestAdd($server)
    {
        if(!$this->validateServer($server)) return;

        $t = new TestCreate;
        $t->setTo($server)
          ->request();
    }

    function ajaxAddConfirm($server, $form)
    {
        if(!$this->validateServer($server)) return;

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
        $c->setTo($server)
          ->setNode($uri)
          ->setName($form->name->value)
          ->request();
    }

    public function prepareCommunitiesServer($server)
    {
        $id = new \Modl\ItemDAO;

        $view = $this->tpl();
        $view->assign('item', $id->getJid($server));
        $view->assign('nodes', $id->getItems($server));
        $view->assign('server', $server);

        return $view->draw('_communitiesserver', true);
    }

    /**
     * @brief Validate the server
     *
     * @param string $server
     */
    private function validateServer($server)
    {
        $validate_server = Validator::noWhitespace()->alnum('.-_')->length(6, 40);
        return ($validate_server->validate($server));
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
    }
}
