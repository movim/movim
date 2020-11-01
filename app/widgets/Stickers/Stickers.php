<?php

use Moxl\Xec\Action\Message\Publish;
use Moxl\Xec\Action\BOB\Answer;

use Respect\Validation\Validator;

class Stickers extends \Movim\Widget\Base
{
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

        $base64 = base64_encode(file_get_contents(PUBLIC_CACHE_PATH.md5($key).'.png'));

        $a = new Answer;
        $a->setTo($to)
          ->setId($id)
          ->setCid($cid)
          ->setType('image/png')
          ->setBase64($base64)
          ->request();
    }

    public function ajaxSend($to, $pack, $file, $muc = false)
    {
        if (!$this->validateJid($to)) {
            return;
        }

        list($key, $ext) = explode('.', $file);

        $filepath = PUBLIC_PATH.'/stickers/'.$pack.'/'.$key.'.png';

        if (!file_exists($filepath)) {
            return;
        }

        // Caching the picture
        if (!file_exists(PUBLIC_CACHE_PATH.md5($key).'.png')) {
            copy($filepath, PUBLIC_CACHE_PATH.md5($key).'.png');
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

    public function ajaxShow($to, $pack = null)
    {
        if (!$this->validateJid($to)) {
            return;
        }

        $packs = $this->getPacks();

        $pack = isset($pack) ? $pack : current($packs);

        if (in_array($pack, $packs)) {
            $files = scandir(PUBLIC_PATH.'/stickers/'.$pack);

            array_shift($files);
            array_shift($files);

            $view = $this->tpl();
            $view->assign('jid', $to);
            $view->assign('stickers', $files);
            $view->assign('packs', $packs);
            $view->assign('pack', $pack);
            $view->assign('info', parse_ini_file(PUBLIC_PATH.'/stickers/'.$pack.'/info.ini'));
            $view->assign('path', $this->respath('stickers', false, false, true));

            Drawer::fill($view->draw('_stickers'), true);
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

    /**
     * @brief Validate the jid
     *
     * @param string $jid
     */
    private function validateJid($jid)
    {
        return Validator::stringType()->noWhitespace()
                        ->length(6, 60)->validate($jid);
    }

    public function getSmileyPath($id)
    {
        return getSmileyPath($id);
    }
}
