<?php

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use Moxl\Xec\Action\Pubsub\Subscribe;

use Movim\Session;
use Movim\Cache;

use Respect\Validation\Validator;
use Michelf\MarkdownExtra;

class PublishBrief extends \Movim\Widget\Base
{
    function load()
    {
        $this->registerEvent('pubsub_postpublish_handle', 'onPublish');
        $this->registerEvent('microblog_commentcreatenode_handle', 'onCommentNodeCreated');

        $this->addjs('publishbrief.js');
        $this->addcss('publishbrief.css');
    }

    function onPublish($packet)
    {
        Notification::append(false, $this->__('post.published'));

        list($to, $node, $id, $repost, $comments) = array_values($packet->content);

        if(!$repost && $comments) {
            $this->ajaxCreateComments(($comments === true) ? $to : $comments, $id);
        }

        if($node == 'urn:xmpp:microblog:0') {
            $this->rpc('MovimUtils.redirect', $this->route('news'));
        } else {
            $this->rpc('MovimUtils.redirect', $this->route('community', [$to, $node]));
        }
    }

    function onCommentNodeCreated($packet)
    {
        list($server, $parentid) = array_values($packet->content);

        $s = new Subscribe;
        $s->setTo($server)
          ->setFrom($this->user->getLogin())
          ->setNode('urn:xmpp:microblog:0:comments/'.$parentid)
          ->request();
    }

    function ajaxCreateComments($server, $id)
    {
        if(!$this->validateServerNode($server, $id)) return;

        $cn = new CommentCreateNode;
        $cn->setTo($server)
           ->setParentId($id)
           ->request();
    }

    function ajaxGet(
        $server = false,
        $node = false,
        $id = false,
        $reply = false,
        $extended = false
    ) {
        $this->rpc(
            'MovimTpl.fill',
            '#publishbrief',
            $this->preparePublishBrief($server, $node, $id, $reply, $extended)
        );
        $this->rpc('PublishBrief.checkEmbed');
    }

    function ajaxSaveDraft($form)
    {
        $p = new \Modl\Postn;
        $p->title = $form->title->value;

        if(Validator::notEmpty()->url()->validate($form->embed->value)) {
            array_push($p->links, $form->embed->value);
        }

        Cache::c('draft', $p);
    }

    function ajaxPreview($form)
    {
        if($form->content->value != '') {
            $view = $this->tpl();

            $doc = new DOMDocument;

            $parser = new MarkdownExtra;
            $parser->hashtag_protection = true;

            $doc->loadXML('<div>'.addHFR(addHFR($parser->transform($form->content->value))).'</div>');
            $view->assign('content', substr($doc->saveXML($doc->getElementsByTagName('div')->item(0)), 5, -6));

            Dialog::fill($view->draw('_publishbrief_preview', true), true);
        } else {
            Notification::append(false, $this->__('publishbrief.no_content_preview'));
        }
    }

    function ajaxPublish($form)
    {
        $this->rpc('PublishBrief.disableSend');

        Cache::c('draft', null);

        if(Validator::stringType()->notEmpty()->validate(trim($form->title->value))) {
            $p = new PostPublish;
            $p->setFrom($this->user->getLogin())
              ->setTo($form->to->value)
              ->setTitle(htmlspecialchars($form->title->value))
              ->setNode($form->node->value);

            $cd = new \Modl\CapsDAO;
            $comments = $cd->getComments($this->user->getServer());

            $tags = [];
            $tagsTitle = getHashtags(htmlspecialchars($form->title->value));
            if(is_array($tagsTitle)) array_merge($tags, $tagsTitle);

            if(Validator::stringType()->notEmpty()->validate(trim($form->content->value))) {
                $content = $form->content->value;

                $parser = new MarkdownExtra;
                $parser->hashtag_protection = true;
                $content_xhtml = addHFR($parser->transform($content));

                $tagsContent = getHashtags(htmlspecialchars($form->content->value));
                if(is_array($tagsContent)) $tags = array_merge($tags, $tagsContent);

                if(!empty($content)) {
                    $p->setContent(htmlspecialchars($content));
                }

                if(!empty($content_xhtml)) {
                    $p->setContentXhtml($content_xhtml);
                }
            }

            if(Validator::stringType()->notEmpty()->validate(trim($form->id->value))) {
                $p->setId($form->id->value);

                $pd = new \Modl\PostnDAO;
                $post = $pd->get($form->to->value, $form->node->value, $form->id->value);

                if(isset($post)) {
                    $p->setPublished(strtotime($post->published));
                }
            }

            if($comments) {
                $p->enableComments($comments->node);
            } else {
                $p->enableComments();
            }

            if($form->open->value === true) {
                $p->isOpen();
            }

            if(is_array($tags)) {
                $p->setTags($tags);
            }

            if($form->reply->value) {
                $pd = new \Modl\PostnDAO;
                $post = $pd->get($form->replyorigin->value,
                                 $form->replynode->value,
                                 $form->replynodeid->value);
                $p->setReply($post->getRef());
            }

            if(Validator::notEmpty()->url()->validate($form->embed->value)) {
                try {
                    $murl = new \Modl\Url;
                    $embed = $murl->resolve($form->embed->value);
                    $p->setLink($form->embed->value);

                    $imagenumber = $form->imagenumber->value;

                    if($embed->type == 'photo' || isset($embed->images)) {
                        $p->setImage($embed->images[$imagenumber]['url'],
                                     $embed->title,
                                     $embed->images[$imagenumber]['mime']);
                    }

                    $p->setLink($form->embed->value,
                                $embed->title,
                                'text/html',
                                $embed->description,
                                $embed->providerIcon);
                } catch(Exception $e) {
                    error_log($e->getMessage());
                }
            }

            $p->request();
            $this->ajaxGet();
        } else {
            $this->rpc('PublishBrief.enableSend');
            Notification::append(false, $this->__('publishbrief.no_title'));
        }
    }

