<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropBundleSessionsTable extends Migration
{
    public function up()
    {
        $this->schema->drop('bundle_sessions');
    }

    public function down()
    {
        $this->schema->create('bundle_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bundle_id')->unsigned();
            $table->string('deviceid', 64);

            $table->foreign('bundle_id')
                  ->references('id')->on('bundles')
                  ->onDelete('cascade');

            $table->unique(['bundle_id', 'deviceid']);

            $table->timestamps();
        });
    }
}
