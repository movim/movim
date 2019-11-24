<?php

use Movim\Migration;
use App\Info;

class ClearBuggyPubsubNodesInInfosTable extends Migration
{
    public function up()
    {
        Info::where('node', '0')->delete();
    }

    public function down()
    {

    }
}
