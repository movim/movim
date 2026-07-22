<?php

namespace App\Workers\Galener;

class ConferencesManager
{
    public function __construct(
        private GaleneAPIClient $apiClient,
        private $sendXMPP,
        private array $conferences = []
    ) {}

    public function createConference(string $id): Conference
    {
        $this->conferences[$id] = new Conference(
            id: $id,
            apiClient: $this->apiClient,
            sendXMPP: $this->sendXMPP
        );

        return $this->conferences[$id];
    }

    public function getConference(string $id): ?Conference
    {
        if (array_key_exists($id, $this->conferences)) {
            return $this->conferences[$id];
        }

        return null;
    }
}
