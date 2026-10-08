<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePushSubscriptionsTable extends Migration
{
    public function up()
    {
        $this->schema->create('push_subscriptions', function (Blueprint $table) {
            $table->increments('id');

            $table->text('endpoint');
            $table->string('p256dh', 128);
            $table->string('auth', 128);

            $table->string('browser', 32)->nullable();
            $table->string('platform', 32)->nullable();

            $table->string('user_id', 256);
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->timestamps();
        });

        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('firebaseauthorizationkey');
        });
    }

    public function down()
    {
        $this->schema->drop('push_subscriptions');

        $this->schema->table('configuration', function (Blueprint $table) {
            $table->string('firebaseauthorizationkey')->nullable();
        });
    }
}
