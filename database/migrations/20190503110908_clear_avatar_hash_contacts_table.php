<?php

use Movim\Migration;
use App\Contact;
use Illuminate\Database\Schema\Blueprint;

class ClearAvatarHashContactsTable extends Migration
{
    public function up()
    {
        App\Contact::whereNotNull('avatarhash')->update(['avatarhash' => null]);
    }

    public function down()
    {
    }
}
