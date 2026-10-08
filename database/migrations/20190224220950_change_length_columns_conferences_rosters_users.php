<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeLengthColumnsConferencesRostersUsers extends Migration
{
    public function up()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->string('name', 512)->change();
            $table->string('nick', 256)->change();
        });

        $this->schema->table('rosters', function (Blueprint $table) {
            $table->string('name', 256)->change();
        });

        $this->schema->table('users', function (Blueprint $table) {
            $table->string('cssurl', 256)->change();
        });
    }

    public function down()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->string('name', 128)->change();
            $table->string('nick', 128)->change();
        });

        $this->schema->table('rosters', function (Blueprint $table) {
            $table->string('name', 128)->change();
        });

        $this->schema->table('users', function (Blueprint $table) {
            $table->string('cssurl', 128)->change();
        });
    }
}
