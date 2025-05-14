<?php

namespace App\Workers\Resolver;

use Embed\Http\Crawler as HttpCrawler;
use Embed\Http\FactoryDiscovery;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use React\Http\Browser;
use React\Http\Message\Uri;

use function React\Async\await;

class Crawler extends HttpCrawler
{
    private RequestFactoryInterface $requestFactory;
    private UriFactoryInterface $uriFactory;
    private array $defaultHeaders = [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:73.0) Gecko/20100101 Firefox/73.0',
        'Cache-Control' => 'max-age=0',
    ];

    public function __construct(private Browser $browser)
    {
        $this->requestFactory = FactoryDiscovery::getRequestFactory();
        $this->uriFactory = FactoryDiscovery::getUriFactory();
    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        foreach ($this->defaultHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return await($this->browser->get($request->getUri(), $this->defaultHeaders));
    }

    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
