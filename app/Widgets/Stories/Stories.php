<?php

namespace App\Widgets\Stories;

use App\Post;
use App\Widgets\Chats\Chats;
use Movim\Widget\Base;
use Moxl\Xec\Payload\Packet;

class Stories extends Base
{
    public function load()
    {
        $this->registerEvent('post', 'onStory');
        $this->registerEvent('pubsub_getitem_handle', 'onStory');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');

        $this->addjs('stories.js');
        $this->addcss('stories.css');
    }

    public function onStory(Packet $packet)
    {
        $post = Post::find($packet->content);

        if ($post && $post->isStory()) {
            $this->ajaxHttpGet();
        }
    }

    public function onDelete(Packet $packet)
    {
        if ($packet->content['server'] == $this->me->id
         && $packet->content['node'] == Post::STORIES_NODE) {
            $this->toast($this->__('stories.deleted'));
            $this->ajaxHttpGet();
        }
    }

    public function ajaxOpenChat(string $jid)
    {
        (new Chats)->ajaxOpen($jid, andShow: true);
        $this->ajaxHttpGet();
    }

    public function ajaxHttpGet()
    {
        $blocks = 10;
        $stories = Post::myStories()->withCount('myViews')->get();

        $takeTopContacts = 0;
        if ($stories->count() < $blocks) {
            $takeTopContacts = $blocks - $stories->count();
        }

        $view = $this->tpl();
        $view->assign('topcontacts', $this->me->session->topContactsToChat()->take($takeTopContacts)->get());
        $view->assign('stories', $stories);

        $this->rpc('MovimTpl.fill', '#stories', $view->draw('_stories'));
    }
}
