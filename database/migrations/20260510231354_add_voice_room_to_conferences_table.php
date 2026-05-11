<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddVoiceRoomToConferencesTable extends Migration
{
    public function up()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->boolean('voice_room')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->dropColumn('voice_room');
        });
    }
}
