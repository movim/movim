<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddIndexJidToRostersTable extends Migration
{
    public function up()
    {
        $this->schema->table('rosters', function (Blueprint $table) {
            $table->index('jid');
        });
    }

    public function down()
    {
        $this->schema->table('rosters', function (Blueprint $table) {
            $table->dropIndex('rosters_jid_index');
        });
    }
}
