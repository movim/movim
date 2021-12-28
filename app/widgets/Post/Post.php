<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Pubsub\GetItem;
use Moxl\Xec\Action\Microblog\CommentsGet;
use Moxl\Xec\Action\Microblog\CommentPublish;

use Respect\Validation\Validator;

class Post extends Base
{
    public function load()
    {
        $this->addjs('post.js');
        $this->addcss('post.css');
        $this->registerEvent('microblog_commentsget_handle', 'onComments', 'post');
        $this->registerEvent('microblog_commentpublish_handle', 'onCommentPublished');
        $this->registerEvent('microblog_commentpublish_error', 'onCommentPublishError');
        $this->registerEvent('microblog_commentsget_error', 'onCommentsError');
        $this->registerEvent('pubsub_getitem_handle', 'onHandle', 'post');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete', 'post');
    }

    public function onHandle($packet)
    {
        $post = $packet->content;

        if ($post->isComment()) {
            $parent = $post->getParent();

            $this->rpc(
                'MovimTpl.fill',
                '#post_widget.'.cleanupId($parent->nodeid).' #comments',
                $this->prepareComments($post->getParent())
            );
            $this->rpc('MovimUtils.applyAutoheight');
        } else {
            $this->rpc(
                'MovimTpl.fill',
                '#post_widget.'.cleanupId($post->nodeid),
                $this->preparePost($post)
            );
            $this->rpc('MovimUtils.enhanceArticlesContent');
        }
    }

    public function onCommentPublished($packet)
    {
        $isLike = $packet->content;
        Toast::send($isLike
            ? $this->__('post.comment_like_published')
            : $this->__('post.comment_published'));
    }

    public function onCommentPublishError()
    {
        Toast::send($this->__('post.comment_publish_error'));
    }

    public function onComments($packet)
    {
        $post = \App\Post::find($packet->content);

        if ($post) {
            $this->rpc(
                'MovimTpl.fill',
                '#post_widget.'.cleanupId($post->nodeid).' #comments',
                $this->prepareComments($post)
            );
            $this->rpc('MovimUtils.applyAutoheight');
        }
    }

    public function onCommentsError($packet)
    {
        $view = $this->tpl();
        $view->assign('post', \App\Post::find($packet->content));
        $this->rpc('MovimTpl.fill', '#comments', $view->draw('_post_comments_error'));
    }

    public function onDelete($packet)
    {
        $this->rpc('Post.refreshComments');
    }

    public function ajaxGetContact($jid)
    {
        $c = new ContactActions;
        $c->ajaxGetDrawer($jid);
    }

    public function ajaxGetPost($server, $node, $nodeid)
    {
        $p = \App\Post::where('server', $server)
                      ->where('node', $node)
                      ->where('nodeid', $nodeid)
                      ->with('tags')
                      ->first();

        $gi = new GetItem;
        $gi->setTo($server)
            ->setNode($node)
            ->setId($nodeid)
            ->setManual()
            ->request();

        if ($p) {
            $html = $this->preparePost($p);

            $this->rpc('MovimTpl.fill', '#post_widget.'.cleanupId($p->nodeid), $html);
            $this->rpc('MovimUtils.enhanceArticlesContent');
            $this->rpc('Notification.setTitle', $this->__('page.post') . ' • ' . $p->title);

            // If the post is a reply but we don't have the original
            if ($p->isReply() && !$p->getReply()) {
                $gi = new GetItem;
                $gi->setTo($p->replyserver)
                   ->setNode($p->replynode)
                   ->setId($p->replynodeid)
                   ->setAskReply($p->id)
                   ->request();
            }
        } else {
            $this->rpc('MovimTpl.fill', '#post_widget', $this->prepareNotFound());
        }
    }

    public function ajaxGetPostComments($server, $node, $id)
    {
        $post = \App\Post::where('server', $server)
                         ->where('node', $node)
                         ->where('nodeid', $id)
                         ->first();

        if ($post) {
            $this->requestComments($post);
        }
    }

