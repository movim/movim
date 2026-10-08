<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

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