    function ajaxEmbedLoading()
    {
        $this->rpc(
            'MovimTpl.fill',
            '#publishbrief ul.embed',
            '<li><p class="normal">' . $this->__('global.loading') . '</p></li>');
    }

    function ajaxEmbedTest($url, $imagenumber = 0)
    {
        if($url == '') {
            return;
        }

        if(!Validator::url()->validate($url)) {
            Notification::append(false, $this->__('publishbrief.valid_url'));
            $this->ajaxClearEmbed();
            return;
        }

        $this->rpc('Dialog_ajaxClear');

        try {
            $murl = new \Modl\Url;
            $embed = $murl->resolve($url);
            $this->rpc('MovimTpl.fill', '#publishbrief ul.embed', $this->prepareEmbed($embed, $imagenumber));
            if ($embed->type == 'link') {
                $this->rpc('PublishBrief.setTitle', $embed->title);
            }
        } catch(Exception $e) {
            error_log($e->getMessage());
        }
    }

    function ajaxEmbedChooseImage($url)
    {
        try {
            $view = $this->tpl();
            $murl = new \Modl\Url;
            $view->assign('embed', $murl->resolve($url));
            Drawer::fill($view->draw('_publishbrief_images', true), true);
        } catch(Exception $e) {
            error_log($e->getMessage());
        }
    }

    function ajaxClearEmbed()
    {
        $session = Session::start();
        $session->remove('share_url');
        $this->rpc('MovimTpl.fill', '#publishbrief ul.embed', $this->prepareEmbedDefault());
    }

    function prepareEmbedDefault()
    {
        $view = $this->tpl();
        return $view->draw('_publishbrief_embed_default', true);
    }

    function prepareReply($reply)
    {
        $view = $this->tpl();
        $view->assign('reply', $reply);
        return $view->draw('_publishbrief_reply', true);
    }

    function prepareEmbed($embed, $imagenumber = 0)
    {
        $view = $this->tpl();
        $view->assign('embed', $embed);
        $view->assign('imagenumber', $imagenumber);
        return $view->draw('_publishbrief_embed', true);
    }

    function preparePublishBrief(
        $server = false,
        $node = false,
        $id = false,
        $reply = false,
        $extended = false
    ) {
        if($server == false
        && $node == false) {
            $server = $this->user->getLogin();
            $node = 'urn:xmpp:microblog:0';
        }

        $post = false;
        $view = $this->tpl();

        if($id) {
            $pd = new \Modl\PostnDAO;
            $p = $pd->get($server, $node, $id);

            if($p) {
                if($p->isEditable() && !$reply) {
                    $post = $p;
                }

                if($p->isReply()) {
                    $reply = $p->getReply();
                } elseif($reply) {
                    $reply = $p;
                }
            }
        }

        $session = Session::start();
        $view->assign('url', $session->get('share_url'));
        $view->assign('draft', Cache::c('draft'));

        if($reply) {
            $view->assign('to', $this->user->getLogin());
            $view->assign('node', 'urn:xmpp:microblog:0');
            $view->assign('item', $post);
            $view->assign('reply', $reply);
            $view->assign('replyblock', $this->prepareReply($reply));
        } else {
            $view->assign('to', $server);
            $view->assign('node', $node);
            $view->assign('item', $post);
            $view->assign('reply', false);
            $view->assign('embed', $this->prepareEmbedDefault());
        }

        $view->assign('extended', $extended);

        return $view->draw('_publishbrief', true);
    }

    function ajaxLink()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_publishbrief_link', true));
    }

    function ajaxDisplayPrivacy($open)
    {
        Notification::append(false, ($open)
            ? $this->__('post.public_yes')
            : $this->__('post.public_no'));
    }

    private function validateServerNode($server, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        return ($validate_server->validate($server)
             && $validate_node->validate($node));
    }
}
