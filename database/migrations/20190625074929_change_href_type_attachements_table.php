<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeHrefTypeAttachementsTable extends Migration
{
    public function up()
    {
        $this->schema->table('attachments', function (Blueprint $table) {
            $table->text('href')->change();
        });
    }

    public function down()
    {
        $this->schema->table('attachments', function (Blueprint $table) {
            $table->string('href', 512)->change();
        });
    }
}
