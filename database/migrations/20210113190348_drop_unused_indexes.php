<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropUnusedIndexes extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_jidfrom_index');
            $table->dropIndex('messages_originid_index');
        });

        $this->schema->table('attachments', function (Blueprint $table) {
            $table->dropIndex('attachments_rel_index');
            $table->dropIndex('attachments_category_index');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->index('jidfrom');
            $table->index('originid');
        });

        $this->schema->table('attachments', function (Blueprint $table) {
            $table->index('rel');
            $table->index('category');
        });
    }
}