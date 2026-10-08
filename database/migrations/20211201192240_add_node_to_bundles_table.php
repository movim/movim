<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNodeToBundlesTable extends Migration
{
    public function up()
    {
        $this->schema->table('bundles', function (Blueprint $table) {
            $table->string('node')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('bundles', function (Blueprint $table) {
            $table->dropColumn('node');
        });
    }
}
