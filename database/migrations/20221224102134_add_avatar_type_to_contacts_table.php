<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

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
