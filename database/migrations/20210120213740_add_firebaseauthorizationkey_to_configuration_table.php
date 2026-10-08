<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddFirebaseauthorizationkeyToConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->string('firebaseauthorizationkey')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('firebaseauthorizationkey');
        });
    }
}
