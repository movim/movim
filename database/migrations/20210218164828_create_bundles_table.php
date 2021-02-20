<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBundlesTable extends Migration
{
    public function up()
    {
        $this->schema->create('bundles', function (Blueprint $table) {
            $table->string('user_id', 64);
            $table->integer('bundle_id');
            $table->string('jid', 128);

            $table->text('prekeypublic');
            $table->text('prekeysignature');
            $table->text('identitykey');
            $table->text('prekeys');
            $table->timestamps();

            $table->primary(['user_id', 'bundle_id', 'jid']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        $this->schema->drop('bundles');
    }
}
