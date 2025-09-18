<?php

namespace App\Widgets\Stickers;

use Moxl\Xec\Action\Message\Publish;
use Moxl\Xec\Action\BOB\Answer;

use Psr\Http\Message\ResponseInterface;

use App\Configuration;
use App\Emoji;
use App\Info;
use App\MessageFile;
use App\Sticker;
use App\StickersPack;
use App\Widgets\Chat\Chat;
use App\Widgets\Chats\Chats;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Drawer\Drawer;

class Stickers extends \Movim\Widget\Base
{
    private $paginate = 20;

    public function load()
    {
        $this->addcss('stickers.css');
        $this->addjs('stickers.js');
        $this->registerEvent('bob', 'onRequest');
    }

    public function onRequest($packet)
    {
        list($to, $id, $cid) = array_values($packet->content);

        $eCid = getCid($cid);
        $imagePath = null;

        $emoji = Emoji::where('cache_hash_algorythm', $eCid['algorythm'])
            ->where('cache_hash', $eCid['hash'])
            ->first();

        if ($emoji) {
            $imagePath = $emoji->imagePath;
        } else {
            $sticker = Sticker::where('cache_hash_algorythm', $eCid['algorythm'])
                ->where('cache_hash', $eCid['hash'])
                ->first();

            if ($sticker) {
                $imagePath = $sticker->imagePath;
            }
        }

        if ($imagePath && file_exists($imagePath)) {
            $a = new Answer;
            $a->setTo($to)
                ->setId($id)
                ->setCid($cid)
                ->setType('image/png')
                ->setBase64(base64_encode(file_get_contents($imagePath)))
                ->request();
        }
    }

    public function ajaxSend(string $to, int $id, bool $muc = false)
    {
        if (!validateJid($to)) {
            return;
        }

        $sticker = Sticker::where('id', $id)->first();

        if (!$sticker) return;

        // Creating a message
        $m = new \App\Message;
        $m->user_id         = $this->me->id;
        $m->jidto           = $to;
        $m->jidfrom         = $this->me->id;
        $m->sticker_cid_hash = $sticker->cache_hash;
        $m->sticker_cid_algorythm = $sticker->cache_hash_algorythm;
        $m->seen            = true;
        $m->retracted       = false;
        $m->body            = $this->__('sticker.sent');

        $m->published = gmdate('Y-m-d H:i:s');

        $m->id      = generateUUID();
        $m->type    = 'chat';
        $m->resource = $this->me->session->resource;

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $p = $dom->createElement('p');
        $dom->append($p);

        $img = $dom->createElement('img');
        $img->setAttribute('src', 'cid:' . \phpToIANAHash()[$sticker->cache_hash_algorythm] . '+' . $sticker->cache_hash . '@bob.xmpp.org');
        $img->setAttribute('alt', 'Sticker');
        $p->append($img);

        $p = new Publish;
        $p->setTo($m->jidto)
            ->setContent($m->body)
            ->setHTML($dom->saveXML($dom->documentElement))
            ->setId($m->id);

        if ($muc) {
            $p->setMuc();
        }

        $p->request();

        $m->save();

        // Sending it to Chat and Chats
        if (!$p->getMuc()) {
            $packet = new \Moxl\Xec\Payload\Packet;
            $packet->content = $m;

            $c = new Chats();
            $c->onMessage($packet);

            $c = new Chat();
            $c->onMessage($packet);
        }
    }

    public function ajaxShow(string $to, ?string $packName = null)
    {
        if (!validateJid($to)) {
            return;
        }

        $configuration = Configuration::get();
        $isGifEnabled = !empty($configuration->gifapikey);

        $packs = StickersPack::all();

        $pack = (!$isGifEnabled && $packName == null)
            ? $packs->first()
            : StickersPack::where('name', $packName)->first();

        if ($pack) {
            $view = $this->tpl();
            $view->assign('jid', $to);
            $view->assign('packs', $packs);
            $view->assign('pack', $pack);
            $view->assign('gifEnabled', $isGifEnabled);

            Drawer::fill('stickers', $view->draw('_stickers'), actions: true, tiny: true);
        } else {
            $view = $this->tpl();
            $view->assign('jid', $to);
            $view->assign('packs', $packs);
            $view->assign('pack', null);

            Drawer::fill('stickers', $view->draw('_stickers_gifs'), actions: true, tiny: true);
            $this->rpc('Stickers.setGifsSearchEvent', $to);
        }
    }

