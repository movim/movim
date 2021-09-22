<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddBundleIdToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->integer('bundleid')->nullable();
            $table->index('bundleid');
        });

        $this->schema->table('bundles', function (Blueprint $table) {
            $table->renameColumn('bundle_id', 'bundleid');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('bundleid');
        });

        $this->schema->table('bundles', function (Blueprint $table) {
            $table->renameColumn('bundleid', 'bundle_id');
        });
    }
}
