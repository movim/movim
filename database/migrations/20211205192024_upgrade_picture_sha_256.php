<?php

use App\Contact;
use App\Info;
use Movim\Migration;

class UpgradePictureSha256 extends Migration
{
    public function up()
    {
        // Database related contancs
        Contact::query()->delete();
        Info::whereNotNull('avatarhash')->delete();

        // Destroy all the picture cache
        foreach (glob(PUBLIC_CACHE_PATH . '*.jpg', GLOB_NOSORT) as $path) {
            @unlink($path);
        }

        foreach (glob(PUBLIC_CACHE_PATH . '*.png', GLOB_NOSORT) as $path) {
            @unlink($path);
        }
    }

    public function down()
    {

    }
}
