<?php

use App\Message;
use App\MessageFile;
use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessageFilesTable extends Migration
{
    public function up()
    {
        $this->schema->create('message_files', function (Blueprint $table) {
            $table->integer('message_mid')->unsigned();

            $table->foreign('message_mid')
                  ->references('mid')->on('messages')
                  ->onDelete('cascade');

            $table->string('type');
            $table->string('name');
            $table->string('url');
            $table->text('desc')->nullable();
            $table->integer('width')->nullable();
            $table->integer('size')->nullable();
            $table->integer('height')->nullable();

            $table->string('thumbnail_url')->nullable();
            $table->integer('thumbnail_width')->nullable();
            $table->integer('thumbnail_height')->nullable();
            $table->string('thumbnail_type')->nullable();

            $table->timestamps();

            $table->unique(['message_mid', 'url', 'size']);
        });

        Message::whereNotNull('file')->update(['resolved' => false]);

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('file');
        });
    }

    public function down()
    {
        $this->schema->drop('message_files');

        $this->schema->table('messages', function (Blueprint $table) {
            $table->text('file')->nullable();
        });
    }
}
