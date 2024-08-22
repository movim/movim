<?php

use App\OpenChat;
use App\User;
use Carbon\Carbon;
use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class DropCachesTable extends Migration
{
    public function up()
    {
        // Migrating the open chats to a dedicated table

        $this->schema->create('open_chats', function (Blueprint $table) {
            $table->string('user_id');
            $table->string('jid');
            $table->timestamps();

            $table->foreign('user_id')->references('id')
                  ->on('users')->onDelete('cascade');

            $table->primary(['user_id', 'jid']);
        });

        foreach(DB::table('caches')->where('name', 'chats')->get() as $cache) {
            $chats = $this->extractFromCache($cache);

            if (is_array($chats)) {
                $openChats = [];

                foreach ($chats as $jid => $value) {
                    array_push($openChats, [
                        'user_id' => $cache->user_id,
                        'jid' => $jid,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }

                OpenChat::insert($openChats);
            }
        }

        // Migrating the other data to the users table

        $this->schema->table('users', function (Blueprint $table) {
            $table->datetime('posts_since')->nullable();
            $table->datetime('notifications_since')->nullable();
            $table->string('chats_filter', 12)->default('all');
        });

        foreach(DB::table('caches')->where('name', 'since')->get() as $cache) {
            User::where('id', $cache->user_id)->update(['posts_since' => $this->extractFromCache($cache)]);
        }

        foreach(DB::table('caches')->where('name', 'notifs_since')->get() as $cache) {
            User::where('id', $cache->user_id)->update(['notifications_since' => $this->extractFromCache($cache)]);
        }

        foreach(DB::table('caches')->where('name', 'chats_filter')->get() as $cache) {
            User::where('id', $cache->user_id)->update(['chats_filter' => $this->extractFromCache($cache)]);
        }

        // Dropping the table

        $this->schema->drop('caches');
    }

    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('posts_since');
            $table->dropColumn('notifications_since');
            $table->dropColumn('chats_filter');
        });

        $this->schema->drop('open_chats');

        $this->schema->create('caches', function (Blueprint $table) {
            $table->string('user_id', 64);
            $table->string('name', 64);
            $table->text('data');
            $table->timestamps();

            $table->primary(['user_id', 'name']);
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    private function extractFromCache($cache)
    {
        return unserialize(
            gzuncompress(base64_decode(str_replace("\\'", "'", $cache->data)))
        );
    }
}
