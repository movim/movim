<?php

use Movim\Migration;

class SetCorrectCollationToAllTables extends Migration
{
    public function up()
    {
        if ($this->schema->getConnection()->getDriverName() == 'mysql') {
            $this->disableForeignKeyCheck();

            foreach ($this->schema->getConnection()->select('
                select concat("ALTER TABLE `", TABLE_NAME,"` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;") as result
                from information_schema.tables
                where table_schema="' . config('database.database') . '"
                and table_type="BASE TABLE"
            ') as $convert) {
                $this->schema->getConnection()->statement($convert->result);
            };

            $this->enableForeignKeyCheck();
        }
    }

    public function down()
    {

    }
}
