<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddRelatedToInfos extends Migration
{
    public function up()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->string('related', 512)->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropColumn('related');
        });
    }
}
