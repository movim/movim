<?php

use Movim\Migration;

use App\Contact;
use App\Presence;
use App\Roster;
use App\Conference;

use Illuminate\Database\Schema\Blueprint;

class ChangeUrlsToTextUploadTable extends Migration
{
    public function up()
    {
        $this->schema->table('upload', function (Blueprint $table) {
            $table->text('geturl')->nullable()->change();
            $table->text('puturl')->nullable()->change();
        });
    }

    public function down()
    {
        $this->schema->table('upload', function (Blueprint $table) {
            $table->string('geturl')->nullable()->change();
            $table->string('puturl')->nullable()->change();
        });
    }
}
