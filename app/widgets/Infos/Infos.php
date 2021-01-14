<?php

use Movim\Widget\Base;

use App\Configuration;
use App\User;

class Infos extends Base
{
    public function display()
    {
        $configuration = Configuration::get();
        $connected = (int)requestAPI('started');

        $gitHeadPath = DOCUMENT_ROOT . '/.git/refs/heads/master';
        $hash = file_exists($gitHeadPath) ? substr(file_get_contents($gitHeadPath), 0, 7) : 'release';

        $presences = App\Presence::select('jid', 'resource', 'node')
            ->where('node', '!=', '')
            ->where('resource', '!=', '')
            ->orderBy('node')
            ->groupBy('jid', 'resource', 'node')
            ->get();

        $clients = [];

        foreach ($presences as $presence) {
            if (!isset($clients[$presence->node])) {
                $clients[$presence->node] = 0;
            }

            $clients[$presence->node]++;
        }

        $resolvedClients = [];

        foreach ($clients as $name => $value) {
            $resolvedName = $this->getCapabilityName($name);

            if (!isset($resolvedClients[$resolvedName])) {
                $resolvedClients[$resolvedName] = 0;
            }

            $resolvedClients[$resolvedName] += $value;
        }

        arsort($resolvedClients);

        $infos = [
            'url'           => BASE_URI,
            'language'      => $configuration->locale,
            'whitelist'     => $configuration->xmppwhitelist,
            'description'   => $configuration->description,
            'unregister'    => $configuration->unregister,
            'php_version'   => phpversion(),
            'version'       => APP_VERSION,
            'population'    => User::count(),
            'linked'        => (int)requestAPI('linked'),
            'started'       => $connected,
            'connected'     => $connected,
            'commit'        => $hash,
            'statistics'    => [
                'presences' => [
                    'total' => $presences->count(),
                    'clients' => $resolvedClients
                ]
            ]
        ];

        $this->view->assign('json', json_encode($infos));
    }

    private function getCapabilityName($node)
    {
        $capability = \App\Info::where('node', $node)->first();

        if ($capability && !filter_var($capability->name, FILTER_VALIDATE_URL)) {
            $parts = explode(' ', $capability->name);
            return reset($parts);
        }

        return $node;
    }
}
