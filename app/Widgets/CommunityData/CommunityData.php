<?php

namespace App\Widgets\CommunityData;

use Moxl\Xec\Action\Pubsub\GetItem;

use Movim\Widget\Base;
use Moxl\Xec\Payload\Packet;

class CommunityData extends Base
{
    public function load()
    {
        $this->addcss('communitydata.css');
        $this->addjs('communitydata.js');
        $this->registerEvent('disco_request_handle', 'onDiscoRequest', 'community');
        $this->registerEvent('pubsub_getitem_avatar', 'tonAvatar', 'community');
    }

    public function onDiscoRequest(Packet $packet)
    {
        $info = $packet->content;

        if (!$info->isMicroblogCommentsNode()) {
            $this->rpc('MovimTpl.fill', '#community_data', $this->prepareData($info->server, $info->node));
        }
    }

    public function tonAvatar(Packet $packet)
    {
        list($origin, $node) = array_values($packet->content);

        $this->rpc('MovimTpl.fill', '#community_data', $this->prepareData($origin, $node));
    }

    public function ajaxGetAvatar($origin, $node)
    {
        $g = new GetItem;
        $g->setTo($origin)
            ->setNode($node)
            ->setId('urn:xmpp:avatar:metadata')
            ->request();
    }

    public function prepareCard($info)
    {
        $view = $this->tpl();
        $view->assign('info', $info);
        $view->assign('num', 0);

        if ($info) {
            $view->assign(
                'num',
                ($info->items > 0)
                    ? $info->items
                    : \App\Post::where('server', $info->server)
                    ->where('node', $info->node)
                    ->count()
            );
        } else {
            return '';
        }

        return $view->draw('_communitydata_card');
    }

    public function prepareData($origin, $node)
    {
        $view = $this->tpl();
        $info = \App\Info::where('server', $origin)
            ->where('node', $node)
            ->first();

        $view->assign('info', $info);
        $view->assign('num', 0);

        if ($info) {
            $view->assign(
                'num',
                ($info->items > 0)
                    ? $info->items
                    : \App\Post::where('server', $origin)
                    ->where('node', $node)
                    ->count()
            );

            $title = !empty($info->name) ? $info->name : $node;
            $this->rpc(
                'Notif.setTitle',
                $this->__('page.communities') . ' • ' . $title
            );
        }

        return $view->draw('_communitydata');
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
        $this->view->assign('node', $this->get('n'));
    }
}
