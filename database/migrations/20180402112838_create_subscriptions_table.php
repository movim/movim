<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubscriptionsTable extends Migration
{
    public function up()
    {
        $this->schema->create('subscriptions', function (Blueprint $table) {
            $table->string('jid', 64);
            $table->string('server', 64);
            $table->string('node', 192);
            $table->string('subid', 128)->nullable();
            $table->string('title', 128)->nullable();
            $table->boolean('public')->default(false);

            $table->timestamps();

            $table->primary(['jid', 'server', 'node']);
        });
    }

    public function down()
    {
        $this->schema->drop('subscriptions');
    }
}
