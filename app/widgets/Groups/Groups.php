<?php

use Moxl\Xec\Action\Pubsub\GetItems;
use Moxl\Xec\Action\Pubsub\DiscoItems;
use Respect\Validation\Validator;

class Groups extends WidgetCommon
{
    private $_list_server;

    function load()
    {
        $this->registerEvent('pubsub_discoitems_handle', 'onDisco');
        $this->registerEvent('pubsub_discoitems_error', 'onDiscoError');
        $this->addjs('groups.js');
    }

    function onDisco($packet)
    {
        $server = $packet->content;
        $this->displayServer($server);
    }

    function onDiscoError($packet)
    {
        // Display a nice error
    }

    function ajaxHeader()
    {
        $id = new \modl\ItemDAO();

        $view = $this->tpl();
        $view->assign('servers', $id->getGroupServers());
        $header = $view->draw('_groups_header', true);

        Header::fill($header);
    }

    function ajaxSubscriptions()
    {
        $html = $this->prepareSubscriptions();

        RPC::call('movim_fill', 'groups_widget', $html);
        RPC::call('Groups.refresh');
    }

    function ajaxDisco($server)
    {
        $validate_server = Validator::string()->noWhitespace()->length(6, 40);
        if(!$validate_server->validate($server)) return;
        
        $r = new DiscoItems;
        $r->setTo($server)->request();
    }

    private function displayServer($server)
    {
        $validate_server = Validator::string()->noWhitespace()->length(6, 40);
        if(!$validate_server->validate($server)) return;

        $html = $this->prepareServer($server);

        RPC::call('movim_fill', 'groups_widget', $html);
        RPC::call('Groups.refresh');
    }

    function checkNewServer($node) {
        $r = false;
        
        if($this->_list_server != $node->server)
            $r = true;

        $this->_list_server = $node->server;
        return $r;
    }

    function prepareSubscriptions() {
        $sd = new \modl\SubscriptionDAO();

        $view = $this->tpl();
        $view->assign('subscriptions', $sd->getSubscribed());
        $html = $view->draw('_groups_subscriptions', true);

        return $html;
    }

    private function prepareServer($server) {
        $id = new \modl\ItemDAO();

        $view = $this->tpl();
        $view->assign('nodes', $id->getItems($server));
        $view->assign('server', $server);
        $html = $view->draw('_groups_server', true);

        return $html;
    }

    private function cleanServers($servers) {
        $i = 0;
        foreach($servers as $c) {
            if(filter_var($c->server, FILTER_VALIDATE_EMAIL)) {
                unset($servers[$i]);
            } elseif(count(explode('.', $c->server))<3) {
                unset($servers[$i]);
            }
            $i++;
        }
        return $servers;
    }

    function display()
    {
    }
}
