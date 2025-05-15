<?php

namespace App\Workers\Resolver;

use App\Workers\Resolver\Detectors\ContentLength;
use App\Workers\Resolver\Detectors\ContentType;
use App\Workers\Resolver\Detectors\Images as DetectorsImages;
use App\Workers\Resolver\Detectors\Type;
use App\Workers\Resolver\Extractors\Reddit\Extractor as RedditExtractor;

use Embed\Embed;
use Embed\Extractor;
use Embed\ExtractorFactory;
use React\Http\Browser;
use React\Promise\Promise;

use function React\Async\async;

class Resolver
{
    private array $queries = [];
    private Browser $browser;
    private $maxSizeCache = 50;

    public function __construct()
    {
        $this->browser = new Browser;
    }

    public function resolve(string $url): Promise
    {
        $extractorFactory = new ExtractorFactory;
        $extractorFactory->addAdapter('reddit.com', RedditExtractor::class);
        $extractorFactory->addDetector('images', DetectorsImages::class);
        $extractorFactory->addDetector('contentType', ContentType::class);
        $extractorFactory->addDetector('contentLength', ContentLength::class);
        $extractorFactory->addDetector('type', Type::class);

        $embed = new Embed(new Crawler($this->browser), $extractorFactory);

        if (!array_key_exists($url, $this->queries)) {
            $this->queries[$url] = async(fn () => $embed->get($url))();
        }

        $query = $this->queries[$url];

        // Limit the amount of in-memory resolved URLs, it's a mini-cache
        $this->queries = array_slice($this->queries, -$this->maxSizeCache, $this->maxSizeCache);

        return new Promise(function ($resolve) use ($query) {
            $query->then(function (Extractor $extractor) use ($resolve) {
                try {
                    $resolve($this->extractorToArray($extractor));
                } catch (\Throwable $th) {
                    \logError($th);
                }
            });
        });
    }

    private function extractorToArray(Extractor $extractor): array
    {
        return [
            'authorName'    => $extractor->authorName,
            'authorUrl'     => $extractor->authorUrl ? (string)$extractor->authorUrl : null,
            'contentLength' => $extractor->contentLength,
            'contentType'   => $extractor->contentType,
            'description'   => $extractor->description,
            'icon'          => $extractor->icon ? (string)$extractor->icon : null,
            'image'         => $extractor->image ? (string)$extractor->image : null,
            'images'        => $extractor->images,
            'keywords'      => $extractor->keywords,
            'providerName'  => $extractor->providerName,
            'providerUrl'   => $extractor->providerUrl ? (string)$extractor->providerUrl : null,
            'publishedTime' => $extractor->publishedTime ? $extractor->publishedTime->format('c') : null,
            'title'         => $extractor->title,
            'type'          => $extractor->type,
            'url'           => $extractor->url ? (string)$extractor->url : null,
        ];
    }
}
