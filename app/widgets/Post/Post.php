<?php

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Pubsub\PostDelete;
use Moxl\Xec\Action\Pubsub\Delete;
use Moxl\Xec\Action\Pubsub\GetItem;
use Moxl\Xec\Action\Microblog\CommentsGet;
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use Moxl\Xec\Action\Microblog\CommentPublish;
use \Michelf\Markdown;
use Respect\Validation\Validator;

class Post extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('microblog_commentsget_handle', 'onComments');
        $this->registerEvent('microblog_commentpublish_handle', 'onCommentPublished');
        $this->registerEvent('microblog_commentsget_error', 'onCommentsError');
        $this->registerEvent('pubsub_postpublish_handle', 'onPublish');
        $this->registerEvent('pubsub_postdelete_handle', 'onDelete');
    }

    function onPublish($packet)
    {
        Notification::append(false, $this->__('post.published'));
        $this->ajaxClear();
        RPC::call('MovimTpl.hidePanel');
    }

    function onCommentPublished($packet)
    {
        Notification::append(false, $this->__('post.comment_published'));
        $this->onComments($packet);
    }

    function onDelete($packet)
    {
        $content = $packet->content;

        if(substr($content['node'], 0, 29) == 'urn:xmpp:microblog:0:comments') {
            Notification::append(false, $this->__('post.comment_deleted'));
            $this->ajaxGetComments($content['server'], substr($content['node'], 30));
        } else {
            Notification::append(false, $this->__('post.deleted'));
            $this->ajaxClear();
            RPC::call('MovimTpl.hidePanel');
            RPC::call('Menu_ajaxGetAll');
        }
    }

    function onComments($packet)
    {
        list($server, $node, $id) = array_values($packet->content);

        $p = new \Modl\ContactPostn();
        $p->nodeid = $id;

        $pd = new \Modl\PostnDAO();
        $comments = $pd->getComments($p);

        $view = $this->tpl();
        $view->assign('comments', $comments);
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('id', $id);

        $html = $view->draw('_post_comments', true);
        RPC::call('movim_fill', 'comments', $html);
    }

    function onCommentsError($packet)
    {
        $view = $this->tpl();
        $html = $view->draw('_post_comments_error', true);
        RPC::call('movim_fill', 'comments', $html);
    }

    function ajaxClear()
    {
        RPC::call('movim_fill', 'post_widget', $this->prepareEmpty());
        RPC::call('Menu.refresh');
        //RPC::call('Menu_ajaxGetAll');
    }

    function ajaxGetContact($jid)
    {
        $c = new Contact;
        $c->ajaxGetDrawer($jid);
    }

    function ajaxGetPost($id)
    {
        $pd = new \Modl\PostnDAO;
        $p  = $pd->getItem($id);

        $gi = new GetItem;
        $gi->setTo($p->origin)
           ->setNode($p->node)
           ->setId($p->nodeid)
           ->request();

        $html = $this->preparePost($p);

        RPC::call('MovimUtils.pushState', $this->route('news', $id));

        RPC::call('movim_fill', 'post_widget', $html);
    }

    function ajaxDelete($to, $node, $id)
    {
        $view = $this->tpl();

        $view->assign('to', $to);
        $view->assign('node', $node);
        $view->assign('id', $id);

        Dialog::fill($view->draw('_post_delete', true));
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

    function ajaxPublishComment($form, $to, $node, $id)
    {
        $comment = trim($form->comment->value);

        $validate_comment = Validator::stringType()->notEmpty();
        $validate_id = Validator::stringType()->length(6, 128)->noWhitespace();

        if(!$validate_comment->validate($comment)
        || !$validate_id->validate($id)) return;

        $cp = new CommentPublish;
        $cp->setTo($to)
           ->setFrom($this->user->getLogin())
           ->setParentId($id)
           ->setContent(htmlspecialchars(rawurldecode($comment)))
           ->request();
    }

    function prepareEmpty()
    {
        $view = $this->tpl();

        $nd = new \modl\PostnDAO;
        $cd = new modl\ContactDAO;

        $view = $this->tpl();

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('top', $cd->getTop(6));
        $view->assign('blogs', $nd->getLastBlogPublic(0, 6));
        $view->assign('posts', $nd->getLastPublished(0, 4));
        $view->assign('jid', $this->user->getLogin());

        return $view->draw('_post_empty', true);
    }

    function preparePost($p, $external = false, $public = false)
    {
        $view = $this->tpl();

        if(isset($p)) {
            if(isset($p->commentplace) && !$external) {
                $this->ajaxGetComments($p->commentplace, $p->nodeid);
            }

            $view->assign('recycled', false);
            $view->assign('external', $external);
            $view->assign('public', $public);

            // Is it a recycled post ?
            if($p->getContact()->jid
            && $p->node == 'urn:xmpp:microblog:0'
            && ($p->origin != $p->getContact()->jid)) {
                $cd = new \Modl\ContactDAO;
                $view->assign('recycled', $cd->get($p->origin));
            }

            $view->assign('post', $p);
            $view->assign('attachments', $p->getAttachments());
            return $view->draw('_post', true);
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