    /**
     * @brief Show the smiley Poppin
     */
    public function ajaxReaction(?string $mid = null)
    {
        $info = $mid
            ? Info::where('server', function ($query) use ($mid) {
                $query->select('jidfrom')
                    ->from('messages')
                    ->where('mid', $mid);
            })->first()
            : null;

        $emojis = $this->tpl();
        $emojis->assign('mid', $mid);
        $emojis->assign('reactionsrestrictions', $info ? $info->reactionsrestrictions : null);
        $emojis->assign('favorites', $this->me->emojis);
        $emojis->assign('gotemojis', $mid == null && Emoji::count() > 0);

        Drawer::fill('emojis', $emojis->draw('_stickers_emojis'), actions: true, tiny: true);
        $this->rpc('Stickers.setEmojisEvent', $mid);
    }

    /**
     * @brief Search for gifs
     */
    public function ajaxSearchGifs(string $keyword, int $page = 0)
    {
        $configuration = Configuration::get();
        $apiKey = $configuration->gifapikey;

        if (empty($apiKey)) return;
        $keyword = filter_var($keyword, FILTER_SANITIZE_URL);
        $keyword = str_replace(' ', '+', $keyword);

        requestAsyncURL(
            'https://tenor.googleapis.com/v2/search?q=' . $keyword .
                '&media_filter=preview,tinywebm' .
                '&key=' . $apiKey .
                '&limit=' . $this->paginate .
                '&pos=' . ($page * $this->paginate)
        )->then(function (ResponseInterface $response) {
            $view = $this->tpl();
            $results = \json_decode($response->getBody());

            if ($results) {
                $i = 0;
                foreach ($results->results as $result) {
                    $gif = [
                        'id' => (string)$result->id,
                        'url' => (string)$result->media_formats->tinywebm->url,
                        'preview' => (string)$result->media_formats->preview->url,
                        'width' => (int)$result->media_formats->tinywebm->dims[0],
                        'height' => (int)$result->media_formats->tinywebm->dims[1],
                    ];
                    $view->assign('gif', $gif);

                    $column = $i % 2 == 0
                        ? '.first'
                        : '.second';

                    $this->rpc('MovimTpl.append', '#gifs .masonry' . $column, $view->draw('_stickers_gifs_result'));
                    $i++;
                }
            }

            $this->rpc('Stickers.setGifsEvents');
        }, function (\Exception $e) {
            error_log($e->getMessage());
        });
    }

    /**
     * Resolve a GIF and share it as a message
     */
    public function ajaxSendGif(string $to, string $gifId, bool $muc = false)
    {
        $configuration = Configuration::get();
        $apiKey = $configuration->gifapikey;

        if (empty($apiKey)) return;

        requestAsyncURL(
            'https://tenor.googleapis.com/v2/posts?ids=' . $gifId .
                '&media_filter=preview,tinywebm' .
                '&key=' . $apiKey
        )->then(function (ResponseInterface $response) use ($to, $muc) {
            $results = \json_decode($response->getBody());

            if ($results) {
                $result = $results->results[0];

                $messageFile = new MessageFile;

                $messageFile->name = (string)$result->url;
                $messageFile->url = (string)$result->media_formats->tinywebm->url;
                $messageFile->type = 'video/webm';
                $messageFile->size = (int)$result->media_formats->tinywebm->size;

                $messageFile->thumbnail_type = 'image/png';
                $messageFile->thumbnail_url = (string)$result->media_formats->preview->url;
                $messageFile->thumbnail_width = (int)$result->media_formats->preview->dims[0];
                $messageFile->thumbnail_height = (int)$result->media_formats->preview->dims[1];

                $chat = new Chat();
                $chat->sendMessage(
                    $to,
                    false,
                    $muc,
                    null,
                    $messageFile
                );
            }
        }, function (\Exception $e) {
            error_log($e->getMessage());
        });
    }

    /**
     * @brief Get the path of an emoji
     */
    public function ajaxSmileyGet($string)
    {
        return prepareString($string);
    }

    public function getSmileyPath($id)
    {
        return getSmileyPath($id);
    }
}
