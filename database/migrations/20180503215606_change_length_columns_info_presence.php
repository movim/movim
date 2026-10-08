<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeLengthColumnsInfoPresence extends Migration
{
    public function up()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->string('node', 256)->change();
        });

        $this->schema->table('presences', function (Blueprint $table) {
            $table->string('resource', 256)->change();
            $table->text('status')->change();
        });
    }

    public function down()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->string('node', 96)->change();
        });

        $this->schema->table('presences', function (Blueprint $table) {
            $table->string('resource', 128)->change();
            $table->string('status', 255)->change();
        });
    }
}
