<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

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
