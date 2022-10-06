<?php

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Microblog\CommentCreateNode;
use Moxl\Xec\Action\Pubsub\Subscribe;

use Movim\Widget\Base;
use Movim\Session;

use League\CommonMark\CommonMarkConverter;
use Respect\Validation\Validator;
use Cocur\Slugify\Slugify;

use App\Draft;
use App\Post;
use App\DraftEmbed;

include_once WIDGETS_PATH.'Post/Post.php';

class Publish extends Base
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
        $draft = $this->user->drafts()->find($id);

        if ($draft) {
            $draft->title = $title;
            $draft->save();
            $this->rpc('MovimUtils.addClass', '#publish textarea[name=title] + label span.save', 'saved');
        }
    }

    public function ajaxHttpSaveContent($id, $content)
    {
        $draft = $this->user->drafts()->find($id);

        if ($draft) {
            $draft->content = $content;
            $draft->save();
            $this->rpc('MovimUtils.addClass', '#publish textarea[name=content] + label span.save', 'saved');
        }
    }

    public function ajaxPreview($id)
    {
        $draft = $this->user->drafts()->find($id);

        if ($draft->isNotEmpty()) {
            $view = $this->tpl();
            $doc = new DOMDocument;
            $converter = new CommonMarkConverter([
                'html_input' => 'escape',
                'allow_unsafe_links' => true,
            ]);

            $doc->loadXML('<div>'.$converter->convert($draft->content).'</div>');
            $view->assign('title', $draft->title);
            $view->assign('content', substr($doc->saveXML($doc->getElementsByTagName('div')->item(0)), 5, -6));

            Drawer::fill($view->draw('_publish_preview'), true);
        } else {
            Toast::send($this->__('publish.no_content_preview'));
        }
    }

    public function ajaxPublish($id)
    {
        $this->rpc('Publish.disableSend');

        $draft = $this->user->drafts()->find($id);

        if ($draft && $draft->isNotEmpty()) {
            $p = new PostPublish;
            $p->setFrom($this->user->id)
                ->setTo($draft->server)
                ->setNode($draft->node)
                ->setTitle(htmlspecialchars($draft->title));

            $comments = $this->user->session->getCommentsService();

            $tags = [];

            $tagsTitle = getHashtags(htmlspecialchars($draft->title));
            if (is_array($tagsTitle)) {
                array_merge($tags, $tagsTitle);
            }

            if (Validator::stringType()->notEmpty()->validate(trim($draft->content))) {

                $converter = new CommonMarkConverter([
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

            if (Validator::stringType()->notEmpty()->validate(trim($draft->nodeid))) {
                $p->setId($draft->nodeid);

                $post = \App\Post::where('server', $draft->server)
                                    ->where('node', $draft->node)
                                    ->where('nodeid', $draft->nodeid)
                                    ->first();

                if (isset($post)) {
                    $p->setPublished(strtotime($post->published));
                }
            } else {
                $slugify = new Slugify;
                $slug = $slugify->slugify(
                    strtok(wordwrap($draft->title, 80, "\n"), "\n")
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

            foreach ($draft->embeds as $embed) {
                $resolved = $embed->resolve();

                // The url is an image
                if ($resolved->type == 'image'
                && $resolved->images[0]['url'] == $embed->url) {
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

    public function ajaxAddEmbed($id, $url)
    {
        $draft = $this->user->drafts()->find($id);

        if ($draft && Validator::url()->validate($url)) {
            $embed = $draft->embeds()->where('url', $url)->first();

            if (!$embed) {
                $embed = new DraftEmbed;
                $embed->draft_id = $id;
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
        $session = Session::start();
        $shareUrl = $session->get('share_url');

        if ($shareUrl) {
            $this->ajaxAddEmbed($id, $shareUrl);
            $session->remove('share_url');
        }
    }

    public function ajaxHttpRemoveEmbed($id, $embedId)
    {
        $draft = $this->user->drafts()->find($id);

        if ($draft) {
            $embed = $draft->embeds()->find($embedId);

            if ($embed) {
                $this->rpc('MovimTpl.remove', '#'.$embed->HTMLId);
                $embed->delete();
            }
        }

    }

    public function ajaxTogglePrivacy($id, bool $open)
    {
        $draft = $this->user->drafts()->find($id);

        if ($draft) {
            $draft->open = $open;
            $draft->save();

            Toast::send(($open)
                ? $this->__('post.public_yes')
                : $this->__('post.public_no'));
        }
    }

    public function ajaxClearReply($id)
    {
        $draft = $this->user->drafts()->find($id);

        if ($draft) {
            $draft->reply_id = null;
            $draft->save();

            $this->rpc('MovimUtils.redirect', $this->route('publish', [$draft->server, $draft->node, $draft->nodeid]));
        }

    }

    public function prepareEmbed(DraftEmbed $embed)
    {
        $view = $this->tpl();
        $view->assign('embed', $embed);
        return $view->draw('_publish_embed');
    }

    public function ajaxEmbedChooseImage($id, $embedId)
    {
        $draft = $this->user->drafts()->find($id);

        if ($draft) {
            $embed = $draft->embeds()->find($embedId);

            if ($embed) {
                $view = $this->tpl();
                $view->assign('embed', $embed);
                Drawer::fill($view->draw('_publish_images'), true);
            }
        }
    }

    public function ajaxHttpSetImageNumber($id, $embedId, $imageNumber)
    {
        $draft = $this->user->drafts()->find($id);

        if ($draft) {
            $embed = $draft->embeds()->find($embedId);

            if ($embed) {
                $embed->imagenumber = $imageNumber;
                $embed->save();
                $this->rpc('MovimTpl.remove', '#'.$embed->HTMLId);
                $this->rpc('MovimTpl.append', '#publishembeds', $this->prepareEmbed($embed));
            }
        }

        $this->rpc('Drawer.clear');
    }

    public function display()
    {
        $microblog = 'urn:xmpp:microblog:0';

        $server = $this->get('s') ?? $this->user->id;
        $node = $this->get('n') ?? $microblog;
        $nodeId = $this->get('i') ?? '';
        $replyServer = $this->get('rs');
        $replyNode = $this->get('rn');
        $replyNodeId = $this->get('ri');

        if ($node == $microblog) {
            $this->view->assign('icon', App\Contact::firstOrNew(['id' => $server]));
        } else {
            $info = \App\Info::where('server', $server)
                             ->where('node', $node)
                             ->first();
            $this->view->assign('icon', $info);
        }

        $draft = $this->user->drafts()
                            ->where('server', $server)
                            ->where('node', $node)
                            ->where('nodeid', $nodeId)
                            ->first();

        if (!$draft) {
            $draft = new Draft;
            $draft->user_id = $this->user->id;
            $draft->server = $server;
            $draft->node = $node;
            $draft->nodeid = $nodeId;

            // If we find an existing post let's fill the draft
            $draft->tryFillPost();
        }

        // Reply
        $reply = null;

        if ($replyServer && $replyNode && $replyNodeId) {
            $reply = Post::where('server', $replyServer)
                            ->where('node', $replyNode)
                            ->where('nodeid', $replyNodeId)
                            ->first();
        } elseif ($draft->reply_id) {
            $reply = Post::find($draft->reply_id);
        }

        if ($reply) {
            $draft->reply_id = $reply->id;
        } else {
            $draft->reply_id = null;
        }

        $draft->save();
        $draft->refresh();

        if ($draft->reply) {
            $this->view->assign('replyblock', (new \Post)->prepareTicket($draft->reply));
        }

        $this->view->assign('draft', $draft);
    }
}
