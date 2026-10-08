<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class ChangeUrlsToTextUploadTable extends Migration
{
    public function up()
    {
        $this->schema->table('upload', function (Blueprint $table) {
            $table->text('geturl')->nullable()->change();
            $table->text('puturl')->nullable()->change();
        });
    }

    public function down()
    {
        $this->schema->table('upload', function (Blueprint $table) {
            $table->string('geturl')->nullable()->change();
            $table->string('puturl')->nullable()->change();
        });
    }
}
