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

        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('user_id', 256)->change();
            $table->string('jidto', 256)->change();
            $table->string('jidfrom', 256)->change();
            $table->string('resource', 256)->change();
        });

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

        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('user_id', 64)->change();
            $table->string('jidto', 96)->change();
            $table->string('jidfrom', 96)->change();
            $table->string('resource', 96)->change();
        });

        $this->enableForeignKeyCheck();
    }
}
