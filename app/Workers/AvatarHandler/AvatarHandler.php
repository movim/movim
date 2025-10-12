<?php

namespace App\Workers\AvatarHandler;

use Movim\Image;
use React\Http\Browser;
use React\Http\Message\Response;
use React\Promise\Promise;
use React\Socket\Connector;

class Avatarhandler
{
    public function url(
        string $jid,
        string $url,
        ?string $node = null,
        ?bool $banner = false
    ): Promise {
        $connector = null;

        // Disable SSL if the host requested is the local one
        if (parse_url(config('daemon.url'), PHP_URL_HOST) == parse_url($url, PHP_URL_HOST)) {
            $connector = new Connector([
                'tls' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
        }

        $browser = (new Browser($connector))
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
                    $bin = (string)$response->getBody();

                    if (empty($bin)) {
                        $resolve(['jid' => $jid, 'node' => $node, 'key' => null]);
                        return;
                    }

                    $hash = sha1($bin);

                    if ($node != null) {
                        $info = \App\Info::where('server', $jid)
                            ->where('node', $node)
                            ->first();

                        if ($info && $info->avatarhash != $hash) {
                            $info->avatarhash = $hash;
                            $info->save();

                            $key = $hash;
                        }
                    } elseif ($banner == true) {
                        $contact = \App\Contact::firstOrNew(['id' => $jid]);

                        if ($contact->bannerhash != $hash) {
                            $contact->bannerhash = $hash;
                            $contact->save();

                            $key = $jid . '_banner';
                        }
                    } else {
                        $contact = \App\Contact::firstOrNew(['id' => $jid]);

                        if ($contact->avatarhash != $hash) {
                            $contact->avatarhash = $hash;
                            $contact->save();
                        }

                        // No optimisation, we save the image anytime
                        $key = $jid;
                    }


                    if ($key) {
                        $image = new Image;
                        $image->fromBin($bin);
                        $image->setKey($key);
                        $image->save();
                    }

                    $resolve(['jid' => $jid, 'node' => $node, 'key' => $key]);
                } catch (\Throwable $th) {
                    \logError($th);
                }
            });
        });
    }

    public function base64(string $jid, string $base64): Promise
    {
        return new Promise(function ($resolve) use ($jid, $base64) {
            $p = new Image;
            $p->setKey($jid);
            $p->fromBase64($base64);
            $p->save();

            $contact = \App\Contact::firstOrNew(['id' => $jid]);
            $contact->avatarhash = sha1(base64_decode($base64));
            $contact->avatartype = 'vcard-temp';
            $contact->save();

            $resolve(['jid' => $jid]);
        });
    }
}
