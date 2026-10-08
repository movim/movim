<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class AddHashToMessageFilesTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('message_files', function (Blueprint $table) {
            $table->dropUnique('message_files_message_mid_url_size_unique');
            $table->text('url')->change();

            switch ($this->schema->getConnection()->getDriverName()) {
                case 'mysql':
                    $table->string('hash')->charset('binary')->storedAs('sha2(`url`, 256)');
                    break;

                case 'pgsql':
                    DB::statement('create extension if not exists pgcrypto');
                    $table->string('hash')->storedAs("encode(digest(url, 'sha256'), 'hex')");
                    break;
            }

            $table->unique(['message_mid', 'hash']);
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('message_files', function (Blueprint $table) {
            $table->dropUnique('message_files_message_mid_hash_unique');
            $table->dropColumn('hash');
        });

        $this->schema->table('message_files', function (Blueprint $table) {
            $table->string('url', 256)->change();
            $table->unique(['message_mid', 'url', 'size']);
        });

        $this->enableForeignKeyCheck();
    }
}
