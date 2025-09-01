<?php
/*
 * SPDX-FileCopyrightText: 2024 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

use App\Post;

class XMPPUri
{
    private ?string $type = null;
    private ?string $category = null;
    private array $uri = [];
    private array $params = [];

    public function __construct(string $uri)
    {
        $this->uri = parse_url($uri);

        if ($this->uri && $this->uri['scheme'] == 'xmpp') {
            if (isset($this->uri['query'])) {
                if ($this->uri['query'] == 'join') {
                    $this->type = 'room';
                    $this->params = [$this->uri['path']];
                }

                $queryParams = explodeQueryParams($this->uri['query']);

                if (isset($queryParams['node'])) {
                    if (isset($queryParams['item'])) {
                        $this->type = 'post';
                        $this->params = [$this->uri['path'], $queryParams['node'], $queryParams['item']];
                    }

                    $this->category = match ($queryParams['node']) {
                        Post::STORIES_NODE => 'story',
                        Post::MICROBLOG_NODE => 'microblog',
                        default => 'community',
                    };
                }
            } elseif (isset($this->uri['host']) && isset($this->uri['user'])) {
                $this->type = 'contact';
                $this->params = [$this->uri['user'] . '@' . $this->uri['host']];
            } else {
                $this->type = 'contact';
                $this->params = [$this->uri['path']];
            }
        }
    }

    public function getServer(): ?string
    {
        return $this->type == 'post' ? $this->params[0] : null;
    }

    public function getNode(): ?string
    {
        return $this->type == 'post' ? $this->params[1] : null;
    }

    public function getNodeItemId(): ?string
    {
        return $this->type == 'post' ? $this->params[2] : null;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getRoute(): ?string
    {
        switch ($this->type) {
            case 'room':
                return Route::urlize(
                    'chat',
                    [$this->params[0], 'room']
                );
                break;

            case 'post':
                return Route::urlize(
                    'post',
                    $this->params
                );
                break;

            case 'contact':
                return Route::urlize(
                    'contact',
                    $this->params
                );
                break;
        }

        return null;
    }

    public function getPost(): ?Post
    {
        if ($this->type != 'post') return null;

        return Post::where('server', $this->params[0])
                ->where('node',  $this->params[1])
                ->where('nodeid',  $this->params[2])
                ->first();
    }
}
