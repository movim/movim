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
        $this->registerEvent('microblog_commentsget_handle', 'onComments');
        $this->registerEvent('microblog_commentpublish_handle', 'onCommentPublished');
        $this->registerEvent('microblog_commentsget_error', 'onCommentsError');
        $this->registerEvent('pubsub_getitem_handle', 'onHandle');
    }

    function onHandle($packet)
    {
        $content = $packet->content;

        if(is_array($content) && isset($content['nodeid'])) {
            $pd = new \Modl\PostnDAO;
            $p  = $pd->get($content['origin'], $content['node'], $content['nodeid']);

            if($p) {
                if($p->isComment()) {
                    $this->rpc(
                        'MovimTpl.fill',
                        '#comments',
                        $this->prepareComments($p->getParent())
                    );
                } else {
                    //$this->rpc('MovimUtils.pushState', $this->route('post', [$p->origin, $p->node, $p->nodeid]));
                    $this->rpc('MovimTpl.fill', '#post_widget', $this->preparePost($p));
                    $this->rpc('MovimUtils.enableVideos');
                }
            }
        }
    }

    function onCommentPublished($packet)
    {
        Notification::append(false, $this->__('post.comment_published'));
    }

    function onComments($packet)
    {
        list($server, $node, $id, $parentnode) = array_values($packet->content);

        $pd = new \Modl\PostnDAO;
        $p = $pd->get($server, $parentnode, $id);

        $this->rpc('MovimTpl.fill', '#comments', $this->prepareComments($p));
    }

    function onCommentsError($packet)
    {
        $view = $this->tpl();
        $html = $view->draw('_post_comments_error', true);
        $this->rpc('MovimTpl.fill', '#comments', $html);
    }

    function ajaxClear()
    {
        $this->rpc('MovimUtils.pushState', $this->route('news'));

        $this->rpc('MovimTpl.fill', '#post_widget', $this->prepareEmpty());
        $this->rpc('Menu.refresh');
        //$this->rpc('Menu_ajaxGetAll');
    }

    function ajaxGetContact($jid)
    {
        $c = new ContactActions;
        $c->ajaxGetDrawer($jid);
    }

    function ajaxGetPost($origin, $node, $id)
    {
        $pd = new \Modl\PostnDAO;
        $p  = $pd->get($origin, $node, $id);

        if($p) {
            $html = $this->preparePost($p);

            $this->rpc('MovimTpl.fill', '#post_widget', $html);
            $this->rpc('MovimUtils.enhanceArticlesContent');

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
        } else {
            $this->rpc('MovimTpl.fill', '#post_widget', $this->prepareNotFound());
        }
    }

    function ajaxShare($origin, $node, $id)
    {
        $pd = new \Modl\PostnDAO;
        $p  = $pd->get($origin, $node, $id);

        if($p) {
            $this->rpc('MovimUtils.redirect', $this->route('publish', [$origin, $node, $id, 'share']));
        }
    }

    function requestComments(\Modl\ContactPostn $post)
    {
        $pd = new \Modl\PostnDAO;
        $pd->deleteNode($post->commentorigin, "urn:xmpp:microblog:0:comments/".$post->commentnodeid);

        $c = new CommentsGet;
        $c->setTo($post->commentorigin)
          ->setId($post->commentnodeid)
          ->setParentNode($post->node)
          ->request();
    }

    public function publishComment($comment, $to, $node, $id)
    {
        if(!Validator::stringType()->notEmpty()->validate($comment)
        || !Validator::stringType()->length(6, 128)->noWhitespace()->validate($id)) return;

        $cp = new CommentPublish;
        $cp->setTo($to)
           ->setFrom($this->user->getLogin())
           ->setParentId($id)
           ->setTitle(htmlspecialchars(rawurldecode($comment)))
           ->request();
    }

    public function ajaxPublishComment($form, $to, $node, $id)
    {
        $comment = trim($form->comment->value);

        if($comment != '♥') {
            $this->publishComment($comment, $to, $node, $id);
        }
    }

    public function prepareComments($post)
    {
        $emoji = \MovimEmoji::getInstance();

        $view = $this->tpl();
        $view->assign('post', $post);
        $view->assign('comments', $post->getComments());
        $view->assign('hearth', $emoji->replace('♥'));

        return $view->draw('_post_comments', true);
    }

    public function prepareEmpty()
    {
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

    function prepareNotFound()
    {
        $nd = new \Modl\PostnDAO;

        $view = $this->tpl();

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('blogs', $nd->getLastBlogPublic(0, 8));
        $view->assign('posts', $nd->getLastPublished(false, false, 0, 6));
        $view->assign('jid', $this->user->getLogin());

        return $view->draw('_post_not_found', true);
    }

    function preparePost($p, $external = false, $public = false, $card = false)
    {
        $view = $this->tpl();

        $view->assign('external', $external);

        if(isset($p)) {
            if($p->hasCommentsNode()
            && !$external) {
                $this->requestComments($p); // Broken in case of repost
                $view->assign('commentsdisabled', false);
            } else {
                $viewd = $this->tpl();
                $view->assign('commentsdisabled', $viewd->draw('_post_comments_error', true));
            }

            $view->assign('repost', false);

            $view->assign('prevnext', '');
            $view->assign('comments', '');

            if(!$external) {
                $prevnext = $this->tpl();
                $prevnext->assign('next', $p->getNext());
                $prevnext->assign('previous', $p->getPrevious());
                $view->assign('prevnext', $prevnext->draw('_post_prevnext', true));
            } else {
                $comments = $this->tpl();
                $comments->assign('comments', $p->getComments());
                $view->assign('comments', $comments->draw('_post_comments_external', true));
            }
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

    function display()
    {
        $validate_nodeid = Validator::stringType()->length(10, 100);

        $this->view->assign('nodeid', false);
        if($validate_nodeid->validate($this->get('n'))) {
            $this->view->assign('nodeid', $this->get('n'));
        }
    }
}
