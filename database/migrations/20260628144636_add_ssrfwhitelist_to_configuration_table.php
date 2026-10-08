<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSsrfwhitelistToConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->text('ssrfwhitelist')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('ssrfwhitelist');
        });
    }
}
