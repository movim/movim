<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class ChangePresenceMucJidResourceLength extends Migration
{
    public function up()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->text('mucjidresource')->nullable()->change();
        });
    }

    public function down()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->string('mucjidresource', 255)->nullable()->change();
        });
    }
}
