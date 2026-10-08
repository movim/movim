<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class CreateCapabilitiesTable extends Migration
{
    public function up()
    {
        $this->schema->create('capabilities', function (Blueprint $table) {
            $table->string('node', 256);
            $table->string('category', 16);
            $table->string('type', 16);
            $table->string('name', 128);
            $table->text('features');
            $table->timestamps();

            $table->primary('node');
        });
    }

    public function down()
    {
        $this->schema->drop('capabilities');
    }
}
