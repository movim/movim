<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAdminColumnToUsersTable extends Migration
{
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->boolean('admin')->default(false);
            $table->index('admin');
        });

        $this->schema->table('configuration', function (Blueprint $table) {
            $table->string('banner')->nullable();
            $table->dropColumn('username');
            $table->dropColumn('password');
        });
    }

    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('admin');
        });

        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('banner');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
        });
    }
}