<?php

namespace App;

use Respect\Validation\Validator;
use stdClass;

class MessageFile {
    public $name;
    public $size;
    public $type;
    public $uri;

    public $thumbnail;

    public $valid = false;

    public function __construct()
    {
        $this->thumbnail = new stdClass;
    }

    public function import($file)
    {
        if (Validator::url()->validate($file->uri)) {
            $this->name = (string)$file->name;
            $this->size = (int)$file->size;
            $this->type = (string)$file->type;
            $this->uri = $file->uri;

            if (isset($file->thumbnail)
            && Validator::url()->validate($file->thumbnail->uri)) {
                $this->thumbnail->type = (string)$file->thumbnail->type;
                $this->thumbnail->width = (int)$file->thumbnail->width;
                $this->thumbnail->height = (int)$file->thumbnail->height;
                $this->thumbnail->uri = (string)$file->thumbnail->uri;
            }

            $this->valid = true;
        }
    }
}