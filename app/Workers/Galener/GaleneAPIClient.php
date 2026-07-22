<?php

namespace App\Workers\Galener;

use React\Http\Browser;
use React\Promise\PromiseInterface;

class GaleneAPIClient
{
    private Browser $browser;
    private const API_PATH = '/galene-api/v0/';
    public const USER_WILDCARD_PASSWORD = 'password';

    public function __construct(
        public int $port,
        private string $adminUsername,
        private string $adminPassword
    ) {
        $this->browser = new Browser();
    }

    private function getPath(): string
    {
        return 'http://localhost:' . $this->port . self::API_PATH;
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->adminUsername . ':' . $this->adminPassword),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function json(array $data): string
    {
        return json_encode($data);
    }

    public function createGroup(string $groupName)
    {
        $this->browser->put(
            $this->getPath() . '.groups/' . $groupName,
            $this->getHeaders(),
            $this->json(['public' => true])
        )/*->then(fn($response) => $this->browser->put(
            $this->getPath() . '.groups/' . $groupName . '/.wildcard-user',
            $this->getHeaders(),
            '{}'
        )->then(
            function ($response) use ($groupName) {
                $headers = $this->getHeaders();
                $headers['Content-Type']= 'text/plain';

                return $this->browser->post(
                    $this->getPath() . '.groups/' . $groupName . '/.wildcard-user/.password',
                    $headers,
                    self::USER_WILDCARD_PASSWORD
                );
            }
        ))*/;
    }

    public function getGroup(string $groupName): PromiseInterface
    {
        return $this->browser->get(
            $this->getPath() . '.groups/' . $groupName,
            $this->getHeaders()
        );
    }

    public function getGroupStatus(string $groupName): PromiseInterface
    {
        return $this->browser->get(
            $this->getPath() . '.groups/' . $groupName . '/.status',
            $this->getHeaders()
        );
    }

    public function addUserToGroup(string $groupName, string $username): PromiseInterface
    {
        return $this->browser->put(
            $this->getPath() . '.groups/' . $groupName . '/.users/' . $username,
            $this->getHeaders(),
            $this->json(['permissions' => 'op'])
        )->then(
            function ($response) use ($groupName, $username) {
                $headers = $this->getHeaders();
                $headers['Content-Type'] = 'text/plain';

                return $this->browser->post(
                    $this->getPath() . '.groups/' . $groupName . '/.users/' . $username . '/.password',
                    $headers,
                    self::USER_WILDCARD_PASSWORD
                );
            }
        );
    }
}
