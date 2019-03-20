<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAvatarHashInfosTable extends Migration
{
    public function up()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->string('avatarhash', 128)->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropColumn('avatarhash');
        });
    }
}
