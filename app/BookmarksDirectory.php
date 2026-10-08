<?php

namespace App;

use Awobaz\Compoships\Database\Eloquent\Model;

class BookmarksDirectory extends Model
{
    protected $table = 'bookmarks_directories';
    public $incrementing = false;

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function conferences()
    {
        return $this->hasMany(Conference::class, ['space_server', 'space_node', 'directory_id'], ['server', 'node', 'id']);
    }
}
