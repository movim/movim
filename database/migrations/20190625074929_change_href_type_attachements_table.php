<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeHrefTypeAttachementsTable extends Migration
{
    public function up()
    {
        /**
         * This migration will only works on PosgreSQL, MySQL doesn't suppport indexes on TEXT
         * see https://techjourney.net/mysql-error-1170-42000-blobtext-column-used-in-key-specification-without-a-key-length/
         */
        if ($this->schema->getConnection()->getDriverName() == 'pgsql') {
            $this->schema->table('attachments', function (Blueprint $table) {
                $table->text('href')->change();
            });
        }
    }

    public function down()
    {
        if ($this->schema->getConnection()->getDriverName() == 'pgsql') {
            $this->schema->table('attachments', function (Blueprint $table) {
                $table->string('href', 512)->change();
            });
        }
    }
}
