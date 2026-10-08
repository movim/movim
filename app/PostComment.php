<?php

namespace App;

class PostComment extends Post {
    protected $table = 'posts';
    public $with = ['contact'];
    public $withCount = [];
}