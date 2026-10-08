<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddBannerHashToContactsTable extends Migration
{
    public function up()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('bannerhash', 128)->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->dropColumn('bannerhash');
        });
    }
}
