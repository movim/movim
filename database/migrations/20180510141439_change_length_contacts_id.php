<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeLengthContactsId extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('id', 256)->change();
            $table->string('nickname', 256)->change();
            $table->string('fn', 256)->change();
        });

        $this->schema->table('presences', function (Blueprint $table) {
            $table->string('jid', 256)->change();
        });

        $this->schema->table('rosters', function (Blueprint $table) {
            $table->string('jid', 256)->change();
        });

        $this->schema->table('subscriptions', function (Blueprint $table) {
            $table->string('jid', 256)->change();
        });

        $this->dropMySQLForeignKeys();

        $this->schema->table('users', function (Blueprint $table) {
            $table->string('id', 256)->change();
        });

        $this->schema->table('sessions', function (Blueprint $table) {
            $table->string('user_id', 256)->change();
            $table->string('username', 192)->change();
        });

        $this->schema->table('invites', function (Blueprint $table) {
            $table->string('user_id', 256)->change();
            $table->string('resource', 256)->change();
        });

        $this->schema->table('caches', function (Blueprint $table) {
            $table->string('user_id', 256)->change();
        });

        $this->schema->table('encrypted_passwords', function (Blueprint $table) {
            $table->string('user_id', 256)->change();
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('user_id', 256)->change();
            $table->string('jidto', 256)->change();
            $table->string('jidfrom', 256)->change();
            $table->string('resource', 256)->change();
        });

        $this->recreateMySQLForeignKeys();

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('id', 64)->change();
            $table->string('nickname', 64)->change();
            $table->string('fn', 64)->change();
        });

        $this->schema->table('presences', function (Blueprint $table) {
            $table->string('jid', 64)->change();
        });

        $this->schema->table('rosters', function (Blueprint $table) {
            $table->string('jid', 64)->change();
        });

        $this->schema->table('subscriptions', function (Blueprint $table) {
            $table->string('jid', 64)->change();
        });

        $this->dropMySQLForeignKeys();

        $this->schema->table('users', function (Blueprint $table) {
            $table->string('id', 64)->change();
        });

        $this->schema->table('sessions', function (Blueprint $table) {
            $table->string('user_id', 64)->change();
            $table->string('username', 64)->change();
        });

        $this->schema->table('invites', function (Blueprint $table) {
            $table->string('user_id', 64)->change();
            $table->string('resource', 128)->change();
        });

        $this->schema->table('caches', function (Blueprint $table) {
            $table->string('user_id', 64)->change();
        });

        $this->schema->table('encrypted_passwords', function (Blueprint $table) {
            $table->string('user_id', 64)->change();
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('user_id', 64)->change();
            $table->string('jidto', 96)->change();
            $table->string('jidfrom', 96)->change();
            $table->string('resource', 96)->change();
        });

        $this->recreateMySQLForeignKeys();

        $this->enableForeignKeyCheck();
    }

    // Need to drop the foreign key to alter the ids, and then recreate it

    private function dropMySQLForeignKeys()
    {
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
            $this->schema->table('sessions', function (Blueprint $table) {
                $table->dropForeign('sessions_user_id_foreign');
            });

            $this->schema->table('invites', function (Blueprint $table) {
                $table->dropForeign('invites_user_id_foreign');
            });

            $this->schema->table('caches', function (Blueprint $table) {
                $table->dropForeign('caches_user_id_foreign');
            });

            $this->schema->table('messages', function (Blueprint $table) {
                $table->dropForeign('messages_user_id_foreign');
            });

            $this->schema->table('encrypted_passwords', function (Blueprint $table) {
                $table->dropForeign('encrypted_passwords_user_id_foreign');
            });
        }
    }

    private function recreateMySQLForeignKeys()
    {
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
            $this->schema->table('sessions', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });

            $this->schema->table('invites', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });

            $this->schema->table('caches', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });

            $this->schema->table('messages', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });

            $this->schema->table('encrypted_passwords', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
}
