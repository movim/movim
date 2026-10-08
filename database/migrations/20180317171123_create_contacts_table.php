<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContactsTable extends Migration
{
    public function up()
    {
        $this->schema->create('contacts', function (Blueprint $table) {
            $table->string('id', 64);
            $table->string('fn', 64)->nullable();
            $table->string('name')->nullable();
            $table->date('date')->nullable();
            $table->string('url')->nullable();
            $table->string('email')->nullable();
            $table->string('adrlocality')->nullable();
            $table->string('adrpostalcode')->nullable();
            $table->string('adrcountry')->nullable();
            $table->text('description')->nullable();
            $table->string('mood', 32)->nullable();
            $table->string('activity', 32)->nullable();
            $table->string('nickname', 64)->nullable();

            $table->string('tuneartist')->nullable();
            $table->string('tunesource')->nullable();
            $table->string('tunetitle')->nullable();
            $table->string('tunetrack')->nullable();
            $table->integer('tunelenght')->nullable();
            $table->integer('tunerating')->nullable();

            $table->string('loclatitude', 32)->nullable();
            $table->string('loclongitude', 32)->nullable();
            $table->string('localtitude', 32)->nullable();
            $table->string('loccountry', 64)->nullable();
            $table->string('loccountrycode', 2)->nullable();
            $table->string('locregion', 128)->nullable();
            $table->string('locpostalcode', 16)->nullable();
            $table->string('loclocality', 128)->nullable();
            $table->string('locstreet', 128)->nullable();
            $table->string('locbuilding', 32)->nullable();
            $table->text('loctext')->nullable();
            $table->string('locuri', 128)->nullable();
            $table->dateTime('loctimestamp')->nullable();

            $table->string('avatarhash', 128)->nullable();
            $table->timestamps();

            $table->primary('id');
        });
    }

    public function down()
    {
        $this->schema->drop('contacts');
    }
}
