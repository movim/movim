<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAvatarTypeToContactsTable extends Migration
{
    public function up()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('avatartype', 128)->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->dropColumn('avatartype');
        });
    }
}
