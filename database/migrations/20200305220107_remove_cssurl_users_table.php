<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveCssurlUsersTable extends Migration
{
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('cssurl');
        });
    }

    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->string('cssurl', 256)->nullable();
        });
    }
}
