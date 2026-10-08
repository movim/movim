
<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class CreateBookmarksDirectoriesTable extends Migration
{
    public function up()
    {
        $this->schema->create('bookmarks_directories', function (Blueprint $table) {
            $table->string('session_id', 64);
            $table->uuid('id');
            $table->string('server', 64);
            $table->string('node', 256);
            $table->string('title');
            $table->integer('order');
            $table->timestamps();

            $table->primary(['session_id', 'id']);

            $table->foreign('session_id')
                ->references('id')->on('sessions')
                ->onDelete('cascade');
        });

        $this->schema->table('conferences', function (Blueprint $table) {
            $table->float('weight')->nullable();
            $table->uuid('directory_id')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->dropColumn('weight');
            $table->dropColumn('directory_id');
        });

        $this->schema->drop('bookmarks_directories');
    }
}
