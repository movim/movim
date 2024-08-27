<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeEncryptedPasswordsUserIdLength extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();
        $this->dropMySQLForeignKeys();

        $this->schema->table('encrypted_passwords', function (Blueprint $table) {
            $table->string('user_id', 256)->change();
        });

        $this->recreateMySQLForeignKeys();
        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();
        $this->dropMySQLForeignKeys();

        $this->schema->table('encrypted_passwords', function (Blueprint $table) {
            $table->string('user_id', 64)->change();
        });

        $this->recreateMySQLForeignKeys();
        $this->enableForeignKeyCheck();
    }

    private function dropMySQLForeignKeys()
    {
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
            $this->schema->table('encrypted_passwords', function (Blueprint $table) {
                $table->dropForeign('encrypted_passwords_user_id_foreign');
            });
        }
    }

    private function recreateMySQLForeignKeys()
    {
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
           $this->schema->table('encrypted_passwords', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users');
            });
        }
    }
}
