<?php

namespace App\Widgets\StoriesViewer;

use App\MessageFile;
use App\Post;
use App\Widgets\Chat\Chat;
use App\Widgets\Toast\Toast;
use Movim\Widget\Base;

class StoriesViewer extends Base
{
    public function load()
    {
        $this->addjs('storiesviewer.js');
        $this->addcss('storiesviewer.css');
    }

    public function ajaxGet(int $id)
    {
        $post = Post::myStories()->where('id', $id)->first();
        if (!$post) return;

        $view = $this->tpl();
        $post->userViews()->syncWithoutDetaching($this->user->id);
        $view->assign('story', $post);

        $this->rpc('MovimTpl.fill', '#storiesviewer', $view->draw('_storiesviewer'));
        $this->rpc('StoriesViewer.launch', $post->published);
    }

    public function ajaxGetNext(string $before)
    {
        $post = Post::myStories()->where('published', '<', $before)->first();

        if (!$post || $post->seen) {
            $this->rpc('StoriesViewer.close');
            return;
        }

        $view = $this->tpl();
        $post->userViews()->syncWithoutDetaching($this->user->id);
        $view->assign('story', $post);

        $this->rpc('MovimTpl.fill', '#storiesviewer', $view->draw('_storiesviewer'));
        $this->rpc('StoriesViewer.launch', $post->published);
    }

    public function ajaxClose()
    {
        $this->rpc('MovimTpl.fill', '#storiesviewer', '');
    }

    public function ajaxSendComment(string $id, ?string $comment = null)
    {
        $post = Post::myStories()->where('id', $id)->first();
        if (!$post || empty($comment)) return;

        $file = new MessageFile();
        $file->type = 'xmpp/uri';
        $file->url = $post->getRef();

        (new Chat)->sendMessage($post->server, $comment, file: $file);

        Toast::send($this->__('post.comment_published'));
    }
}
