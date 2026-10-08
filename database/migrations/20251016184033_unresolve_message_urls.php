<?php

use App\Message;
use Movim\Migration;

class UnresolveMessageUrls extends Migration
{
    public function up()
    {
        Message::whereNull('urlid')->update(['resolved' => false]);
    }

    public function down()
    {
    }
}
