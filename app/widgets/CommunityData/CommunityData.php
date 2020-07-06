<?php

use Moxl\Xec\Action\Pubsub\GetItem;

use Movim\Widget\Base;

use Respect\Validation\Validator;

class CommunityData extends Base
{
    public function load()
    {
        $this->addcss('communitydata.css');
        $this->addjs('communitydata.js');
        $this->registerEvent('disco_request_handle', 'onDiscoRequest', 'community');
        $this->registerEvent('pubsub_getitem_avatar', 'onDiscoRequest', 'community');
    }

    public function onDiscoRequest($packet)
    {
        list($origin, $node) = array_values($packet->content);

        if ((substr($node, 0, 30) != 'urn:xmpp:microblog:0:comments/')) {
            $this->rpc('MovimTpl.fill', '#community_data', $this->prepareData($origin, $node));
        }
    }

    public function ajaxGetAvatar($origin, $node)
    {
        $g = new GetItem;
        $g->setTo($origin)
          ->setNode($node)
          ->setId('urn:xmpp:avatar:metadata')
          ->request();
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
            $view->assign('num',
                ($info->items > 0)
                    ? $info->items
                    : \App\Post::where('server', $origin)
                           ->where('node', $node)
                           ->count()
            );

            $this->rpc('MovimUtils.setTitle',
                $this->__('page.communities') .
                ' • ' .
                $info->name ?? $info->node
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
