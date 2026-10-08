<?php

use App\Affiliation;
use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class CreateAffiliationsTable extends Migration
{
    public function up()
    {
        $this->schema->create('affiliations', function (Blueprint $table) {
            $table->string('server', 256);
            $table->string('node', 256);
            $table->enum('affiliation', Affiliation::TYPES);
            $table->string('jid', 256)->index();

            $table->unique(['server', 'node', 'jid']);
            $table->index(['server', 'node']);
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('affiliations');
    }
}
