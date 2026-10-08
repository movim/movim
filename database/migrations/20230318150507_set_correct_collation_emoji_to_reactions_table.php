<?php

use Movim\Migration;

class SetCorrectCollationEmojiToReactionsTable extends Migration
{
    public function up()
    {
        /**
         * MySQL specific fix to ensure that the emojis are properly handled
         * See https://github.com/movim/movim/issues/1042
         */
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
            $this->schema->getConnection()->unprepared('alter table reactions modify emoji varchar(32) charset utf8mb4 collate utf8mb4_bin;');
        }
    }

    public function down()
    {

    }
}
