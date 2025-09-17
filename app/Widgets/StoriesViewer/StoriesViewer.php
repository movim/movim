<?php

namespace App\Widgets\StoriesViewer;

use App\MessageFile;
use App\Post;
use App\Widgets\Chat\Chat;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Toast\Toast;
use Movim\Widget\Base;
use Moxl\Xec\Action\Pubsub\Delete;
use Moxl\Xec\Action\Pubsub\PostDelete;

class StoriesViewer extends Base
{
    public function load()
    {
        $this->addjs('storiesviewer.js');
        $this->addcss('storiesviewer.css');
    }

    public function ajaxHttpGet(int $id)
    {
        $post = Post::myStories()->where('id', $id)->first();
        if (!$post) return;

        $view = $this->tpl();
        $post->userViews()->syncWithoutDetaching($this->me->id);
        $view->assign('story', $post);

        $this->rpc('MovimTpl.fill', '#storiesviewer', $view->draw('_storiesviewer'));
        $this->rpc('StoriesViewer.launch', $post->published);
    }

    public function ajaxHttpGetNext(string $before)
    {
        $post = Post::myStories()->withCount('myViews')->where('published', '<', $before)->first();

        if (!$post || $post->my_views_count > 0) {
            $this->rpc('StoriesViewer.close');
            return;
        }

        $view = $this->tpl();
        $post->userViews()->syncWithoutDetaching($this->me->id);
        $view->assign('story', $post);

        $this->rpc('MovimTpl.fill', '#storiesviewer', $view->draw('_storiesviewer'));
        $this->rpc('StoriesViewer.launch', $post->published);
    }

    public function ajaxClose()
    {
        $this->rpc('MovimTpl.fill', '#storiesviewer', '');
    }

    public function ajaxDelete(string $id)
    {
        $post = Post::myStories()->where('id', $id)->first();

        if ($post) {
            $view = $this->tpl();
            $view->assign('post', $post);

            Dialog::fill($view->draw('_storiesviewer_delete'));
        }
    }

    public function ajaxDeleteConfirm(string $id)
    {
        $post = Post::myStories()->where('id', $id)->first();

        if ($post) {
            $p = new PostDelete;
            $p->setTo($post->server)
              ->setNode($post->node)
              ->setId($post->nodeid)
              ->request();
        }

        $this->rpc('StoriesViewer.close');
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
