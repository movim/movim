<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    protected $with = ['reply'];
    private $embeds = [];

    public function save(array $options = [])
    {
        parent::save($options);
        $this->embeds()->saveMany($this->embeds);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function embeds()
    {
        return $this->hasMany('App\DraftEmbed');
    }

    public function reply()
    {
        return $this->hasOne('App\Post', 'id', 'reply_id');
    }

    public function isNotEmpty()
    {
        return !empty($this->title);
    }

    public function tryFillPost()
    {
        $post = Post::where('server', $this->server)
                    ->where('node', $this->node)
                    ->where('nodeid', $this->nodeid)
                    ->first();

        if ($post) {
            $this->title = $post->title;
            $this->content = $post->contentraw;
            $this->open = $post->open;

            $reply = $post->getReply();
            if ($reply) {
                $this->reply_id = $reply->id;
            }

            foreach ($post->attachments()->whereIn('rel', ['enclosure', 'related'])->get() as $attachment) {
                $embed = new DraftEmbed;
                $embed->url = $attachment->href;
                array_push($this->embeds, $embed);
            }
        }
    }
}