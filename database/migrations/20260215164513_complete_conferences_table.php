<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CompleteConferencesTable extends Migration
{
    public function up()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->string('space_server', 256)->nullable();
            $table->string('space_node', 256)->nullable();

            $table->index(['space_server', 'space_node']);
        });
    }

    public function down()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->dropIndex('conferences_space_server_space_node_index');

            $table->dropColumn('space_server');
            $table->dropColumn('space_node');
        });
    }
}
