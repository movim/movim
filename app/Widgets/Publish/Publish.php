<?php

namespace App\Widgets\Publish;

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use Moxl\Xec\Action\Pubsub\GetConfig;
use Moxl\Xec\Action\Pubsub\Subscribe;

use Movim\Widget\Base;
use Movim\Session;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Respect\Validation\Validator;

use App\Draft;
use App\DraftEmbed;
use App\Post as AppPost;
use App\Upload;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Post\Post;
use App\Widgets\Toast\Toast;
use Moxl\Xec\Payload\Packet;

class Publish extends Base
{
    public function load()
    {
        $this->registerEvent('pubsub_postpublish_handle', 'onPublish');
        $this->registerEvent('pubsub_postpublish_errorforbidden', 'onPublishErrorForbidden');
        $this->registerEvent('pubsub_postpublish_errorpayloadtoobig', 'onPayloadTooBig');
        $this->registerEvent('microblog_commentcreatenode_handle', 'onCommentNodeCreated');
        $this->registerEvent('pubsub_getconfig_handle', 'onBlogConfig');

        $this->addjs('publish.js');
        $this->addcss('publish.css');
    }

    public function onPublish(Packet $packet)
    {
        Toast::send($this->__('post.published'));

        list($to, $node, $id, $repost, $comments) = array_values($packet->content);

        if (!$repost && $comments) {
            $this->ajaxCreateComments(($comments === true) ? $to : $comments, $id);
        }

        if ($node == AppPost::MICROBLOG_NODE) {
            $this->rpc('MovimUtils.reload', $this->route('news'));
        } elseif ($node != AppPost::STORIES_NODE) {
            $this->rpc('MovimUtils.reload', $this->route('community', [$to, $node]));
        }
    }

    public function onBlogConfig(Packet $packet)
    {
        if ($packet->content['access_model'] == 'presence') {
            $view = $this->tpl();
            $this->rpc('MovimTpl.fill', '#publish_blog_presence', $view->draw('_publish_blog_presence'));
        }
    }

    public function onPublishErrorForbidden(Packet $packet)
    {
        Toast::send($this->__('publish.publish_error_forbidden'));
        $this->rpc('Publish.enableSend');
    }

    public function onPayloadTooBig(Packet $packet)
    {
        Toast::send($this->__('publish.publish_error_payload_to_big'));
        $this->rpc('Publish.enableSend');
    }

    public function onCommentNodeCreated(Packet $packet)
    {
        list($server, $parentid) = array_values($packet->content);

        $s = new Subscribe;
        $s->setTo($server)
            ->setFrom($this->me->id)
            ->setNode(AppPost::COMMENTS_NODE . '/' . $parentid)
            ->request();
    }

    public function ajaxCreateComments(string $server, string $id)
    {
        if (!validateServerNode($server, $id)) {
            return;
        }

        $cn = new CommentCreateNode;
        $cn->setTo($server)
            ->setParentId($id)
            ->request();
    }

