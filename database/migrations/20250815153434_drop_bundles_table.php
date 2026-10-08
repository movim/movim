<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropBundlesTable extends Migration
{
    public function up()
    {
        $this->schema->drop('bundles');
    }

    public function down()
    {
        $this->schema->create('bundles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id', 64);
            $table->integer('bundleid');
            $table->string('jid', 128);

            $table->integer('signedprekeyid');
            $table->text('signedprekeypublic');
            $table->text('signedprekeysignature');

            $table->text('identitykey');
            $table->text('prekeys');

            $table->string('node')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'bundleid', 'jid']);
            $table->index('user_id');
        });
    }
}
