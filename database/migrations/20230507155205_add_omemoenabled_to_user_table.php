<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddOmemoenabledToUserTable extends Migration
{
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->boolean('omemoenabled')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('omemoenabled');
        });
    }
}
