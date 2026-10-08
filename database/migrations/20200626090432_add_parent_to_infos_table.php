<?php

use App\Info;
use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddParentToInfosTable extends Migration
{
    public function up()
    {
        Info::query()->delete();

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
