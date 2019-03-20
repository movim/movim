<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInfosTable extends Migration
{
    public function up()
    {
        $this->schema->create('infos', function (Blueprint $table) {
            $table->string('server', 64);
            $table->string('node', 96);
            $table->string('category', 16);
            $table->string('type', 16)->nullable();
            $table->string('name', 128)->nullable();
            $table->text('description')->nullable();
            $table->datetime('created')->nullable();

            $table->integer('occupants')->default(0);
            $table->boolean('mucpublic')->default(false);
            $table->boolean('mucpersistent')->default(false);
            $table->boolean('mucpasswordprotected')->default(false);
            $table->boolean('mucmembersonly')->default(false);
            $table->boolean('mucmoderated')->default(false);

            $table->text('abuseaddresses')->nullable();
            $table->text('adminaddresses')->nullable();
            $table->text('feedbackaddresses')->nullable();
            $table->text('salesaddresses')->nullable();
            $table->text('securityaddresses')->nullable();
            $table->text('supportaddresses')->nullable();

            $table->timestamps();

            $table->primary(['server', 'node']);
        });
    }

    public function down()
    {
        $this->schema->drop('infos');
    }
}
