<?php

namespace App\Widgets\Stories;

use App\Post;
use App\Widgets\Chats\Chats;
use App\Widgets\Notif\Notif;
use Movim\Widget\Base;
use Moxl\Xec\Payload\Packet;

class Stories extends Base
{
    public function load()
    {
        $this->registerEvent('story', 'onStory');
        $this->registerEvent('story_retract', 'onStoryRetract');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');

        $this->addjs('stories.js');
        $this->addcss('stories.css');
    }

    public function onStory(Packet $packet)
    {
        $post = Post::find($packet->content);

        if ($post && $post->isRecentStory()) {
            if (!$post->isMine($this->me)) {
                $contact = \App\Contact::firstOrNew(['id' => $post->server]);

                $this->notif(
                    key: 'news',
                    title: '📝 ' . __('stories.new_story', $contact->truename),
                    body: $post->title,
                    url: $this->route('chat'),
                    picture: $contact->getPicture(),
                    time: 4,
                );
            }

            $this->ajaxHttpGet();
        }
    }

    public function onStoryRetract(Packet $packet)
    {
        $this->ajaxHttpGet();
    }

    public function onDelete(Packet $packet)
    {
        if (
            $packet->content['server'] == $this->me->id
            && $packet->content['node'] == Post::STORIES_NODE
        ) {
            $this->toast($this->__('stories.deleted'));
            $this->ajaxHttpGet();
        }
    }

    public function ajaxOpenChat(string $jid)
    {
        (new Chats($this->me))->ajaxOpen($jid, andShow: true);
        $this->ajaxHttpGet();
    }

    public function ajaxHttpGet()
    {
        $blocks = 10;
        $stories = Post::myStories($this->me)->get();

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