    public function ajaxHttpSaveTitle($id, $title)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft) {
            $draft->title = $title;
            $draft->save();
            $this->rpc('MovimUtils.addClass', '#publish textarea[name=title] + label span.save', 'saved');
        }
    }

    public function ajaxHttpSaveContent($id, $content)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft) {
            $draft->content = $content;
            $draft->save();
            $this->rpc('MovimUtils.addClass', '#publish textarea[name=content] + label span.save', 'saved');
        }
    }

    public function ajaxPreview($id)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft && $draft->isNotEmpty()) {
            $view = $this->tpl();
            $doc = new \DOMDocument;
            $converter = new GithubFlavoredMarkdownConverter([
                'html_input' => 'escape',
                'allow_unsafe_links' => true,
            ]);

            $doc->loadXML('<div>' . $converter->convert($draft->content) . '</div>');
            $view->assign('title', $draft->title);
            $view->assign('content', substr($doc->saveXML($doc->getElementsByTagName('div')->item(0)), 5, -6));

            Drawer::fill('publish_preview', $view->draw('_publish_preview'), true);
        } else {
            Toast::send($this->__('publish.no_title'));
        }
    }

    public function ajaxPublish($id)
    {
        $this->rpc('Publish.disableSend');

        $draft = $this->me->drafts()->find($id);

        if ($draft && $draft->isNotEmpty()) {
            if (!$draft->isSmallEnough()) {
                $this->rpc('Publish.enableSend');
                Toast::send($this->__('publish.too_long', Draft::LENGTH_LIMIT));
                return;
            }

            $p = new PostPublish;
            $p->setFrom($this->me->id)
                ->setTo($draft->server)
                ->setNode($draft->node)
                ->setTitle(htmlspecialchars($draft->title));

            $comments = $this->me->session->getCommentsService();

            $tags = [];

            $tagsTitle = getHashtags(htmlspecialchars($draft->title));
            if (is_array($tagsTitle)) {
                array_merge($tags, $tagsTitle);
            }

            if (Validator::stringType()->notEmpty()->isValid(trim($draft->content))) {
                $converter = new GithubFlavoredMarkdownConverter([
                    'html_input' => 'escape',
                    'allow_unsafe_links' => true,
                ]);

                $contentXhtml = $converter->convert($draft->content);

                $tagsContent = getHashtags($draft->content);
                if (is_array($tagsContent)) {
                    $tags = array_merge($tags, $tagsContent);
                }

                if (!empty($draft->content)) {
                    $p->setContent($draft->content);
                }

                if (!empty($contentXhtml)) {
                    $p->setContentXhtml($contentXhtml);
                }
            }

            if (Validator::stringType()->notEmpty()->isValid(trim($draft->nodeid))) {
                $p->setId($draft->nodeid);

                $post = \App\Post::where('server', $draft->server)
                    ->where('node', $draft->node)
                    ->where('nodeid', $draft->nodeid)
                    ->first();

                if (isset($post)) {
                    $p->setPublished(strtotime($post->published));
                }
            } else {
                $p->setId($this->titleToSlug($draft->title));
            }

            if (!$draft->comments_disabled) {
                if ($comments) {
                    $p->enableComments($comments->server);
                } else {
                    $p->enableComments();
                }
            }

            if ($draft->open) {
                $p->isOpen();
            }

            if (is_array($tags)) {
                $p->setTags($tags);
            }

            if ($draft->reply) {
                $post = \App\Post::where('server', $draft->reply->server)
                    ->where('node', $draft->reply->node)
                    ->where('nodeid', $draft->reply->nodeid)
                    ->first();
                $p->setReply($post->getRef());
            }

            $hasImage = false;

            foreach ($draft->embeds as $embed) {
                $resolved = $embed->resolve();

                // The url is an image
                if (
                    $resolved->type == 'image'
                    && $resolved->images[0]['url'] == $embed->url
                ) {
                    if (!$hasImage) $hasImage = true;

                    $p->addImage(
                        $resolved->images[0]['url'],
                        $resolved->title,
                        $resolved->contentType
                    );
                }

                // The url is a gallery
                /*elseif (is_array($resolved->images) && count($resolved->images) > 1) {
                    // If an image was picked (0 is not picked)
                    if ($embed->imagenumber > 0 && array_key_exists($embed->imagenumber-1, $resolved->images)) {
                        $p->addImage(
                            $resolved->images[$embed->imagenumber-1]['url'],
                            $resolved->title,
                            $resolved->images[$embed->imagenumber-1]['mime']
                        );
                    }

                    $p->addLink(
                        $embed->url,
                        $resolved->title,
                        'text/html',
                        $resolved->description,
                        $resolved->providerIcon
                    );
                }*/

                // The url is a link
                else {
                    $p->addLink(
                        $embed->url,
                        $resolved->title,
                        'text/html',
                        $resolved->description,
                        $resolved->providerIcon
                    );
                }
            }

            $info = \App\Info::where('server', $draft->server)
                ->where('node', $draft->node)
                ->first();

            if ($info && $info->isGallery() && !$hasImage) {
                $this->rpc('Publish.enableSend');
                Toast::send($this->__('publish.no_picture'));
                return;
            }

            $p->request();
            $draft->delete();
        } else {
            $this->rpc('Publish.enableSend');
            Toast::send($this->__('publish.no_title'));
        }
    }

    public function ajaxLink()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_publish_link'));
    }

    public function ajaxAddUpload(int $draftId, string $uploadId)
    {
        $upload = Upload::find($uploadId);

        if ($upload && $upload->uploaded) {
            $this->addEmbed($draftId, $upload->geturl);
        }
    }

    public function ajaxAddUrl(int $draftId, string $url)
    {
        $this->addEmbed($draftId, $url);
    }

    private function addEmbed(int $draftId, string $url)
    {
        $draft = $this->me->drafts()->find($draftId);

        if (Validator::url()->isValid($url)) {
            $embed = $draft->embeds()->where('url', $url)->first();

            if (!$embed) {
                $embed = new DraftEmbed;
                $embed->draft_id = $draftId;
                $embed->url = $url;
                $embed->save();
            }

            $embed->refresh();

            $this->rpc('MovimTpl.append', '#publishembeds', $this->prepareEmbed($embed));
            $this->rpc('Dialog_ajaxClear');
        } else {
            Toast::send($this->__('publish.valid_url'));
        }
    }

    public function ajaxTryResolveShareUrl($id)
    {
        $session = Session::instance();
        $shareUrl = $session->get('share_url');

        if ($shareUrl) {
            $this->ajaxAddUrl($id, $shareUrl);
            $session->delete('share_url');
        }
    }

    public function ajaxHttpRemoveEmbed($id, $embedId)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft) {
            $embed = $draft->embeds()->find($embedId);

            if ($embed) {
                $this->rpc('MovimTpl.remove', '#' . $embed->HTMLId);
                $embed->delete();
            }
        }
    }

    public function ajaxOpenlinkPreview($id)
    {
        $draft = $this->me->drafts()->find($id);
        $view = $this->tpl();

        if ($draft && $draft->open && !$draft->nodeid) {
            $slug = $this->titleToSlug($draft->title);

            $view->assign('link', ($draft->node == AppPost::MICROBLOG_NODE)
                ? $this->route('blog', [$draft->server, $slug])
                : $this->route('community', [$draft->server, $draft->node, $slug]));

            $this->rpc('MovimTpl.fill', '#publish_preview_url', $view->draw('_publish_preview_url'));
            return;
        }

        $this->rpc('MovimTpl.fill', '#publish_preview_url', '');
    }

    public function ajaxTogglePrivacy($id, bool $open)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft) {
            $draft->open = $open;
            $draft->save();

            $this->ajaxCheckPrivacy($id);

            if (!$open) {
                $this->rpc('MovimTpl.fill', '#publish_preview_url', '');
            }

            Toast::send(($open)
                ? $this->__('post.public_yes')
                : $this->__('post.public_no'));
        }
    }

    public function ajaxCheckPrivacy($id)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft && $draft->open) {
            (new GetConfig)->setNode(AppPost::MICROBLOG_NODE)->request();
        } else {
            $this->rpc('MovimTpl.fill', '#publish_blog_presence', '');
        }
    }

    public function ajaxToggleCommentsDisabled($id, bool $commentsDisabled)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft) {
            $draft->comments_disabled = $commentsDisabled;
            $draft->save();

            Toast::send(($commentsDisabled)
                ? $this->__('post.comments_disabled_yes')
                : $this->__('post.comments_disabled_no'));
        }
    }

    public function ajaxClearReply($id)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft) {
            $draft->reply_id = null;
            $draft->save();

            $this->rpc('MovimUtils.redirect', $this->route('publish', [$draft->server, $draft->node, $draft->nodeid]));
        }
    }

    public function prepareToggles(Draft $draft)
    {
        $view = $this->tpl();
        $view->assign('draft', $draft);
        return $view->draw('_publish_toggles');
    }

    public function prepareEmbed(DraftEmbed $embed)
    {
        $view = $this->tpl();
        $view->assign('embed', $embed);
        return $view->draw('_publish_embed');
    }

    public function ajaxEmbedChooseImage($id, $embedId)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft) {
            $embed = $draft->embeds()->find($embedId);

            if ($embed) {
                $view = $this->tpl();
                $view->assign('embed', $embed);
                Drawer::fill('publish_images', $view->draw('_publish_images'), true);
            }
        }
    }

    public function ajaxHttpSetImageNumber($id, $embedId, $imageNumber)
    {
        $draft = $this->me->drafts()->find($id);

        if ($draft) {
            $embed = $draft->embeds()->find($embedId);

            if ($embed) {
                $embed->imagenumber = $imageNumber;
                $embed->save();
                $this->rpc('MovimTpl.remove', '#' . $embed->HTMLId);
                $this->rpc('MovimTpl.append', '#publishembeds', $this->prepareEmbed($embed));
            }
        }

        $this->rpc('Drawer.clear');
    }

    public function ajaxHttpGet(
        string $type = 'brief',
        ?string $server = null,
        ?string $node = null,
        ?string $nodeId = null,
        ?string $replyServer = null,
        ?string $replyNode = null,
        ?string $replyNodeId = null,
    ) {
        $view = $this->tpl();

        if ($server == null) {
            $server = $this->me->id;
        }

        if ($node == null) {
            $node = AppPost::MICROBLOG_NODE;
        }

        if ($nodeId == null) {
            $nodeId = '';
        }

        if ($node == AppPost::MICROBLOG_NODE) {
            $view->assign('icon', \App\Contact::firstOrNew(['id' => $server]));
        } else {
            $info = \App\Info::where('server', $server)
                ->where('node', $node)
                ->first();
            $view->assign('icon', $info);
        }

        $draft = $this->me->drafts()
            ->where('server', $server)
            ->where('node', $node)
            ->where('nodeid', $nodeId)
            ->first();

        if (!$draft) {
            $draft = new Draft;
            $draft->user_id = $this->me->id;
            $draft->server = $server;
            $draft->node = $node;
            $draft->nodeid = $nodeId;

            // If we find an existing post let's fill the draft
            $draft->tryFillPost();
        }

        if ($draft->content != null) {
            $type = "article";
        }

        // Reply
        $reply = null;

        if ($replyServer && $replyNode && $replyNodeId) {
            $reply = \App\Post::where('server', $replyServer)
                ->where('node', $replyNode)
                ->where('nodeid', $replyNodeId)
                ->first();
        } elseif ($draft->reply_id) {
            $reply = \App\Post::find($draft->reply_id);
        }

        if ($reply) {
            $draft->reply_id = $reply->id;
        } else {
            $draft->reply_id = null;
        }

        $draft->save();
        $draft->refresh();

        if ($draft->reply) {
            $view->assign('replyblock', (new Post)->prepareTicket($draft->reply));
        }

        $view->assign('type', $type);
        $view->assign('draft', $draft);

        $this->rpc('MovimTpl.fill', '#publish', $view->draw('_publish_form'));
        $this->rpc('Publish.init');
    }

    private function titleToSlug(?string $title = null): string
    {
        $slug = slugify(
            strtok(wordwrap($title, 80, "\n"), "\n")
        );

        if (!empty($slug) && strlen($slug) > 24) {
            return $slug . '-' . \generateKey(6);
        }

        return \generateUUID();
    }
}
