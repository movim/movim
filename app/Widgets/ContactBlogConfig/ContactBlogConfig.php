<?php

namespace App\Widgets\ContactBlogConfig;

use App\Post;
use Movim\Widget\Base;

use Moxl\Xec\Action\Pubsub\GetConfig;
use Moxl\Xec\Payload\Packet;

class ContactBlogConfig extends Base
{
    public function load()
    {
        $this->addjs('contactblogconfig.js');
        $this->registerEvent('pubsub_getconfig_handle', 'onBlogConfig');
    }

    public function ajaxCheckAccessModel(string $jid)
    {
        if ($jid == $this->me->id) {
            (new GetConfig)->setNode(Post::MICROBLOG_NODE)->request();
        }
    }

    public function onBlogConfig(Packet $packet)
    {
        if ($packet->content['access_model'] == 'presence') {
            $view = $this->tpl();
            $this->rpc('MovimTpl.fill', '#contact_blog_config_widget', $view->draw('_contactblogconfig'));
        }
    }
}
