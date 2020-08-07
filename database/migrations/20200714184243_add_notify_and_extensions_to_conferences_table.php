<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Info;

class AddNotifyAndExtensionsToConferencesTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();
        Info::truncate();
        $this->enableForeignKeyCheck();

        $this->schema->table('conferences', function (Blueprint $table) {
            $table->text('extensions')->nullable();
            $table->integer('bookmarkversion')->default(0);
            $table->integer('notify')->default(1);
        });
    }

    public function down()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->dropColumn('extensions');
            $table->dropColumn('bookmarkversion');
            $table->dropColumn('notify');
        });
    }
}
