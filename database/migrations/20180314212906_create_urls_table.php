<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

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
