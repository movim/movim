
<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class AddCoinXmlToPresencesTable extends Migration
{
    public function up()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->text('coin_xml')->nullable();
        });

        /**
         * Conference infos requested from presences didn't had the correct parent to
         * discover the SFU service
         */
        DB::table('infos')->delete();
    }

    public function down()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropColumn('coin_xml');
        });
    }
}
