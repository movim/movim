<?php

namespace App\Widgets\PublishStories;

use App\Post;
use App\Upload;
use App\Widgets\Toast\Toast;
use Movim\Widget\Base;
use Moxl\Xec\Action\Pubsub\GetItem;
use Moxl\Xec\Action\Pubsub\PostPublish;

class PublishStories extends Base
{
    public function load()
    {
        $this->registerEvent('pubsub_postpublish_handle', 'onPublish');

        $this->addjs('publishstories.js');
        $this->addcss('publishstories.css');
    }

    public function onPublish($packet)
    {
        Toast::send($this->__('story.published'));

        list($to, $node, $id, $repost, $comments) = array_values($packet->content);

        if ($node == Post::STORIES_NODE) {
            // If the  Story was not cached we force reload
            if (!Post::where('server', $to)->where('node', $node)->where('nodeid', $id)->exists()) {
                $gi = new GetItem;
                $gi->setTo($to)
                   ->setNode($node)
                   ->setId($id)
                   ->request();
            }

            $this->rpc('MovimUtils.reload', $this->route('chat'));
        }

        $this->rpc('PublishStories.close');
    }

    public function ajaxOpen()
    {
        $this->rpc('PublishStories.init');
    }

    public function ajaxNoTitle()
    {
        Toast::send($this->__('publish.no_title'));
    }

    public function ajaxPublish($form, string $uploadId)
    {
        if (empty($form->title->value)) {
            $this->ajaxNoTitle();
        }

        $upload = Upload::find($uploadId);

        if (!$upload) return;

        $publish = new PostPublish;
        $publish->setTo($this->me->id)
                ->setNode(Post::STORIES_NODE)
                ->setId(generateUUID())
                ->setFrom($this->me->id)
                ->setTitle($form->title->value)
                ->addImage($upload->geturl, 'story', 'image/jpeg')
                ->setTags(getHashtags(htmlspecialchars($form->title->value)))
                ->request();
    }

    public function display()
    {
        $this->view->assign(
            'rostercount',
            $this->me->session->contacts()->whereIn('subscription', ['both', 'from'])->count()
        );
    }
}
