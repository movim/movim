<?php

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use Moxl\Xec\Action\Pubsub\Subscribe;

use Movim\Widget\Base;
use Movim\Session;
use App\Cache;

use Respect\Validation\Validator;
use Michelf\MarkdownExtra;
use Cocur\Slugify\Slugify;

class PublishBrief extends Base
{
    public function load()
    {
        $this->registerEvent('pubsub_postpublish_handle', 'onPublish');
        $this->registerEvent('microblog_commentcreatenode_handle', 'onCommentNodeCreated');

        $this->addjs('publish.js');
        $this->addcss('publish.css');
    }

    public function onPublish($packet)
    {
        Toast::send($this->__('post.published'));

        list($to, $node, $id, $repost, $comments) = array_values($packet->content);

        if (!$repost && $comments) {
            $this->ajaxCreateComments(($comments === true) ? $to : $comments, $id);
        }

        if ($node == 'urn:xmpp:microblog:0') {
            $this->rpc('MovimUtils.softRedirect', $this->route('news'));
        } else {
            $this->rpc('MovimUtils.softRedirect', $this->route('community', [$to, $node]));
        }
    }

    public function onCommentNodeCreated($packet)
    {
        list($server, $parentid) = array_values($packet->content);

        $s = new Subscribe;
        $s->setTo($server)
          ->setFrom($this->user->id)
          ->setNode('urn:xmpp:microblog:0:comments/'.$parentid)
          ->request();
    }

    public function ajaxCreateComments($server, $id)
    {
        if (!$this->validateServerNode($server, $id)) {
            return;
        }

        $cn = new CommentCreateNode;
        $cn->setTo($server)
           ->setParentId($id)
           ->request();
    }

    public function ajaxGet(
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
        $this->rpc('publish.checkEmbed');
    }

    public function ajaxHttpSaveDraft($form)
    {
        $p = new \App\Post;
        $p->title = $form->title->value;
        $p->content = $form->content->value;

        if (Validator::notEmpty()->url()->validate($form->embed->value)) {
            $p->link = $form->embed->value;
        }

        Cache::c('draft', $p);

        if (empty($p->title) && empty($p->content) && empty($p->link)) {
            http_response_code(204);
        }
    }

