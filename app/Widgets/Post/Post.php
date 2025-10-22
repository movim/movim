<?php

namespace App\Widgets\Post;

use App\Post as AppPost;
use App\Widgets\ContactActions\ContactActions;
use Movim\Widget\Base;

use Moxl\Xec\Action\Pubsub\GetItem;
use Moxl\Xec\Action\Microblog\CommentsGet;
use Moxl\Xec\Action\Microblog\CommentPublish;
use Moxl\Xec\Payload\Packet;
use Respect\Validation\Validator;

class Post extends Base
{
    public function load()
    {
        $this->addjs('post.js');
        $this->addcss('post.css');
        $this->registerEvent('microblog_commentsget_handle', 'onComments', 'post');
        $this->registerEvent('microblog_commentsget_error', 'onCommentsError');
        $this->registerEvent('microblog_commentpublish_error', 'onCommentPublishError');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete', 'post');
        $this->registerEvent('pubsub_getitem_errorpresencesubscriptionrequired', 'onPresenceSubscriptionRequired');
        $this->registerEvent('post', 'tonHandle', 'post');
        $this->registerEvent('post_resolved', 'tonHandle', 'post');
        $this->registerEvent('post_comment_published', 'onCommentPublished', 'post');
    }

    public function tonHandle(Packet $packet)
    {
        $post = AppPost::find($packet->content);

        if ($post) {
            if ($post->isComment()) {
                $parent = $post->getParent();
                $this->rpc(
                    'MovimTpl.fill',
                    '#post_widget.' . cleanupId($parent->nodeid) . ' #comments',
                    $this->prepareComments($parent)
                );
                $this->rpc('MovimUtils.applyAutoheight');
                $this->rpc('Post.checkCommentAction');
            } else {
                $this->rpc(
                    'MovimTpl.fill',
                    '#post_widget.' . cleanupId($post->nodeid),
                    $this->preparePost($post)
                );
                $this->rpc('MovimUtils.enhanceArticlesContent');
            }
        }
    }

    public function onCommentPublished(Packet $packet)
    {
        $post = AppPost::find($packet->content);

        if ($post->isComment()) {
            if ($parent = $post->getParent()) {
                $this->rpc(
                    'MovimTpl.fill',
                    '#post_widget.' . cleanupId($parent->nodeid) . ' #comments',
                    $this->prepareComments($parent)
                );
                $this->rpc('MovimUtils.applyAutoheight');
            }

            if ($post->isLike()) {
                $this->toast($packet->content
                    ? $this->__('post.comment_like_published')
                    : $this->__('post.comment_published'));
            }
        }
    }

    public function onCommentPublishError()
    {
        $this->toast($this->__('post.comment_publish_error'));
    }

    public function onComments(Packet $packet)
    {
        $post = \App\Post::find($packet->content);

        if ($post) {
            $this->rpc(
                'MovimTpl.fill',
                '#post_widget.' . cleanupId($post->nodeid) . ' #comments',
                $this->prepareComments($post)
            );
            $this->rpc('MovimUtils.applyAutoheight');
            $this->rpc('Post.checkCommentAction');
        }
    }

    public function onPresenceSubscriptionRequired(Packet $packet)
    {
        $view = $this->tpl();
        $view->assign('contact', \App\Contact::firstOrNew(['id' => $packet->content]));
        $this->rpc('MovimTpl.fill', '#post_widget', $view->draw('_post_subscription_required'));
    }

    public function onCommentsError(Packet $packet)
    {
        $view = $this->tpl();
        $view->assign('post', \App\Post::find($packet->content));
        $this->rpc('MovimTpl.fill', '#comments', $view->draw('_post_comments_error'));
    }

    public function onDelete(Packet $packet)
    {
        $this->rpc('Post.refreshComments');
    }

    public function ajaxGetContact($jid)
    {
        $c = new ContactActions();
        $c->ajaxGetDrawer($jid);
    }

    public function ajaxGetPost(string $server, string $node, string $nodeid)
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
            ->request();

        if ($p) {
            $p->userViews()->syncWithoutDetaching($this->me->id);

            $html = $this->preparePost($p, requestComments: false);

            $this->rpc('MovimTpl.fill', '#post_widget.' . cleanupId($p->nodeid), $html);
            $this->rpc('MovimUtils.enhanceArticlesContent');
            $this->rpc('Notif.setTitle', $this->__('page.post') . ' • ' . $p->title);

            // If the post is a reply but we don't have the original
            if ($p->isReply() && !$p->getReply()) {
                $gi = new GetItem;
                $gi->setTo($p->replyserver)
                    ->setNode($p->replynode)
                    ->setId($p->replynodeid)
                    ->setReplypostid($p->id)
                    ->request();
            }
        } else {
            $this->rpc('MovimTpl.fill', '#post_widget', $this->prepareNotFound());
        }
    }

    public function ajaxGetNotFound()
    {
        $this->rpc('MovimTpl.fill', '#post_widget', $this->prepareNotFound());
    }

    public function ajaxGetPostComments(string $server, string $node, string $id)
    {
        $post = \App\Post::where('server', $server)
            ->where('node', $node)
            ->where('nodeid', $id)
            ->first();

        if ($post) {
            $this->requestComments($post);
        }
    }

    public function ajaxShare(string $server, string $node, string $id)
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
        if (
            !Validator::stringType()->notEmpty()->isValid($comment)
            || !Validator::stringType()->length(6, 128)->noWhitespace()->isValid($id)
        ) {
            return;
        }

        $p = \App\Post::where('server', $to)
            ->where('node', $node)
            ->where('nodeid', $id)
            ->first();

        if ($p) {
            $cp = new CommentPublish;
            $cp->setTo($p->commentserver)
                ->setFrom($this->me->id)
                ->setId($p->commentnodeid)
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

    public function prepareComments(\App\Post $post, ?bool $public = false)
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

    public function preparePost(\App\Post $post, $public = false, $card = false, $requestComments = true)
    {
        if (isset($post)) {
            $view = $this->tpl();

            $commentsDisabled = false;

            if (
                $post->hasCommentsNode()
                && !$public && !$card
            ) {
                if ($requestComments) {
                    $this->rpc('Post_ajaxGetPostComments', $post->server, $post->node, $post->nodeid); // Broken in case of repost
                }
            } elseif (!$card) {
                $viewd = $this->tpl();
                $viewd->assign('post', $post);

                if ($requestComments) {
                    $commentsDisabled = $viewd->draw('_post_comments_error');
                }
            }

            $view->assign('commentsdisabled', $commentsDisabled);
            $view->assign('public', $public);
            $view->assign('reply', $post->isReply() ? $post->getReply() : false);
            $view->assign('repost', $post->isRecycled() ? \App\Contact::find($post->server) : false);

            $view->assign('nsfw', $this->me->nsfw);
            $view->assign('post', $post);

            return ($card)
                ? $view->draw('_post_card')
                : $view->draw('_post');
        }

        return $this->prepareNotFound();
    }

    public function prepareTicket(\App\Post $post, $public = false)
    {
        $view = $this->tpl();
        $view->assign('post', $post);
        $view->assign('public', $public);
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
        if (Validator::stringType()->length(3, 256)->isValid($this->get('i'))) {
            $this->view->assign('nodeid', $this->get('i'));
        }
    }
}