    public function ajaxShare($server, $node, $id)
    {
        $this->rpc('MovimUtils.redirect', $this->route('publish', [$server, $node, $id, 'share']));
    }

    public function requestComments(\App\Post $post)
    {
        if ($post->id == null) {
            return;
        }

        \App\Post::whereNotNull('parent_id')
                 ->where('parent_id', $post->id)
                 ->delete();

        $c = new CommentsGet;
        $c->setTo($post->commentserver)
          ->setId($post->commentnodeid)
          ->setParentId($post->id)
          ->request();
    }

    public function publishComment($comment, $to, $node, $id)
    {
        if (!Validator::stringType()->notEmpty()->validate($comment)
        || !Validator::stringType()->length(6, 128)->noWhitespace()->validate($id)) {
            return;
        }

        $p = \App\Post::where('server', $to)
                      ->where('node', $node)
                      ->where('nodeid', $id)
                      ->first();

        if ($p) {
            $cp = new CommentPublish;
            $cp->setTo($p->commentserver)
               ->setFrom($this->user->id)
               ->setCommentNodeId($p->commentnodeid)
               ->setTitle(htmlspecialchars(rawurldecode($comment)))
               ->setParentId($p->id)
               ->request();
        }
    }

    public function ajaxPublishComment($form, $to, $node, $id)
    {
        $comment = trim($form->comment->value);

        if ($comment != '♥') {
            $this->publishComment($comment, $to, $node, $id);
        }
    }

    public function prepareComments(\App\Post $post, $public = false)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        $view->assign('public', $public);
        $view->assign('hearth', addEmojis('♥'));

        return $view->draw('_post_comments');
    }

    public function prepareNotFound()
    {
        $view = $this->tpl();
        return $view->draw('_post_not_found');
    }

    public function preparePost(\App\Post $p, $public = false, $card = false)
    {
        if (isset($p)) {
            $view = $this->tpl();

            if ($p->hasCommentsNode()
            && !$public && !$card) {
                $this->requestComments($p); // Broken in case of repost
                $view->assign('commentsdisabled', false);
            } elseif (!$card) {
                $viewd = $this->tpl();
                $viewd->assign('post', $p);
                $view->assign('commentsdisabled', $viewd->draw('_post_comments_error'));
            }

            $view->assign('public', $public);
            $view->assign('reply', $p->isReply() ? $p->getReply() : false);
            $view->assign('repost', $p->isRecycled() ? \App\Contact::find($p->server) : false);

            $view->assign('nsfw', $this->user->nsfw);
            $view->assign('post', $p);
            $view->assign('info', \App\Info::where('server', $p->server)
                                           ->where('node', $p->node)
                                           ->first());

            return ($card)
                ? $view->draw('_post_card')
                : $view->draw('_post');
        }

        return $this->prepareNotFound();
    }

    public function prepareTicket(\App\Post $post)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        return $view->draw('_post_ticket');
    }

    public function preparePostLinks(\App\Post $post)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        return $view->draw('_post_links');
    }

    public function preparePostReply(\App\Post $post)
    {
        if (!$post->isReply()) {
            return '';
        }

        $view = $this->tpl();
        $view->assign('reply', $post->getReply());
        return $view->draw('_post_reply');
    }

    public function preparePreviousNext(\App\Post $post)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        return $view->draw('_post_prevnext');
    }

    public function preparePreviousNextBack(\App\Post $post)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        $view->assign('info', \App\Info::where('server', $post->server)
                                       ->where('node', $post->node)
                                       ->first());
        return $view->draw('_post_prevnext_back');
    }

    public function display()
    {
        $this->view->assign('nodeid', false);
        if (Validator::stringType()->length(3, 256)->validate($this->get('i'))) {
            $this->view->assign('nodeid', $this->get('i'));
        }
    }
}
