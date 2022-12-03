<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Widget;

use Movim\Widget\Wrapper;

class Event
{
    public function run(string $key, $data = null)
    {
        $widgets = Wrapper::getInstance();
        $widgets->iterate($key, $data);
    }
}
