<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddCommentsDisabledToDrafsTable extends Migration
{
    public function up()
    {
        $this->schema->table('drafts', function (Blueprint $table) {
            $table->boolean('comments_disabled')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('drafts', function (Blueprint $table) {
            $table->dropColumn('comments_disabled');
        });
    }
}
