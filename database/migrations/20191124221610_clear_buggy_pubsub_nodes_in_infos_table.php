<?php

use App\Info;
use Movim\Migration;

class ClearBuggyPubsubNodesInInfosTable extends Migration
{
    public function up()
    {
        Info::where('node', '0')->delete();
    }

    public function down() {}
}
