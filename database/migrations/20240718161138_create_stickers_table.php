<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStickersTable extends Migration
{
    public function up()
    {
        $this->schema->create('stickers_packs', function (Blueprint $table) {
            $table->string('name');
            $table->string('homepage')->nullable();
            $table->string('author')->nullable();
            $table->string('license')->nullable();

            $table->timestamps();

            $table->unique('name');
        });

        $this->schema->create('stickers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pack');
            $table->string('name');
            $table->string('filename');
            $table->string('cache_hash');
            $table->string('cache_hash_algorythm');

            $table->timestamps();

            $table->foreign('pack')->references('name')
                ->on('stickers_packs')->onDelete('cascade');

            $table->index(['cache_hash', 'cache_hash_algorythm']);
            $table->unique(['pack', 'name'], 'stickers_pack_name_file_unique');
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('sticker');
            $table->string('sticker_cid_hash', 256)->nullable();
            $table->string('sticker_cid_algorythm', 16)->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('sticker_cid_hash');
            $table->dropColumn('sticker_cid_algorythm');

            $table->string('sticker', 128)->nullable();
        });

        $this->schema->drop('stickers');
        $this->schema->drop('stickers_packs');
    }
}
