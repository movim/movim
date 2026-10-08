<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddDispositionToMessageFilesTable extends Migration
{
    public function up()
    {
        $this->schema->table('message_files', function (Blueprint $table) {
            $table->string('disposition', 16)->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('message_files', function (Blueprint $table) {
            $table->dropColumn('disposition');
        });
    }
}
