<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class CreateUrlsTable extends Migration
{
    public function up()
    {
        $this->schema->create('urls', function (Blueprint $table) {
            $table->string('hash');
            $table->text('cache');
            $table->primary('hash');
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('urls');
    }
}
