<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHatsTable extends Migration
{
    private $columns = ['session_id', 'jid', 'mucjid', 'resource'];

    public function up()
    {
        $this->disableForeignKeyCheck();
        $this->schema->table('presences', function (Blueprint $table) {
            if ($this->schema->getConnection()->getDriverName() == 'pgsql') {
                $table->dropPrimary($this->columns);
                $table->unique($this->columns);
            }

            if ($this->schema->getConnection()->getDriverName() == 'mysql') {
                $table->dropPrimary(['session_id', 'jid', 'resource']);
            }

            $table->increments('id');
        });
        $this->enableForeignKeyCheck();

        $this->schema->create('hats', function (Blueprint $table) {
            $table->integer('presence_id')->constrained()->onDelete('cascade');

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

        $this->disableForeignKeyCheck();
        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropColumn('id');

            if ($this->schema->getConnection()->getDriverName() == 'pgsql') {
                $table->dropUnique($this->columns);
                $table->primary($this->columns);
            }

            if ($this->schema->getConnection()->getDriverName() == 'mysql') {
                $table->primary(['session_id', 'jid', 'resource']);
            }
        });
        $this->enableForeignKeyCheck();

    }
}
