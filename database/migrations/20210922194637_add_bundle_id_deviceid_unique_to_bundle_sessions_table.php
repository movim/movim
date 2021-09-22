<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

use App\BundleSession;

class AddBundleIdDeviceIdUniqueToBundleSessionsTable extends Migration
{
    public function up()
    {
        BundleSession::truncate();

        $this->schema->table('bundle_sessions', function (Blueprint $table) {
            $table->renameColumn('device_id', 'deviceid');
            $table->unique(['bundle_id', 'deviceid']);
        });
    }

    public function down()
    {
        $this->schema->table('bundle_sessions', function (Blueprint $table) {
            $table->dropUnique('bundle_sessions_bundle_id_deviceid_unique');
            $table->renameColumn('deviceid', 'device_id');
        });
    }
}
