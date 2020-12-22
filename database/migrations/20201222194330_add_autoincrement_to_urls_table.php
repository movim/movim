<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAutoincrementToUrlsTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('urls', function (Blueprint $table) {
            $table->dropPrimary();
        });

        $this->schema->table('urls', function (Blueprint $table) {
            $table->increments('id')->first();
            $table->unique('hash');
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->integer('urlid')->unsigned()->nullable();

            $table->foreign('urlid')
                  ->references('id')->on('urls')
                  ->onDelete('set null');
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropForeign('messages_urlid_foreign');
            $table->dropColumn('urlid');
        });

        $this->schema->table('urls', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropUnique('urls_hash_unique');
            $table->primary('hash');
        });

        $this->enableForeignKeyCheck();
    }
}
