<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddParentMidToMessagesTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('messages', function (Blueprint $table) {
            $table->integer('parentmid')->unsigned()->nullable();
            $table->dropColumn('parentthread');

            $table->foreign('parentmid')
                  ->references('mid')->on('messages')
                  ->onDelete('set null');
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropForeign('messages_parentmid_foreign');
            $table->dropColumn('parentmid');
            $table->string('parentthread', 128)->nullable();
        });

        $this->enableForeignKeyCheck();
    }
}
