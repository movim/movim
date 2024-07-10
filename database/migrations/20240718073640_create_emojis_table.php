<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmojisTable extends Migration
{
    public function up()
    {
        $this->schema->create('emojis_packs', function (Blueprint $table) {
            $table->string('name');
            $table->string('homepage')->nullable();
            $table->string('license')->nullable();
            $table->string('description')->nullable();

            $table->timestamps();

            $table->unique('name');
        });

        $this->schema->create('emojis', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pack');
            $table->string('name');
            $table->string('filename');
            $table->string('cache_hash');
            $table->string('cache_hash_algorythm');
            $table->string('alias')->nullable();

            $table->timestamps();

            $table->foreign('pack')->references('name')
                ->on('emojis_packs')->onDelete('cascade');

            $table->index(['cache_hash', 'cache_hash_algorythm']);
            $table->unique(['pack', 'name'], 'emojis_pack_name_file_unique');
        });

        $this->schema->create('emoji_user', function (Blueprint $table) {
            $table->integer('emoji_id')->unsigned();
            $table->string('user_id');
            $table->string('alias');

            $table->foreign('emoji_id')->references('id')
                  ->on('emojis')->onDelete('cascade');
            $table->foreign('user_id')->references('id')
                  ->on('users')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['emoji_id', 'user_id']);
            $table->unique(['user_id', 'alias']);
        });
    }

    public function down()
    {
        $this->schema->drop('emoji_user');
        $this->schema->drop('emojis');
        $this->schema->drop('emojis_packs');
    }
}
