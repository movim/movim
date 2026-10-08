<?php

use Movim\Migration;

use App\Contact;
use App\Presence;
use App\Roster;
use App\Conference;

use Illuminate\Database\Schema\Blueprint;

/**
 * Required after the behavior change introduced in
 * https://laravel.com/docs/11.x/upgrade#modifying-columns
 */
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
        Presence::whereNull('status')->delete();

        $this->schema->table('presences', function (Blueprint $table) {
            $table->text('status')->nullable(false)->change();
        });

        Roster::whereNull('name')->delete();

        $this->schema->table('rosters', function (Blueprint $table) {
            $table->string('name', 256)->nullable(false)->change();
        });

        Conference::whereNull('nick')->delete();

        $this->schema->table('conferences', function (Blueprint $table) {
            $table->string('nick', 256)->nullable(false)->change();
        });

        Contact::whereNull('nickname')->delete();
        Contact::whereNull('fn')->delete();

        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('nickname', 256)->nullable(false)->change();
            $table->string('fn', 256)->nullable(false)->change();
        });
    }
}
