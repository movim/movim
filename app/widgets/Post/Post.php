<?php

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Pubsub\GetItem;
use Moxl\Xec\Action\Microblog\CommentsGet;
use Moxl\Xec\Action\Microblog\CommentPublish;

use Respect\Validation\Validator;

class Post extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('post.js');
        $this->addcss('post.css');
        $this->registerEvent('microblog_commentsget_handle', 'onComments', 'post');
        $this->registerEvent('microblog_commentpublish_handle', 'onCommentPublished');
        $this->registerEvent('microblog_commentsget_error', 'onCommentsError');
        $this->registerEvent('pubsub_getitem_handle', 'onHandle', 'post');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete', 'post');
    }

    function onHandle($packet)
    {
        $post = $packet->content;

        if ($post->isComment()) {
            $this->rpc(
                'MovimTpl.fill',
                '#comments',
                $this->prepareComments($post->getParent())
            );
        } else {
            $this->rpc('MovimTpl.fill',
                '#post_widget.'.cleanupId($post->nodeid),
                $this->preparePost($post));
            $this->rpc('MovimUtils.enhanceArticlesContent');
        }
    }

    function onCommentPublished($packet)
    {
        Notification::append(false, $this->__('post.comment_published'));
    }

    function onComments($packet)
    {
        $post = \App\Post::find($packet->content);
        $this->rpc('MovimTpl.fill', '#comments', $this->prepareComments($post));
    }

    function onCommentsError($packet)
    {
        $view = $this->tpl();
        $html = $view->draw('_post_comments_error', true);
        $this->rpc('MovimTpl.fill', '#comments', $html);
    }

    function onDelete($packet)
    {
        $this->rpc('Post.refreshComments');
    }

    function ajaxGetContact($jid)
    {
        $c = new ContactActions;
        $c->ajaxGetDrawer($jid);
    }

    function ajaxGetPost($server, $node, $nodeid)
    {
        $p = \App\Post::where('server', $server)
                      ->where('node', $node)
                      ->where('nodeid', $nodeid)
                      ->first();

        if ($p) {
            $html = $this->preparePost($p);

            $this->rpc('MovimTpl.fill', '#post_widget.'.cleanupId($p->nodeid), $html);
            $this->rpc('MovimUtils.enhanceArticlesContent');

            // If the post is a reply but we don't have the original
            if ($p->isReply() && !$p->getReply()) {
                $gi = new GetItem;
                $gi->setTo($p->replyserver)
                   ->setNode($p->replynode)
                   ->setId($p->replynodeid)
                   ->setAskReply($p->id)
                   ->request();
            }

            $gi = new GetItem;
            $gi->setTo($p->server)
               ->setNode($p->node)
               ->setId($p->nodeid)
               ->request();
        } else {
            $this->rpc('MovimTpl.fill', '#post_widget', $this->prepareNotFound());
        }
    }

    function ajaxGetPostComments($server, $node, $id)
    {
        $post = \App\Post::where('server', $server)
                         ->where('node', $node)
                         ->where('nodeid', $id)
                         ->first();

        if ($post) {
            $this->requestComments($post);
        }
    }

    function ajaxShare($server, $node, $id)
    {
        $this->rpc('MovimUtils.redirect', $this->route('publish', [$server, $node, $id, 'share']));
    }

    function requestComments(\App\Post $post)
    {
        if ($post->id == null) return;

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
        || !Validator::stringType()->length(6, 128)->noWhitespace()->validate($id)) return;

        $p = \App\Post::where('server', $to)
                      ->where('node', $node)
                      ->where('nodeid', $id)
                      ->first();

        if ($p) {
            $cp = new CommentPublish;
            $cp->setTo($p->commentserver)
               ->setFrom($this->user->jid)
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
        $emoji = \Movim\Emoji::getInstance();
        $view = $this->tpl();
        $view->assign('post', $post);
        $view->assign('public', $public);
        $view->assign('hearth', $emoji->replace('♥'));

        return $view->draw('_post_comments', true);
    }

    function prepareNotFound()
    {
        $view = $this->tpl();
        return $view->draw('_post_not_found', true);
    }

    function preparePost(\App\Post $p, $public = false, $card = false)
    {
        if (isset($p)) {
            $view = $this->tpl();

            if ($p->hasCommentsNode()
            && !$public && !$card) {
                $this->requestComments($p); // Broken in case of repost
                $view->assign('commentsdisabled', false);
            } elseif (!$card) {
                $viewd = $this->tpl();
                $view->assign('commentsdisabled', $viewd->draw('_post_comments_error', true));
            }

            $view->assign('public', $public);
            $view->assign('reply', $p->isReply() ? $p->getReply() : false);
            $view->assign('repost', $p->isRecycled() ? \App\Contact::find($p->server) : false);

            $view->assign('nsfw', $this->user->nsfw);
            $view->assign('post', $p);

            return ($card)
                ? $view->draw('_post_card', true)
                : $view->draw('_post', true);
        }

        return $this->prepareNotFound();
    }

    function prepareTicket(\App\Post $post, $big = false)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        $view->assign('big', $big);

        return $view->draw('_post_ticket', true);
    }

    public function preparePostLinks(\App\Post $post)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        return $view->draw('_post_links', true);
    }

    public function preparePostReply(\App\Post $post)
    {
        if (!$post->isReply()) return '';

        $view = $this->tpl();
        $view->assign('reply', $post->getReply());
        return $view->draw('_post_reply', true);
    }

    public function preparePostHeader(\App\Post $post, $repost = false, $public = false)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        $view->assign('repost', $repost);
        $view->assign('public', $public);
        return $view->draw('_post_header', true);
    }

    public function preparePreviousNext(\App\Post $post)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        return $view->draw('_post_prevnext', true);
    }

    function display()
    {
        $validate_nodeid = Validator::stringType()->length(10, 100);

        $this->view->assign('nodeid', false);
        if ($validate_nodeid->validate($this->get('i'))) {
            $this->view->assign('nodeid', $this->get('i'));
        }
    }
}
