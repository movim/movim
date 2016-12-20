<?php

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Pubsub\PostDelete;
use Moxl\Xec\Action\Pubsub\Delete;
use Moxl\Xec\Action\Pubsub\GetItem;
use Moxl\Xec\Action\Microblog\CommentsGet;
use Moxl\Xec\Action\Microblog\CommentPublish;

use Respect\Validation\Validator;

class Post extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('post.js');
        $this->registerEvent('microblog_commentsget_handle', 'onComments');
        $this->registerEvent('microblog_commentpublish_handle', 'onCommentPublished');
        $this->registerEvent('microblog_commentsget_error', 'onCommentsError');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');
        $this->registerEvent('pubsub_postdelete', 'onDelete');
        $this->registerEvent('pubsub_getitem_handle', 'onHandle');
    }

    function onHandle($packet)
    {
        $content = $packet->content;

        if(is_array($content) && isset($content['nodeid'])) {
            $pd = new \Modl\PostnDAO;
            $p  = $pd->get($content['origin'], $content['node'], $content['nodeid']);

            if($p) {
                $html = $this->preparePost($p);

                RPC::call('MovimUtils.pushState', $this->route('news', [$p->origin, $p->node, $p->nodeid]));

                RPC::call('MovimTpl.fill', '#post_widget', $html);
                RPC::call('MovimUtils.enableVideos');
            }
        }
    }

    function onCommentPublished($packet)
    {
        Notification::append(false, $this->__('post.comment_published'));
        $this->onComments($packet);
    }

    function onDelete($packet)
    {
        list($server, $node, $id) = array_values($packet->content);

        if(substr($node, 0, 29) == 'urn:xmpp:microblog:0:comments') {
            Notification::append(false, $this->__('post.comment_deleted'));
            $this->ajaxGetComments($server, substr($node, 30));
        } else {
            Notification::append(false, $this->__('post.deleted'));

            if($node == 'urn:xmpp:microblog:0') {
                $this->rpc('MovimUtils.redirect', $this->route('news'));
            } else {
                $this->rpc('MovimUtils.redirect', $this->route('community', [$server, $node]));
            }
        }
    }

    function onComments($packet)
    {
        list($server, $node, $id) = array_values($packet->content);

        $p = new \Modl\ContactPostn;
        $p->nodeid = $id;

        $pd = new \Modl\PostnDAO;
        $comments = $pd->getComments($p);

        $emoji = \MovimEmoji::getInstance();

        $view = $this->tpl();
        $view->assign('post', $p);
        $view->assign('comments', $comments);
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('hearth', $emoji->replace('♥'));
        $view->assign('id', $id);

        $html = $view->draw('_post_comments', true);
        RPC::call('MovimTpl.fill', '#comments', $html);
    }

    function onCommentsError($packet)
    {
        $view = $this->tpl();
        $html = $view->draw('_post_comments_error', true);
        RPC::call('MovimTpl.fill', '#comments', $html);
    }

    function ajaxClear()
    {
        RPC::call('MovimUtils.pushState', $this->route('news'));

        RPC::call('MovimTpl.fill', '#post_widget', $this->prepareEmpty());
        RPC::call('Menu.refresh');
        //RPC::call('Menu_ajaxGetAll');
    }

    function ajaxGetContact($jid)
    {
        $c = new Contact;
        $c->ajaxGetDrawer($jid);
    }

    function ajaxGetPost($origin, $node, $id)
    {
        $pd = new \Modl\PostnDAO;
        $p  = $pd->get($origin, $node, $id);

        if($p) {
            $html = $this->preparePost($p);

            RPC::call('MovimTpl.fill', '#post_widget', $html);
            RPC::call('MovimUtils.enableVideos');

            // If the post is a reply but we don't have the original
            if($p->isReply() && !$p->getReply()) {
                $reply = $p->reply;

                $gi = new GetItem;
                $gi->setTo($reply['origin'])
                   ->setNode($reply['node'])
                   ->setId($reply['nodeid'])
                   ->setAskReply([
                        'origin' => $p->origin,
                        'node' => $p->node,
                        'nodeid' => $p->nodeid])
                   ->request();
            }

            $gi = new GetItem;
            $gi->setTo($p->origin)
               ->setNode($p->node)
               ->setId($p->nodeid)
               ->request();
        }
    }

    function ajaxDelete($to, $node, $id)
    {
        $view = $this->tpl();

        $view->assign('to', $to);
        $view->assign('node', $node);
        $view->assign('id', $id);

        Dialog::fill($view->draw('_post_delete', true));
    }

    function ajaxShare($origin, $node, $id)
    {
        $pd = new \Modl\PostnDAO;
        $p  = $pd->get($origin, $node, $id);

        if($p) {
            $this->rpc('MovimUtils.redirect', $this->route('publish', [$origin, $node, $id, 'share']));
        }
    }

    function ajaxDeleteConfirm($to, $node, $id) {
        $p = new PostDelete;
        $p->setTo($to)
          ->setNode($node)
          ->setId($id)
          ->request();

        $p = new Delete;
        $p->setTo($to)
          ->setNode('urn:xmpp:microblog:0:comments/'.$id)
          ->request();
    }

    function ajaxGetComments($jid, $id)
    {
        $pd = new \Modl\PostnDAO();
        $pd->deleteNode($jid, "urn:xmpp:microblog:0:comments/".$id);

        $c = new CommentsGet;
        $c->setTo($jid)
          ->setId($id)
          ->request();
    }

    private function publishComment($comment, $to, $node, $id)
    {
        if(!Validator::stringType()->notEmpty()->validate($comment)
        || !Validator::stringType()->length(6, 128)->noWhitespace()->validate($id)) return;

        $cp = new CommentPublish;
        $cp->setTo($to)
           ->setFrom($this->user->getLogin())
           ->setParentId($id)
           ->setContent(htmlspecialchars(rawurldecode($comment)))
           ->request();
    }

    function ajaxPublishComment($form, $to, $node, $id)
    {
        $comment = trim($form->comment->value);

        if($comment != '♥') {
            $this->publishComment($comment, $to, $node, $id);
        }
    }

    function ajaxLike($to, $node, $id)
    {
        $this->publishComment('♥', $to, $node, $id);
    }

    function prepareEmpty()
    {
        $view = $this->tpl();

        $nd = new \Modl\PostnDAO;
        $cd = new \Modl\ContactDAO;

        $view = $this->tpl();

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('top', $cd->getTop(6));
        $view->assign('blogs', $nd->getLastBlogPublic(0, 8));
        $view->assign('posts', $nd->getLastPublished(false, false, 0, 6));
        $view->assign('me', $cd->get($this->user->getLogin()), true);
        $view->assign('jid', $this->user->getLogin());

        return $view->draw('_post_empty', true);
    }

    function preparePost($p, $external = false, $public = false, $card = false)
    {
        $view = $this->tpl();

        if(isset($p)) {
            if(isset($p->commentorigin)
            && !$external) {
                $this->ajaxGetComments($p->commentorigin, $p->commentnodeid); // Broken in case of repost
            }

            $view->assign('repost', false);
            $view->assign('external', $external);
            $view->assign('public', $public);

            $view->assign('reply', $p->isReply() ? $p->getReply() : false);

            // Is it a repost ?
            if($p->isRecycled()) {
                $cd = new \Modl\ContactDAO;
                $view->assign('repost', $cd->get($p->origin));
            }

            $view->assign('post', $p);
            $view->assign('attachments', $p->getAttachments());

            if($card) {
                return $view->draw('_post_card', true);
            } else {
                return $view->draw('_post', true);
            }
        } elseif(!$external) {
            return $this->prepareEmpty();
        }
    }

    function getComments($post)
    {
        $pd = new \Modl\PostnDAO();
        return $pd->getComments($post);
    }

    function display()
    {
        $validate_nodeid = Validator::stringType()->length(10, 100);

        $this->view->assign('nodeid', false);
        if($validate_nodeid->validate($this->get('n'))) {
            $this->view->assign('nodeid', $this->get('n'));
        }
    }
}
