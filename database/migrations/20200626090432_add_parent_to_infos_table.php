<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Info;

class AddParentToInfosTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();
        Info::truncate();
        $this->enableForeignKeyCheck();

        $this->schema->table('infos', function (Blueprint $table) {
            $table->string('parent')->nullable();
            $table->index('parent');
        });

    }

    public function down()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropColumn('parent');
        });
    }
}
