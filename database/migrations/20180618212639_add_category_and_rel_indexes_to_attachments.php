<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddCategoryAndRelIndexesToAttachments extends Migration
{
    public function up()
    {
        $this->schema->table('attachments', function (Blueprint $table) {
            $table->index('category');
            $table->index('rel');
        });
    }

    public function down()
    {
        $this->schema->table('attachments', function (Blueprint $table) {
            $table->dropIndex('attachments_category_index');
            $table->dropIndex('attachments_rel_index');
        });
    }
}
