<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class CreateIdentitiesTable extends Migration
{
    public function up()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropPrimary('infos_pkey');
        });

        DB::table('infos')->truncate();

        $this->schema->table('infos', function (Blueprint $table) {
            $table->increments('id');
            $table->dropColumn('category');
            $table->dropColumn('type');
            $table->unique(['server', 'node']);
            $table->text('features')->nullable();
            $table->string('server')->nullable()->change();
        });

        $this->schema->create('identities', function (Blueprint $table) {
            $table->integer('info_id')->unsigned();
            $table->string('category');
            $table->string('type');
            $table->timestamps();

            $table->foreign('info_id')->references('id')
                  ->on('infos')->onDelete('cascade');

            $table->index('category');
            $table->index('type');

            $table->primary(['info_id', 'category', 'type']);
        });

        $this->schema->drop('capabilities');
    }

    public function down()
    {
        $this->schema->drop('identities');

        DB::table('infos')->truncate();

        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropColumn('features');
            $table->dropUnique('infos_server_node_unique');
            $table->string('category', 16);
            $table->string('type', 16)->nullable();
            $table->primary(['server', 'node']);
            $table->string('server')->nullable(false)->change();
        });

        $this->schema->create('capabilities', function (Blueprint $table) {
            $table->string('node', 256);
            $table->string('category', 16);
            $table->string('type', 16);
            $table->string('name', 128);
            $table->text('features');
            $table->timestamps();

            $table->primary('node');
            $table->index('category');
            $table->index('type');
        });
    }
}
