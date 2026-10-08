<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class ChangeMessageFilesNameLength extends Migration
{
    public function up()
    {
        $this->schema->table('message_files', function (Blueprint $table) {
            $table->text('name')->change();
        });
    }

    public function down()
    {
        $this->schema->table('message_files', function (Blueprint $table) {
            $table->string('name', 255)->change();
        });
    }
}
