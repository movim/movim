<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddVcardUpdateToPresence extends Migration
{
    public function up()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->string('avatarhash', 128)->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropColumn('avatarhash');
        });
    }
}
