<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class SetCaseSensitiveResourceTablePresences extends Migration
{
    public function up()
    {
        /**
         * MySQL is no case sensitive by default, we need to enforce it for the resource
         * column to prevent primary key issues during mass insertion
         */
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
            $this->schema->table('presences', function (Blueprint $table) {
                $table->string('resource')->collation('utf8mb4_bin')->change();
            });
        }
    }

    public function down()
    {
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
            $this->schema->table('presences', function (Blueprint $table) {
                $table->string('resource')->collation('utf8mb4_unicode_ci')->change();
            });
        }
    }
}
