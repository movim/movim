<?php

namespace App\Workers\AvatarHandler;

use App\Configuration;
use App\Contact;
use App\Info;
use Movim\Image;
use React\Http\Browser;
use React\Http\Message\Response;
use React\Promise\Promise;

class AvatarHandler
{
    private array $domainWhitelist = [];

    public function __construct()
    {
        $this->domainWhitelist = Configuration::firstOrNew()->ssrfwhitelist_array;
    }

    public static function getAvatarCachePath(string $token): string
    {
        if (!ctype_xdigit($token)) {
            throw new \InvalidArgumentException('Invalid avatar cache token');
        }

        return CACHE_PATH . 'avatar_' . $token;
    }

    public function url(
        string $jid,
        string $url,
        ?string $node = null,
        ?bool $banner = false
    ): Promise {
        $tlsContext = true;

        // Disable SSL if the host requested is the local one
        if (parse_url(config('daemon.url'), PHP_URL_HOST) == parse_url($url, PHP_URL_HOST)) {
            $tlsContext = [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ];
        }

        $browser = (new Browser(SSRFSafeConnector($tlsContext, $this->domainWhitelist)))
            ->withTimeout(5)
            ->withHeader('User-Agent', DEFAULT_HTTP_USER_AGENT)
            ->withFollowRedirects(true);

        $query = $browser->get($url);

        return new Promise(function ($resolve) use ($query, $jid, $node, $banner) {
            $query->then(function (Response $response) use ($resolve, $jid, $node, $banner) {
                if ($response->getStatusCode() != 200) {
                    $resolve(['jid' => $jid, 'node' => $node, 'key' => null]);

                    return;
                }

                try {
                    $key = null;
                    $bin = (string) $response->getBody();

                    $image = new Image;

                    if (empty($bin) || !$image->fromBin($bin)) {
                        $resolve(['jid' => $jid, 'node' => $node, 'key' => null]);

                        return;
                    }

                    $hash = sha1($bin);

                    if ($node != null) {
                        Info::where('server', $jid)
                            ->where('node', $node)
                            ->update(['avatarhash' => $hash]);

                        $key = $hash;
                    } elseif ($banner == true) {
                        $contact = Contact::firstOrNew(['id' => $jid]);

                        if ($contact->bannerhash != $hash) {
                            $contact->bannerhash = $hash;
                            $contact->save();

                            $key = $jid . '_banner';
                        }
                    } else {
                        $contact = Contact::firstOrNew(['id' => $jid]);

                        if ($contact->avatarhash != $hash) {
                            $contact->avatarhash = $hash;
                            $contact->save();
                        }

                        // No optimisation, we save the image anytime
                        $key = $jid;
                    }

                    if ($key) {
                        $image->setKey($key);
                        $image->save();
                    }

                    $resolve(['jid' => $jid, 'node' => $node, 'key' => $key]);
                } catch (\Throwable $th) {
                    \logError($th);
                    $resolve(['jid' => $jid, 'node' => $node, 'key' => null]);
                }
            }, function (\Throwable $th) use ($resolve, $jid, $node) {
                \logError($th);
                $resolve(['jid' => $jid, 'node' => $node, 'key' => null]);
            });
        });
    }

    public function base64(string $jid, string $type, string $token): Promise
    {
        return new Promise(function ($resolve) use ($jid, $type, $token) {
            $key = self::getAvatarCachePath($token);
            $bin = is_readable($key) ? file_get_contents($key) : false;

            if (empty($bin)) {
                \logError('Avatar cache file missing or empty for ' . $jid);
                $resolve(['jid' => $jid]);
                return;
            }

            $p = new Image;
            $p->setKey($jid);

            if (!$p->fromBin($bin)) {
                \logError('Avatar data for ' . $jid . ' is not a valid image');
                $resolve(['jid' => $jid]);
                return;
            }

            $p->save();

            $contact = Contact::firstOrNew(['id' => $jid]);
            $contact->avatarhash = sha1($bin);
            $contact->avatartype = $type;
            $contact->save();

            $resolve(['jid' => $jid]);
        });
    }
}
