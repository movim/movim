<?php

use Movim\Migration;
use App\Contact;

class TruncateContactTable extends Migration
{
    public function up()
    {
        Contact::truncate();
    }

    public function down()
    {
    }
}
