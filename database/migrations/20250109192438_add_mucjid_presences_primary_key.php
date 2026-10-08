<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class AddMucjidPresencesPrimaryKey extends Migration
{
    /**
     * This migration is failing on MySQL (3072 key length limit)
     * We pass it and create a hash of the needed columns the next migration
     */

    public function up()
    {
        if ($this->schema->getConnection()->getDriverName() == 'pgsql') {
            DB::table('presences')->delete();

            $this->schema->table('presences', function (Blueprint $table) {
                $table->dropPrimary(['session_id', 'jid', 'resource']);
                $table->primary(['session_id', 'jid', 'mucjid', 'resource']);
            });
        }
    }

    public function down()
    {
        if ($this->schema->getConnection()->getDriverName() == 'pgsql') {
            DB::table('presences')->delete();

            $this->schema->table('presences', function (Blueprint $table) {
                $table->dropPrimary(['session_id', 'jid', 'mucjid', 'resource']);
                $table->primary(['session_id', 'jid', 'resource']);
            });
        }
    }
}
