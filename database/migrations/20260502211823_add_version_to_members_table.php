<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddVersionToMembersTable extends Migration
{
    public function up()
    {
        $this->schema->table('members', function (Blueprint $table) {
            $table->string('version')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('members', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
}
