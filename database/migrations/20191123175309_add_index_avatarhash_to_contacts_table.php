<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndexAvatarhashToContactsTable extends Migration
{
    public function up()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->index('avatarhash');
        });
    }

    public function down()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_avatarhash_index');
        });
    }
}
