<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHatsTable extends Migration
{
    private $columns = ['session_id', 'jid', 'mucjid', 'resource'];

    public function up()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropPrimary($this->columns);
            $table->unique($this->columns);

            $table->increments('id');
        });

        $this->schema->create('hats', function (Blueprint $table) {
            $table->foreignId('presence_id')->constrained()->onDelete('cascade');

            $table->string('uri');
            $table->string('title');
            $table->float('hue')->nullable();

            $table->timestamps();

            $table->unique(['presence_id', 'uri']);
        });
    }

    public function down()
    {
        $this->schema->drop('hats');

        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropColumn('id');

            $table->dropUnique($this->columns);
            $table->primary($this->columns);
        });
    }
}
