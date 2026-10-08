<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class DropPresencesPrimaryKeyForHashColumn extends Migration
{
    /**
     * MySQL (3072 key length limit) so lets create a hash of the columns to hack a unicity between them
     */
    public function up()
    {
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
            $this->disableForeignKeyCheck();

            DB::table('presences')->delete();

            $this->schema->table('presences', function (Blueprint $table) {
                $table->string('mucjid')->nullable(false)->change();
                $table->string('hash')->charset('binary')->storedAs("sha2(concat_ws('|', session_id, jid, mucjid, resource), 256)")->unique();
            });

            $this->enableForeignKeyCheck();
        };
    }

    public function down()
    {
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
            $this->schema->table('presences', function (Blueprint $table) {
                $table->string('mucjid')->nullable(true)->change();
                $table->dropColumn('hash');
            });
        }
    }
}
