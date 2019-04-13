<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReactionsTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropPrimary('messages_pkey');
        });

        // Let's just recreate the full table for SQLite…
        if ($this->schema->getConnection()->getDriverName() == 'sqlite') {
            $this->schema->create('messages_tmp', function (Blueprint $table) {
                $table->string('user_id', 256);
                $table->string('id', 255);
                $table->string('oldid', 255)->nullable();
                $table->string('type', 255);
                $table->text('subject')->nullable();
                $table->string('thread', 255)->nullable();
                $table->text('body')->nullable();
                $table->text('html')->nullable();
                $table->datetime('published');
                $table->datetime('delivered')->nullable();
                $table->datetime('displayed')->nullable();
                $table->boolean('quoted')->default(false);
                $table->boolean('markable')->default(false);
                $table->text('picture')->nullable();
                $table->string('sticker', 255)->nullable();
                $table->text('file')->nullable();
                $table->timestamps();
                $table->string('jidto', 256);
                $table->string('jidfrom', 256);
                $table->string('resource', 256)->nullable();
                $table->string('replaceid', 255)->nullable();
                $table->increments('mid');
            });

            $this->schema->getConnection()->unprepared('
                insert into messages_tmp(
                        user_id, id, oldid, type, subject, thread, body, html, published,
                        delivered, displayed, quoted, markable, picture, sticker, file,
                        created_at, updated_at, jidto, jidfrom, resource, replaceid)
                select user_id, id, oldid, type, subject, thread, body, html, published,
                        delivered, displayed, quoted, markable, picture, sticker, file,
                        created_at, updated_at, jidto, jidfrom, resource, replaceid
                from messages');

            $this->schema->drop('messages');
            $this->schema->rename('messages_tmp', 'messages');

            $this->schema->table('messages', function (Blueprint $table) {
                $table->unique(['user_id', 'jidfrom', 'id']);

                $table->index('jidto');
                $table->index('jidfrom');
                $table->index('published');
                $table->index('replaceid');
                $table->index('type');

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            });
        } else {
            $this->schema->table('messages', function (Blueprint $table) {
                $table->increments('mid');
                $table->unique(['user_id', 'jidfrom', 'id']);
            });
        }

        $this->schema->create('reactions', function (Blueprint $table) {
            $table->integer('message_mid')->unsigned();
            $table->string('jidfrom', 256);
            $table->string('emoji', 1);
            $table->timestamps();

            $table->foreign('message_mid')->references('mid')
            ->on('messages')->onDelete('cascade');

            $table->unique(['message_mid', 'jidfrom', 'emoji']);
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->drop('reactions');

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropPrimary('messages_pkey');
            $table->dropColumn('mid');
            $table->primary(['user_id', 'jidfrom', 'id']);
            $table->dropUnique('messages_user_id_jidfrom_id_unique');
        });

        $this->enableForeignKeyCheck();
    }
}
