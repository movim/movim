<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddCategoryTypeIndexesToInfosAndCapabilitiesTable extends Migration
{
    public function up()
    {
        $this->schema->table('capabilities', function (Blueprint $table) {
            $table->index('category');
            $table->index('type');
        });

        $this->schema->table('infos', function (Blueprint $table) {
            $table->index('category');
            $table->index('type');
        });
    }

    public function down()
    {
        $this->schema->table('capabilities', function (Blueprint $table) {
            $table->dropIndex('capabilities_category_index');
            $table->dropIndex('capabilities_type_index');
        });

        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropIndex('infos_category_index');
            $table->dropIndex('infos_type_index');
        });
    }
}
