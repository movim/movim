<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

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
