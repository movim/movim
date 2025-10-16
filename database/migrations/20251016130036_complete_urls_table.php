<?php

use App\Url;
use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class CompleteUrlsTable extends Migration
{
    public function up()
    {
        $this->schema->table('urls', function (Blueprint $table) {
            $table->text('url')->nullable();
            $table->string('type', 8)->nullable();

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
        });

        foreach (Url::all() as $url) {
            if ($url->cache) {
                $cache = unserialize(base64_decode($url->cache));

                if (
                    $cache
                    && filter_var($cache->url, FILTER_VALIDATE_URL)
                    && in_array($cache->type, ['text', 'image', 'video'])
                ) {
                    $url->title = $cache->title != $cache->url ? $cache->title : null;
                    $url->url = $cache->url;
                    $url->type = $cache->type;
                    $url->description = $cache->description;
                    $url->content_type = $cache->contentType ?? null;

                    if (is_array($cache->tags)) {
                        $url->tags = $cache->tags;
                    }

                    if (is_array($cache->images)) {
                        $url->images = $cache->images;
                    }

                    $url->author_name = $cache->authorName;
                    $url->author_url = filter_var($cache->authorUrl, FILTER_VALIDATE_URL)
                        ? $cache->authorUrl
                        : null;
                    $url->provider_icon = filter_var($cache->providerIcon, FILTER_VALIDATE_URL)
                        ? $cache->providerIcon
                        : null;
                    $url->provider_name = $cache->providerName;
                    $url->provider_url = filter_var($cache->providerUrl, FILTER_VALIDATE_URL)
                        ? $cache->providerUrl
                        : null;
                    $url->published_at = $cache->publishedTime ?? null;

                    try {
                        $url->save();
                    } catch (\Throwable $th) {
                        //throw $th;
                        $url->delete();
                    }
                }
            } else {
                $url->delete();
            }
        }

        $this->schema->table('urls', function (Blueprint $table) {
            $table->text('url')->nullable(false)->change();
            $table->text('serialized_tags')->nullable(false)->change();
            $table->text('serialized_images')->nullable(false)->change();
            $table->text('cache')->nullable()->change();
        });

        $this->schema->table('urls', function (Blueprint $table) {
            $table->dropColumn('cache');
        });
    }

    public function down()
    {
        DB::table('urls')->truncate();

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
