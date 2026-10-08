<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMessageIdAndStanzaIdToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('messageid')->nullable();
            $table->index('messageid');
            $table->string('stanzaid')->nullable();
            $table->index('stanzaid');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_messageid_index');
            $table->dropColumn('messageid');
            $table->dropIndex('messages_stanzaid_index');
            $table->dropColumn('stanzaid');
        });
    }
}
