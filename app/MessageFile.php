<?php

namespace App;

use Movim\Model;
use Respect\Validation\Validator;

class MessageFile extends Model
{
    protected $appends = ['preview', 'cleansize'];

    public function message()
    {
        return $this->belongsTo('App\Message');
    }

    public function getCleansizeAttribute(): ?string
    {
        if ($this->size != null) {
            return humanSize($this->size);
        }

        return null;
    }

    public function getPreviewAttribute(): ?array
    {
        if (typeIsPicture($this->type)
        ) {
            return [
                'thumb' => protectPicture($this->url),
                'url' => $this->url,
                'picture' => true,
                'thumbnail_type' => $this->thumbnail_type,
                'thumbnail_url' => $this->thumbnail_url,
            ];
        }

        $url = parse_url($this->url);

        if (\array_key_exists('host', $url)) {
            $this->host = $url['host'];

            switch ($url['host']) {
                case 'i.imgur.com':
                    $thumb = getImgurThumbnail($this->url);

                    if ($thumb) {
                        return [
                            'url' => $this->url,
                            'thumb' => $thumb,
                            'picture' => true
                        ];
                    }
                    break;
            }
        }

        return null;
    }

    public function getIsPictureAttribute(): bool
    {
        return typeIsPicture($this->type);
    }

    public function getIsAudioAttribute(): bool
    {
        return typeIsAudio($this->type);
    }

    public function getIsVideoAttribute(): bool
    {
        return typeIsVideo($this->type);
    }

    public function import($file): bool
    {
        $upload = Upload::find($file->id);

        if ($upload && $upload->uploaded && isMimeType($upload->type)) {
            $this->name = (string)$upload->name;

            if (isset($upload->size)) {
                $this->size = (int)$upload->size;
            }

            $this->type = (string)$upload->type;
            $this->url = $upload->geturl;

            if (isset($file->thumbnail)
            && Validator::url()->isValid($file->thumbnail->uri)) {
                $this->thumbnail_type = (string)$file->thumbnail->type;
                $this->thumbnail_width = (int)$file->thumbnail->width;
                $this->thumbnail_height = (int)$file->thumbnail->height;
                $this->thumbnail_url = (string)$file->thumbnail->uri;
            }

            if (isset($file->thumbhash)) {
                $this->thumbnail_type = 'image/thumbhash';
                $this->thumbnail_url = (string)$file->thumbhash;
                $this->thumbnail_width = (int)$file->thumbhashWidth;
                $this->thumbnail_height = (int)$file->thumbhashHeight;
            }

            return true;
        }

        return false;
    }
}
