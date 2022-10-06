<?php

use Moxl\Xec\Action\Message\Publish;
use Moxl\Xec\Action\BOB\Answer;

use Psr\Http\Message\ResponseInterface;

use App\Configuration;
use App\MessageFile;
use Movim\Image;

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
        $content = $packet->content;

        $to = $content[0];
        $id = $content[1];
        $cid = $content[2];

        list($c, $ext) = explode('@', $cid);
        list($sh, $key) = explode('+', $c);

        $base64 = base64_encode(file_get_contents(PUBLIC_CACHE_PATH.hash(Image::$hash, $key).'.png'));

        $a = new Answer;
        $a->setTo($to)
          ->setId($id)
          ->setCid($cid)
          ->setType('image/png')
          ->setBase64($base64)
          ->request();
    }

    public function ajaxSend(string $to, string $pack, $file, bool $muc = false)
    {
        if (!validateJid($to)) {
            return;
        }

        list($key, $ext) = explode('.', $file);

        $filepath = PUBLIC_PATH.'/stickers/'.$pack.'/'.$key.'.png';

        if (!file_exists($filepath)) {
            return;
        }

        // Caching the picture
        if (!file_exists(PUBLIC_CACHE_PATH.hash(Image::$hash, $key).'.png')) {
            copy($filepath, PUBLIC_CACHE_PATH.hash(Image::$hash, $key).'.png');
        }

        // Creating a message
        $m = new \App\Message;
        $m->user_id = $this->user->id;
        $m->jidto   = echapJid($to);
        $m->jidfrom = $this->user->id;
        $m->sticker = $key;
        $m->seen    = true;
        $m->body    = $this->__('sticker.sent');

        $m->published = gmdate('Y-m-d H:i:s');

        $m->id      = generateUUID();
        $m->thread  = generateUUID();
        $m->type    = 'chat';
        $m->resource = $this->user->session->resource;

        // Sending the sticker
        $html = "<p><img alt='Sticker' src='cid:sha1+".$key."@bob.xmpp.org'/></p>";

        $p = new Publish;
        $p->setTo($m->jidto)
          ->setContent($m->body)
          ->setThreadid($m->thread)
          ->setHTML($html)
          ->setId($m->id);

        if ($muc) {
            $p->setMuc();
        }

        $p->request();

        $m->save();

        // Sending it to Chat and Chats
        if (!$p->getMuc()) {
            $packet = new Moxl\Xec\Payload\Packet;
            $packet->content = $m;

            $c = new Chats;
            $c->onMessage($packet);

            $c = new Chat;
            $c->onMessage($packet);
        }
    }

    public function ajaxShow(string $to, $pack = null)
    {
        if (!validateJid($to)) {
            return;
        }

        $configuration = Configuration::get();
        $isGifEnabled = !empty($configuration->gifapikey);

        $packs = $this->getPacks();

        if (!$isGifEnabled && $pack == null) {
            $pack = current($packs);
        }

        if (isset($pack)) {
            $files = scandir(PUBLIC_PATH.'/stickers/'.$pack);

            array_shift($files);
            array_shift($files);

            $view = $this->tpl();
            $view->assign('jid', $to);
            $view->assign('stickers', $files);
            $view->assign('packs', $packs);
            $view->assign('pack', $pack);
            $view->assign('gifEnabled', $isGifEnabled);
            $view->assign('info', parse_ini_file(PUBLIC_PATH.'/stickers/'.$pack.'/info.ini'));
            $view->assign('path', $this->respath('stickers', false, false, true));

            Drawer::fill($view->draw('_stickers'), true);
        } else {
            $view = $this->tpl();
            $view->assign('jid', $to);
            $view->assign('packs', $packs);

            Drawer::fill($view->draw('_stickers_gifs'), true);
            $this->rpc('Stickers.setGifsSearchEvent', $to);
        }
    }

    /**
     * @brief Show the smiley Poppin
     */
    public function ajaxReaction(string $mid = null)
    {
        $view = $this->tpl();

        $emojis = $this->tpl();
        $emojis->assign('mid', $mid);
        $view->assign('emojis', $emojis->draw('_stickers_emojis'));

        Dialog::fill($view->draw('_stickers_reactions'));
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

        $keyword = filter_var($keyword, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $keyword = str_replace(' ', '+', $keyword);

        requestAsyncURL(
            'https://api.tenor.com/v1/search?q='.$keyword.
            '&key='.$apiKey.
            '&limit='.$this->paginate.
            '&pos='.($page*$this->paginate)
        )->then(function (ResponseInterface $response) {
            $view = $this->tpl();

            $results = \json_decode($response->getBody());

            if ($results) {
                $i = 0;
                foreach ($results->results as $result) {
                    $gif = [
                        'id' => $result->id,
                        'url' => $result->media[0]->tinywebm->url,
                        'preview' => $result->media[0]->tinywebm->preview,
                        'width' => $result->media[0]->tinywebm->dims[0],
                        'height' => $result->media[0]->tinywebm->dims[1],
                    ];
                    $view->assign('gif', $gif);

                    $column = $i % 2 == 0
                        ? '.first'
                        : '.second';

                    $this->rpc('MovimTpl.append', '#gifs .masonry'.$column, $view->draw('_stickers_gifs_result'));
                    $i ++;
                }
            }

            $this->rpc('Stickers.setGifsEvents');
        });
    }

    /**
     * Resolve a GIF and share it as a message
     */
    public function ajaxSendGif(string $to, int $gifId, bool $muc = false)
    {
        $configuration = Configuration::get();
        $apiKey = $configuration->gifapikey;

        if (empty($apiKey)) return;

        requestAsyncURL(
            'https://api.tenor.com/v1/gifs?ids='.$gifId.
            '&key='.$apiKey
        )->then(function (ResponseInterface $response) use ($to, $muc) {
            $results = \json_decode($response->getBody());

            if ($results) {
                $result = $results->results[0];

                $messageFile = new MessageFile;

                $messageFile->name = $result->url;
                $messageFile->uri = $result->media[0]->tinywebm->url;
                $messageFile->type = 'video/webm';
                $messageFile->size = $result->media[0]->tinywebm->size;

                $messageFile->thumbnail->type = 'image/png';
                $messageFile->thumbnail->uri = $result->media[0]->tinywebm->preview;
                $messageFile->thumbnail->width = $result->media[0]->tinywebm->dims[0];
                $messageFile->thumbnail->height = $result->media[0]->tinywebm->dims[1];

                $chat = new \Chat;
                $chat->sendMessage(
                    $to, false, $muc,
                    null, $messageFile);
            }
        });
    }

    /**
     * @brief Get the path of an emoji
     */
    public function ajaxSmileyGet($string)
    {
        return prepareString($string);
    }

    /**
     * @brief Get a list of stickers packs
     */
    public function getPacks()
    {
        $dirs = scandir(PUBLIC_PATH.'/stickers/');

        $packs = [];

        array_shift($dirs);
        array_shift($dirs);

        // Get the packs
        foreach ($dirs as $dir) {
            if (is_dir(PUBLIC_PATH.'/stickers/'.$dir)) {
                array_push($packs, $dir);
            }
        }

        return $packs;
    }

    public function getSmileyPath($id)
    {
        return getSmileyPath($id);
    }
}
