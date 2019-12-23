<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddChatOnlyToConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->boolean('chatonly')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('chatonly');
        });
    }
}
