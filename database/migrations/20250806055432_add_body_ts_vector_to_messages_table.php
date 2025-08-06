<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class AddBodyTsVectorToMessagesTable extends Migration
{
    public function up()
    {
        if ($this->schema->getConnection()->getDriverName() == 'pgsql') {
            $this->schema->table('messages', function (Blueprint $table) {
                DB::statement("create index messages_body_gin_index on messages using gin (to_tsvector('simple', body));");
            });
        }
    }

    public function down()
    {
        if ($this->schema->getConnection()->getDriverName() == 'pgsql') {
            $this->schema->table('messages', function (Blueprint $table) {
                $table->dropIndex('messages_body_gin_index');
            });
        }
    }
}
