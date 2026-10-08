<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddVcardXmlToContacts extends Migration
{
    public function up()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->text('vcard_xml')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->dropColumn('vcard_xml');
        });
    }
}
