<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ReconciliateNullableInTables extends Migration
{
    public function up()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->text('status')->nullable()->change();
        });

        $this->schema->table('rosters', function (Blueprint $table) {
            $table->string('name', 256)->nullable()->change();
        });

        $this->schema->table('conferences', function (Blueprint $table) {
            $table->string('nick', 256)->nullable()->change();
        });

        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('nickname', 256)->nullable()->change();
            $table->string('fn', 256)->nullable()->change();
        });
    }

    public function down()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->text('status')->nullable(false)->change();
        });

        $this->schema->table('rosters', function (Blueprint $table) {
            $table->string('name', 256)->nullable(false)->change();
        });

        $this->schema->table('conferences', function (Blueprint $table) {
            $table->string('nick', 256)->nullable(false)->change();
        });

        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('nickname', 256)->change();
            $table->string('fn', 256)->change();
        });
    }
}
