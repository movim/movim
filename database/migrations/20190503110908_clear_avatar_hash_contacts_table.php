<?php

use App\Contact;
use Movim\Migration;

class ClearAvatarHashContactsTable extends Migration
{
    public function up()
    {
        Contact::whereNotNull('avatarhash')->update(['avatarhash' => null]);
    }

    public function down()
    {
    }
}
