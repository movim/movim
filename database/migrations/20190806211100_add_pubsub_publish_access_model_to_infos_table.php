<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPubsubPublishAccessModelToInfosTable extends Migration
{
    public function up()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->string('pubsubaccessmodel', 16)->nullable();
            $table->string('pubsubpublishmodel', 16)->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropColumn('pubsubaccessmodel');
            $table->dropColumn('pubsubpublishmodel');
        });
    }
}