    public function ajaxPreview($form)
    {
        if ($form->content->value != '') {
            $view = $this->tpl();

            $content = htmlspecialchars($form->content->value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $doc = new DOMDocument;
            $parser = new MarkdownExtra;
            $parser->hashtag_protection = true;

            $doc->loadXML('<div>'.addHFR($parser->transform($content)).'</div>');
            $view->assign('title', $form->title->value);
            $view->assign('content', substr($doc->saveXML($doc->getElementsByTagName('div')->item(0)), 5, -6));

            Dialog::fill($view->draw('_publishbrief_preview'), true);
        } else {
            Toast::send($this->__('publish.no_content_preview'));
        }
    }

    public function ajaxHttpDaemonPublish($form)
    {
        $this->rpc('publish.disableSend');

        Cache::c('draft', null);

        if (Validator::stringType()->notEmpty()->validate(trim($form->title->value))) {
            $p = new PostPublish;
            $p->setFrom($this->user->id)
              ->setTo($form->to->value)
              ->setTitle(htmlspecialchars($form->title->value))
              ->setNode($form->node->value);

            $comments = $this->user->session->getCommentsService();

            $tags = [];
            $tagsTitle = getHashtags(htmlspecialchars($form->title->value));
            if (is_array($tagsTitle)) {
                array_merge($tags, $tagsTitle);
            }

            if (Validator::stringType()->notEmpty()->validate(trim($form->content->value))) {
                $content = htmlspecialchars($form->content->value, ENT_XML1 | ENT_COMPAT, 'UTF-8');

                $parser = new MarkdownExtra;
                $parser->hashtag_protection = true;
                $contentXhtml = addHFR($parser->transform($content));

                $tagsContent = getHashtags(htmlspecialchars($form->content->value));
                if (is_array($tagsContent)) {
                    $tags = array_merge($tags, $tagsContent);
                }

                if (!empty($content)) {
                    $p->setContent(htmlspecialchars($content));
                }

                if (!empty($contentXhtml)) {
                    $p->setContentXhtml($contentXhtml);
                }
            }

            if (Validator::stringType()->notEmpty()->validate(trim($form->id->value))) {
                $p->setId($form->id->value);

                $post = \App\Post::where('server', $form->to->value)
                                 ->where('node', $form->node->value)
                                 ->where('nodeid', $form->id->value)
                                 ->first();

                if (isset($post)) {
                    $p->setPublished(strtotime($post->published));
                }
            } else {
                $slugify = new Slugify;
                $slug = $slugify->slugify(
                    strtok(wordwrap($form->title->value, 80, "\n"), "\n")
                );

                if (!empty($slug) && strlen($slug) > 32) {
                    $p->setId($slug. '-'. \generateKey(6));
                }
            }

            if ($comments) {
                $p->enableComments($comments->server);
            } else {
                $p->enableComments();
            }

            if ($form->open->value === true) {
                $p->isOpen();
            }

            if (is_array($tags)) {
                $p->setTags($tags);
            }

            if ($form->reply->value) {
                $post = \App\Post::where('server', $form->replyserver->value)
                                 ->where('node', $form->replynode->value)
                                 ->where('nodeid', $form->replynodeid->value)
                                 ->first();
                $p->setReply($post->getRef());
            }

            if (Validator::notEmpty()->url()->validate($form->embed->value)) {
                try {
                    $embed = (new \App\Url)->resolve($form->embed->value);
                    $p->setLink($form->embed->value);

                    $imagenumber = $form->imagenumber->value;

                    if (($embed->type == 'photo' || !empty($embed->images))
                    && array_key_exists($imagenumber, $embed->images)) {
                        $p->setImage(
                            $embed->images[$imagenumber]['url'],
                            $embed->title,
                            $embed->images[$imagenumber]['mime']
                        );
                    }

                    $p->setLink(
                        $form->embed->value,
                        $embed->title,
                        'text/html',
                        $embed->description,
                        $embed->providerIcon
                    );
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }

            $p->request();
            $this->ajaxGet();
        } else {
            $this->rpc('publish.enableSend');
            Toast::send($this->__('publish.no_title'));
        }
    }

    public function ajaxEmbedLoading()
    {
        $this->rpc(
            'MovimTpl.fill',
            '#publishbrief ul.embed',
            '<li><p class="normal">' . $this->__('global.loading') . '</p></li>'
        );
    }

    public function ajaxEmbedTest($url, $imagenumber = 0)
    {
        $url = trim($url);

        if ($url == '') {
            return;
        }

        if (!Validator::url()->validate($url)) {
            Toast::send($this->__('publish.valid_url'));
            $this->ajaxClearEmbed();
            return;
        }

        $this->rpc('Dialog_ajaxClear');

        try {
            $embed = (new \App\Url)->resolve($url);
            $this->rpc('MovimTpl.fill', '#publishbrief ul.embed', $this->prepareEmbed($embed, $imagenumber));
            if ($embed->type == 'link') {
                $this->rpc('publish.setTitle', $embed->title);
            }
        } catch (Exception $e) {
            $this->ajaxClearEmbed();
            error_log($e->getMessage());
        }
    }

    public function ajaxEmbedChooseImage($url)
    {
        $url = trim($url);

        try {
            $view = $this->tpl();
            $view->assign('embed', (new \App\Url)->resolve($url));
            Drawer::fill($view->draw('_publishbrief_images'), true);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function ajaxClearEmbed()
    {
        //$session = Session::start();
        //$session->remove('share_url');

        $p = Cache::c('draft');
        if ($p && $p->link) {
            unset($p->link);
            Cache::c('draft', $p);
        }

        $this->rpc('MovimTpl.fill', '#publishbrief ul.embed', $this->prepareEmbedDefault());
    }

    public function prepareEmbedDefault()
    {
        $view = $this->tpl();
        return $view->draw('_publishbrief_embed_default');
    }

    public function prepareEmbed($embed, $imagenumber = 0)
    {
        $view = $this->tpl();
        $view->assign('embed', $embed);
        $view->assign('imagenumber', $imagenumber);
        return $view->draw('_publishbrief_embed');
    }

    public function preparePublishBrief(
        $server = false,
        $node = false,
        $id = false,
        $reply = false,
        $extended = false
    ) {
        if ($server == false
        && $node == false) {
            $server = $this->user->id;
            $node = 'urn:xmpp:microblog:0';
        }

        $post = false;
        $view = $this->tpl();

        if ($id) {
            $p = \App\Post::where('server', $server)
                          ->where('node', $node)
                          ->where('nodeid', $id)
                          ->first();

            if ($p) {
                if ($p->isEditable() && !$reply) {
                    $post = $p;
                }

                if ($p->isReply()) {
                    $reply = $p->getReply();
                } elseif ($reply) {
                    $reply = $p;
                }
            }
        }

        $session = Session::start();
        $view->assign('url', $session->get('share_url'));
        $view->assign('draft', Cache::c('draft'));
        $view->assign('post', $post);

        if ($reply) {
            $view->assign('to', $this->user->id);
            $view->assign('node', 'urn:xmpp:microblog:0');
            $view->assign('reply', $reply);
            $view->assign('replyblock', (new \Post)->prepareTicket($reply));
        } else {
            $view->assign('to', $server);
            $view->assign('node', $node);
            $view->assign('reply', false);
            $view->assign('embed', $this->prepareEmbedDefault());
        }

        $view->assign('extended', $extended);

        return $view->draw('_publishbrief');
    }

    public function ajaxLink()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_publishbrief_link'));
    }

    public function ajaxDisplayPrivacy($open)
    {
        Toast::send(($open)
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
