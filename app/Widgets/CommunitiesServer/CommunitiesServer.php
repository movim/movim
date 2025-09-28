<?php

namespace App\Widgets\CommunitiesServer;

use App\Widgets\Dialog\Dialog;
use App\Widgets\Toast\Toast;
use Moxl\Xec\Action\Disco\Request;
use Moxl\Xec\Action\Disco\Items;
use Moxl\Xec\Action\Pubsub\Create;
use Moxl\Xec\Action\Pubsub\TestCreate;
use Moxl\Xec\Payload\Packet;
use Respect\Validation\Validator;

class CommunitiesServer extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('disco_items_handle', 'onDisco');
        $this->registerEvent('disco_items_error', 'onDiscoError');
        $this->registerEvent('disco_items_errorremoteservernotfound', 'tonRemoteServerNotFound');
        $this->registerEvent('disco_items_errorremoteservertimeout', 'tonRemoteServerNotFound');
        $this->registerEvent('disco_request_handle', 'onDiscoRequest');
        $this->registerEvent('pubsub_create_handle', 'onCreate');
        $this->registerEvent('pubsub_testcreate_handle', 'onTestCreate');
        $this->registerEvent('pubsub_testcreate_error', 'onTestCreateError');

        $this->addjs('communitiesserver.js');
    }

    public function onCreate(Packet $packet)
    {
        Toast::send($this->__('communitiesserver.created'));

        list($origin, $node) = array_values($packet->content);
        $this->ajaxDisco($origin);
        $this->rpc('MovimUtils.reload', $this->route('community', [$origin, $node]));
    }

    public function tonRemoteServerNotFound(Packet $packet)
    {
        $view = $this->tpl();
        $view->assign('server', $packet->content);

        $this->rpc('MovimTpl.fill', '#communities_server', $view->draw('_communitiesserver_remoteservernotfound'));
    }

    public function onDisco(Packet $packet)
    {
        $origin = $packet->content;

        $this->rpc('MovimTpl.fill', '#communities_server', $this->prepareCommunitiesServer($origin));
    }

    public function onDiscoRequest(Packet $packet)
    {
        $info = $packet->content;

        if ($info) {
            $this->rpc('MovimTpl.replace', '#' . cleanupId($info->server . $info->node), $this->prepareTicket($info));
        }
    }

    public function onDiscoError(Packet $packet)
    {
        $origin = $packet->content;

        \App\Info::where('server', $origin)->delete();

        $this->rpc('MovimTpl.fill', '#communities_server', $this->prepareCommunitiesServer($origin));

        Toast::send($this->__('communitiesserver.disco_error'));
    }

    public function onTestCreate(Packet $packet)
    {
        $origin = $packet->content;

        $view = $this->tpl();
        $view->assign('server', $origin);

        Dialog::fill($view->draw('_communitiesserver_add'));
    }

    public function onTestCreateError(Packet $packet)
    {
        Toast::send($this->__('communitiesserver.no_creation'));
    }

    public function ajaxDisco($origin)
    {
        if (!validateServer($origin)) {
            Toast::send($this->__('communitiesserver.disco_error'));
            return;
        }

        $r = new Request;
        $r->setTo($origin)->request();

        $r = new Items;
        $r->setTo($origin)->request();
    }

    /*
     * Seriously ? We need to put this hack because of buggy XEP-0060...
     */
    public function ajaxTestAdd($origin)
    {
        if (!validateServer($origin)) {
            return;
        }

        $t = new TestCreate;
        $t->setTo($origin)
            ->request();
    }

    public function ajaxAddConfirm($origin, $form)
    {
        if (!validateServer($origin)) {
            return;
        }

        $validate_name = Validator::stringType()->length(4, 80);
        if (!$validate_name->isValid($form->name->value)) {
            Toast::send($this->__('communitiesserver.name_error'));
            return;
        }

        $uri = slugify($form->name->value);

        if ($uri == '') {
            Toast::send($this->__('communitiesserver.name_error'));
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
        $item = \App\Info::where('server', $origin)->where('node', '')->first();

        if (!$item || !$item->isPubsubService()) return;

        $nodes = \App\Info::where('infos.server', $origin)
            ->where('infos.node', '!=', '')
            ->leftJoinSub(
                function ($query) use ($origin) {
                    $query->selectRaw('max(published) as published, server, node')
                        ->from('posts')
                        ->where('posts.server', $origin)
                        ->groupBy(['server', 'node']);
                },
                'recents',
                function ($join) {
                    $join->on('recents.server', 'infos.server')
                        ->on('recents.node', 'infos.node');
                }
            )->orderBy('published', 'desc')
            ->get(['infos.*', 'published']);

        // Lets push back the null content last
        $nodes = $nodes->reject(function ($node) {
            return $node->published == null;
        })
            ->merge(
                $nodes->filter(function ($node) {
                    return $node->published == null;
                })
            );

        $view = $this->tpl();
        $view->assign('item', $item);
        $view->assign('nodes', $nodes);
        $view->assign('server', $origin);

        if (isset($item->name)) {
            $this->rpc('Notif.setTitle', $this->__('page.communities') . ' • ' . $item->name);
        }

        return $view->draw('_communitiesserver');
    }

    public function prepareTicket(\App\Info $community)
    {
        $view = $this->tpl();
        $view->assign('community', $community);
        $view->assign('id', cleanupId($community->server . $community->node));
        return $view->draw('_communitiesserver_ticket');
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
    }
}
