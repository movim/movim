<?php

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Pubsub\TestPostPublish;
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use \Michelf\Markdown;
use Respect\Validation\Validator;

class Publish extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('publish.js');
        $this->addcss('publish.css');
        $this->registerEvent('pubsub_postpublish_handle', 'onPublish');
        $this->registerEvent('pubsub_testpostpublish_handle', 'onTestPublish');
        $this->registerEvent('pubsub_testpostpublish_error', 'onTestPublishError');
    }

    function onPublish($packet)
    {
        list($to, $node, $id) = array_values($packet->content);

        // Only for the microblog for the moment
        //if($node == 'urn:xmpp:microblog:0') {
            $this->ajaxCreateComments($to, $id);
        //}

        RPC::call('MovimUtils.redirect', Route::urlize('news', $id));
    }

    function onTestPublish($packet)
    {
        list($server, $node) = array_values($packet->content);
        $this->ajaxCreate($server, $node);
    }

    function onTestPublishError($packet)
    {
        Notification::append(null, $this->__('publish.no_publication'));
    }

    function ajaxCreateBlog()
    {
        $this->ajaxCreate($this->user->getLogin(), 'urn:xmpp:microblog:0');
    }

    function ajaxCreate($server, $node, $id = false)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $post = false;

        if($id) {
            $pd = new \modl\PostnDAO();
            $p = $pd->getItem($id);
            if($p->isEditable()) {
                $post = $p;
            }
        }

        $view = $this->tpl();
        $view->assign('to', $server);
        $view->assign('node', $node);
        $view->assign('item', $post);

        RPC::call('MovimTpl.fill', 'main section > div:nth-child(2)', $view->draw('_publish_create', true));

        $pd = new \Modl\ItemDAO;
        $item = $pd->getItem($server, $node);

        $view = $this->tpl();
        $view->assign('server', $server);
        $view->assign('node', $node);
        $view->assign('post', $post);
        $view->assign('item', $item);

        Header::fill($view->draw('_publish_header', true));

        if($id) {
            RPC::call('Publish.initEdit');
        }

        RPC::call('Publish.setEmbed');
    }

    function ajaxCreateComments($server, $id)
    {
        if(!$this->validateServerNode($server, $id)) return;

        $cn = new CommentCreateNode;
        $cn->setTo($server)
           ->setParentId($id)
           ->request();
    }

    function ajaxFormFilled($server, $node)
    {
        $view = $this->tpl();

        $view->assign('server', $server);
        $view->assign('node', $node);

        Dialog::fill($view->draw('_publish_back_confirm', true));
    }

    function ajaxPreview($form)
    {
        if($form->content->value != '') {
            $view = $this->tpl();

            $doc = new DOMDocument();
            $doc->loadXML('<div>'.addHFR(Markdown::defaultTransform($form->content->value)).'</div>');
            $view->assign('content', substr($doc->saveXML($doc->getElementsByTagName('div')->item(0)), 5, -6));

            Dialog::fill($view->draw('_publish_preview', true), true);
        } else {
            Notification::append(false, $this->__('publish.no_content_preview'));
        }
    }

    function ajaxHelp()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_publish_help', true), true);
    }

    /*
     * Sic, doing this hack and wait to have a proper way to test it in the standard
     */
    function ajaxTestPublish($server, $node)
    {
        if(!$this->validateServerNode($server, $node)) return;

        $t = new TestPostPublish;
        $t->setTo($server)
          ->setNode($node)
          ->request();
    }

    function ajaxPublish($form)
    {
        RPC::call('Publish.disableSend');

        if($form->title->value != '') {
            $p = new PostPublish;
            $p->setFrom($this->user->getLogin())
              ->setTo($form->to->value)
              ->setTitle(htmlspecialchars($form->title->value))
              ->setNode($form->node->value);
              //->setLocation($geo)
              //->enableComments()

            // Still usefull ? Check line 44
            if($form->node->value == 'urn:xmpp:microblog:0') {
                $p->enableComments();
            }

            $content = $content_xhtml = '';

            if($form->content->value != '') {
                $content = $form->content->value;
                $content_xhtml = addHFR(Markdown::defaultTransform($content));
            }

            if($form->id->value != '') {
                $p->setId($form->id->value);

                $pd = new \modl\PostnDAO();
                $post = $pd->getItem($form->id->value);

                if(isset($post)) {
                    $p->setPublished(strtotime($post->published));
                }
            }

            if(Validator::stringType()->notEmpty()->validate($form->tags->value)) {
                $p->setTags(array_unique(
                    array_filter(
                        array_map(
                            function($value) {
                                if(Validator::stringType()->notEmpty()->validate($value)) {
                                    preg_match('/([^\s[:punct:]]|_|-){3,30}/', trim($value), $matches);
                                    if(isset($matches[0])) return $matches[0];
                                }
                            },
                            explode(',', $form->tags->value)
                        )
                    )
                ));
            }

            if(Validator::notEmpty()->url()->validate($form->embed->value)) {
                try {
                    $embed = Embed\Embed::create($form->embed->value);
                    $p->setLink($form->embed->value);

                    if(in_array($embed->type, array('photo', 'rich'))) {
                        $p->setImage($embed->images[0]['value'], $embed->title, $embed->images[0]['mime']);
                    }

                    if($embed->type !== 'photo') {
                        $content_xhtml .= $this->prepareEmbed($embed);
                    }
                } catch(Exception $e) {
                    error_log($e->getMessage());
                }
            }

            if($form->open->value === true) {
                $p->isOpen();
            }

            if($content != '') {
                $p->setContent(htmlspecialchars($content));
            }

            if($content_xhtml != '') {
                $p->setContentXhtml($content_xhtml);
            }

            $p->request();
        } else {
            RPC::call('Publish.enableSend');
            Notification::append(false, $this->__('publish.no_title'));
        }
    }

    function ajaxEmbedTest($url)
    {
        if($url == '') {
            return;
        } elseif(!filter_var($url, FILTER_VALIDATE_URL)) {
            Notification::append(false, $this->__('publish.valid_url'));
            return;
        }

        try {
            $embed = Embed\Embed::create($url);
            $html = $this->prepareEmbed($embed);

            RPC::call('movim_fill', 'preview', '');
            RPC::call('movim_fill', 'gallery', '');

            if(in_array($embed->type, array('photo', 'rich'))) {
                RPC::call('movim_fill', 'gallery', $this->prepareGallery($embed));
            }

            if($embed->type !== 'photo') {
                RPC::call('movim_fill', 'preview', $html);
            }
        } catch(Exception $e) {
            error_log($e->getMessage());
        }
    }

    function prepareEmbed($embed)
    {
        $view = $this->tpl();
        $view->assign('embed', $embed);
        return $view->draw('_publish_embed', true);
    }

    function prepareGallery($embed)
    {
        $view = $this->tpl();
        $view->assign('embed', $embed);
        return $view->draw('_publish_gallery', true);
    }

    private function validateServerNode($server, $node)
    {
        $validate_server = Validator::stringType()->noWhitespace()->length(6, 40);
        $validate_node = Validator::stringType()->length(3, 100);

        return ($validate_server->validate($server)
             && $validate_node->validate($node));
    }

    function display()
    {
    }
}
