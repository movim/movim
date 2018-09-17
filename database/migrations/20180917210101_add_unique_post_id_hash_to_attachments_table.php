<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUniquePostIdHashToAttachmentsTable extends Migration
{
    public function up()
    {
        // Removing the duplicate lines
        $this->schema->getConnection()->unprepared('
            delete from attachments
            where id in (
                select a1.id
                from attachments a1, attachments a2
                where a1.id > a2.id
                and a1.post_id = a2.post_id
                and a1.href = a2.href
                and a1.category = a2.category
                and a1.rel = a2.rel)
        ');

        $this->schema->table('attachments', function(Blueprint $table) {
            $table->unique(['post_id', 'href', 'category', 'rel']);
        });

    }

    public function down()
    {
        $this->schema->table('attachments', function(Blueprint $table) {
            $table->dropUnique('attachments_post_id_href_category_rel_unique');
        });
    }
}
