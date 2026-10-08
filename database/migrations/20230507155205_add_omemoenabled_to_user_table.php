<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

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
