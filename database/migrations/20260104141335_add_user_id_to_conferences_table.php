<?php

use App\Conference;
use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserIdToConferencesTable extends Migration
{
    public function up()
    {
        Conference::query()->delete();

        $this->schema->table('conferences', function (Blueprint $table) {
            $table->string('user_id', 256);
            $table->foreign('user_id')->references('id')
                  ->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('conferences', function (Blueprint $table) {
            $table->dropForeign('conferences_user_id_foreign');
            $table->dropColumn('user_id');
        });

        $this->enableForeignKeyCheck();
    }
}
