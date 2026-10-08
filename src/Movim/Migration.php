<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

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

    public function enableForeignKeyCheck()
    {
        switch ($this->schema->getConnection()->getDriverName()) {
            case 'mysql':
                $this->schema->getConnection()->unprepared('SET foreign_key_checks = 1');
                break;
        }
    }

    public function disableForeignKeyCheck()
    {
        switch ($this->schema->getConnection()->getDriverName()) {
            case 'mysql':
                $this->schema->getConnection()->unprepared('SET foreign_key_checks = 0');
                break;
        }
    }
}
