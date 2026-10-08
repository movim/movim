<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->create('configuration', function (Blueprint $table) {
            $table->increments('id');
            $table->text('description')->nullable();
            $table->text('info')->nullable();
            $table->boolean('unregister');
            $table->boolean('restrictsuggestions');
            $table->string('theme');
            $table->string('locale');
            $table->string('loglevel');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('xmppdomain')->nullable();
            $table->string('xmppdescription')->nullable();
            $table->string('xmppcountry')->nullable();
            $table->string('xmppwhitelist')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('configuration');
    }
}
