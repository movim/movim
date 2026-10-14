<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class CreateVersionsTable extends Migration
{
    public function up()
    {
        $this->schema->create('versions', function (Blueprint $table) {
            $table->string('id', 256)->unique();
            $table->string('name');
            $table->string('version');
            $table->string('os')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        $this->schema->drop('versions');
    }
}
