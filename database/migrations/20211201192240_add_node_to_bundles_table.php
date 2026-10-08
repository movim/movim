<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

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
