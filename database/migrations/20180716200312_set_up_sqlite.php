<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class SetUpSqlite extends Migration
{
    public function up()
    {
        // ensure SQLite databases use write-ahead log mode to improve concurrency
        if ($this->schema->getConnection()->getDriverName() == 'sqlite') {
            $this->schema->getConnection()->unprepared('PRAGMA journal_mode = wal');
        }
    }

    public function down()
    {
        if ($this->schema->getConnection()->getDriverName() == 'sqlite') {
            $this->schema->getConnection()->unprepared('PRAGMA journal_mode = delete');
        }
    }
}
