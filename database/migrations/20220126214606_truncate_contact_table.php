<?php

use App\Contact;
use Movim\Migration;

class TruncateContactTable extends Migration
{
    public function up()
    {
        Contact::truncate();
    }

    public function down() {}
}
