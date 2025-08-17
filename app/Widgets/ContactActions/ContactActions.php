<?php

namespace App\Widgets\ContactActions;

use App\Message;
use App\Widgets\AdHoc\AdHoc;
use App\Widgets\Chat\Chat;
use App\Widgets\Chats\Chats;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Post\Post;
use App\Widgets\Toast\Toast;
use Movim\CurrentCall;
use Movim\EmbedLight;
use Movim\Widget\Base;
use Moxl\Xec\Action\Blocking\Block;
use Moxl\Xec\Action\Blocking\Unblock;

class ContactActions extends Base
{
    private $_picturesPagination = 20;
    private $_linksPagination = 12;

    public function load()
    {
        $this->addjs('contactactions.js');
        $this->registerEvent('roster_additem_handle', 'onAdd', 'contact');
        $this->registerEvent('roster_removeitem_handle', 'onDelete');
        $this->registerEvent('roster_updateitem_handle', 'onUpdate');
    }

    public function onDelete($packet)
    {
        Toast::send($this->__('roster.deleted'));
    }

    public function onAdd($packet)
    {
        Toast::send($this->__('roster.added'));
    }

    public function onUpdate($packet = false)
    {
        Toast::send($this->__('roster.updated'));
    }

    public function ajaxGetDrawer($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $tpl = $this->tpl();
        $tpl->assign('contact', \App\Contact::firstOrNew(['id' => $jid]));

        $picturesCount = 0;
        $linksCount = 0;

        if ($jid != $this->user->id) {
            $picturesCount = \App\Message::jid($jid)
                ->where('picture', true)
                ->orderBy('published', 'desc')
                ->count();
            $linksCount = \App\Message::jid($jid)
                ->where('picture', false)
                ->whereNotNull('urlid')
                ->count();
            $tpl->assign('picturesCount', $picturesCount);
            $tpl->assign('linksCount', $linksCount);
            $tpl->assign('roster', $this->user->session->contacts()->where('jid', $jid)->first());
        } else {
            $tpl->assign('pictures', collect());
            $tpl->assign('links', collect());
            $tpl->assign('roster', null);
        }

        $tpl->assign('jid', $jid);
        $tpl->assign('incall', CurrentCall::getInstance()->isStarted());
        $tpl->assign('clienttype', getClientTypes());
        $tpl->assign('posts', \App\Post::where('server', $jid)
            ->restrictToMicroblog()
            ->where('open', true)
            ->orderBy('published', 'desc')
            ->take(4)
            ->get()
        );

        Drawer::fill('contact_drawer', $tpl->draw('_contactactions_drawer'));
        $this->rpc('Tabs.create');

        if ($picturesCount > 0) {
            $this->rpc('ContactActions_ajaxHttpGetPictures', $jid);
        }

        if ($linksCount > 0) {
            $this->rpc('ContactActions_ajaxHttpGetLinks', $jid);
        }

        if ($this->user->hasOMEMO()) {
            $this->rpc('ContactActions.getDrawerFingerprints', $jid);
        }

        (new AdHoc)->ajaxGet($jid);
    }

    public function ajaxGetDrawerFingerprints(string $jid, array $fingerprints)
    {
        $fingerprints = collect($fingerprints);

        foreach ($fingerprints as $fingerprint) {
            $fingerprint->fingerprint = base64ToFingerPrint($fingerprint->fingerprint);
        }

        $latests = \App\Message::selectRaw('max(published) as latest, bundleid')
                               ->where('user_id', $this->user->id)
                               ->where('jidfrom', $jid)
                               ->groupBy('bundleid')
                               ->pluck('latest', 'bundleid');

        foreach ($fingerprints as $fingerprint) {
            $fingerprint->latest = $latests->has($fingerprint->bundleid)
                ? $latests[$fingerprint->bundleid]
                : null;
        }

        $tpl = $this->tpl();
        $tpl->assign('fingerprints', $fingerprints);
        $tpl->assign('clienttype', getClientTypes());

        $this->rpc('MovimTpl.fill', '#omemo_fingerprints', $tpl->draw('_contactactions_drawer_fingerprints'));
        $this->rpc('ContactActions.resolveSessionsStates', $jid);
    }

    public function ajaxChat(string $jid, ?bool $muc = false)
    {
        if (!validateJid($jid)) {
            return;
        }

        if ($muc) {
            $this->rpc('MovimUtils.reload', $this->route('chat', [$jid, 'room']));
        } else {
            $c = new Chats();
            $c->ajaxOpen($jid);

            $this->rpc('MovimUtils.reload', $this->route('chat', $jid));
        }
    }

    public function ajaxBlock(string $jid)
    {
        $block = new Block;
        $block->setJid($jid);
        $block->request();
    }

    public function ajaxUnblock(string $jid)
    {
        $block = new Unblock;
        $block->setJid($jid);
        $block->request();
    }

    public function ajaxHttpGetPictures($jid, $page = 0)
    {
        $tpl = $this->tpl();

        $more = false;
        $pictures = \App\Message::jid($jid)
            ->where('picture', true)
            ->orderBy('published', 'desc')
            ->take($this->_picturesPagination + 1)
            ->skip($this->_picturesPagination * $page)
            ->get();

        if ($pictures->count() == $this->_picturesPagination + 1) {
            $pictures->pop();
            $more = true;
        }
        $tpl->assign('pictures', $pictures);
        $tpl->assign('more', $more);
        $tpl->assign('page', $page);
        $tpl->assign('jid', $jid);

        $this->rpc('MovimTpl.append', '#contact_pictures', $tpl->draw('_contactactions_drawer_pictures'));
    }

    public function ajaxHttpGetLinks($jid, $page = 0)
    {
        $tpl = $this->tpl();

        $more = false;
        $links = \App\Message::jid($jid)
            ->where('picture', false)
            ->whereNotNull('urlid')
            ->orderBy('published', 'desc')
            ->take($this->_linksPagination + 1)
            ->skip($this->_linksPagination * $page)
            ->get();

        if ($links->count() == $this->_linksPagination + 1) {
            $links->pop();
            $more = true;
        }
        $tpl->assign('links', $links);
        $tpl->assign('more', $more);
        $tpl->assign('page', $page);
        $tpl->assign('jid', $jid);

        $this->rpc('MovimTpl.append', '#contact_links', $tpl->draw('_contactactions_drawer_links'));
    }

    public function prepareEmbedUrl(Message $message)
    {
        $resolved = $message->resolvedUrl->cache;

        if ($resolved) {
            return (new Chat())->prepareEmbed($resolved, $message);
        }
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new Post())->prepareTicket($post);
    }
}
