<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAvatarRequestedToContactsTable extends Migration
{
    public function up()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->boolean('avatarrequested')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->dropColumn('avatarrequested');
        });
    }
}
