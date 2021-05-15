<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBundlesTable extends Migration
{
    public function up()
    {
        $this->schema->create('bundles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id', 64);
            $table->integer('bundle_id');
            $table->string('jid', 128);

            $table->integer('signedprekeyid');
            $table->text('signedprekeypublic');
            $table->text('signedprekeysignature');

            $table->text('identitykey');
            $table->text('prekeys');
            $table->timestamps();

            $table->unique(['user_id', 'bundle_id', 'jid']);
            $table->index('user_id');
        });

        $this->schema->create('bundle_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bundle_id')->unsigned();
            $table->string('device_id', 64);

            $table->foreign('bundle_id')
                  ->references('id')->on('bundles')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('bundle_sessions');
        $this->schema->drop('bundles');
    }
}
