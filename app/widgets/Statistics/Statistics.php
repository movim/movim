<?php

use Movim\Widget\Base;

class Statistics extends Base
{
    public function load()
    {
        $this->addcss('statistics.css');
    }

    public function getCapabilityName($node)
    {
        $capability = App\Capability::where('node', 'like', '%' . $node . '%')->first();

        if ($capability && !filter_var($capability->name, FILTER_VALIDATE_URL)) {
            $parts = explode(' ', $capability->name);
            return reset($parts);
        }

        return $node;
    }

    public function display()
    {
        $presences = App\Presence::select('jid', 'resource', 'node')
                                 ->where('node', '!=', '')
                                 ->where('resource', '!=', '')
                                 ->orderBy('node')
                                 ->groupBy('jid', 'resource', 'node')
                                 ->get();
        $stats = [];
        $total = 0;

        foreach ($presences as $presence) {
            list($client, $version) = explode('#', $presence->node);
            $parts = explode('/', $client);
            $part = isset($parts[2]) ? $parts[2] : $client;
            if (!isset($stats[$part])) {
                $stats[$part] = 0;
            }

            $stats[$part]++;
            $total++;
        }

        arsort($stats);

        $this->view->assign('stats', $stats);
        $this->view->assign('total', $total);
    }
}
