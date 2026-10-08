<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

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
