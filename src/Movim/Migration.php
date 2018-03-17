<?php

namespace Movim;

use Illuminate\Database\Capsule\Manager as Capsule;
use Phinx\Migration\AbstractMigration;
use Movim\Bootstrap;

class Migration extends AbstractMigration
{
    public $capsule;
    public $schema;

    public function init()
    {
        if (!defined('DOCUMENT_ROOT')) {
            $bootstrap = new Bootstrap;
            $bootstrap->boot(true);
        }

        $this->schema = Capsule::schema();
    }
}
