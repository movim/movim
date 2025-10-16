<?php

use App\Url;
use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class CompleteUrlsTable extends Migration
{
    public function up()
    {
        DB::statement('delete from urls');

        $this->schema->table('urls', function (Blueprint $table) {
            $table->string('type', 8)->nullable();
            $table->text('url');

            $table->integer('content_length')->default(0);
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->string('content_type')->nullable();
            $table->text('image')->nullable();
            $table->text('serialized_tags')->nullable();
            $table->text('serialized_images')->nullable();

            $table->string('author_name')->nullable();
            $table->text('author_url')->nullable();
            $table->text('provider_icon')->nullable();
            $table->string('provider_name')->nullable();
            $table->text('provider_url')->nullable();

            $table->dateTime('published_at')->nullable();
            $table->text('serialized_tags')->change();
            $table->text('serialized_images')->change();

            $table->dropColumn('cache');
        });
    }

    public function down()
    {
        DB::statement('delete from urls');

        $this->schema->table('urls', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('description');
            $table->dropColumn('image');
            $table->dropColumn('url');
            $table->dropColumn('type');
            $table->dropColumn('content_type');
            $table->dropColumn('content_length');
            $table->dropColumn('serialized_tags');
            $table->dropColumn('serialized_images');
            $table->dropColumn('author_name');
            $table->dropColumn('author_url');

            $table->dropColumn('provider_icon');
            $table->dropColumn('provider_name');
            $table->dropColumn('provider_url');

            $table->dropColumn('published_at');

            $table->text('cache')->nullable();
        });
    }
}
