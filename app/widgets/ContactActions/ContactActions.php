<?php

use Movim\EmbedLight;
use Movim\Widget\Base;

use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Presence\Subscribe;

include_once WIDGETS_PATH.'Chat/Chat.php';
include_once WIDGETS_PATH . 'Post/Post.php';

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

    public function ajaxAddAsk($jid)
    {
        $view = $this->tpl();
        $view->assign('contact', App\Contact::firstOrNew(['id' => $jid]));
        $view->assign('groups', $this->user->session->contacts()
                                                    ->select('group')
                                                    ->whereNotNull('group')
                                                    ->distinct()
                                                    ->pluck('group'));

        Dialog::fill($view->draw('_contactactions_add'));
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

        $hasFingerprints = ($this->user->bundles()->where('jid', $jid)->count() > 0);

        $tpl->assign('jid', $jid);
        $tpl->assign('clienttype', getClientTypes());
        $tpl->assign('hasfingerprints', $hasFingerprints);
        $tpl->assign('posts', \App\Post::where('server', $jid)
            ->restrictToMicroblog()
            ->where('open', true)
            ->orderBy('published', 'desc')
            ->take(4)
            ->get()
        );

        Drawer::fill($tpl->draw('_contactactions_drawer'));
        $this->rpc('Tabs.create');

        if ($picturesCount > 0) {
            $this->rpc('ContactActions_ajaxHttpGetPictures', $jid);
        }

        if ($linksCount > 0) {
            $this->rpc('ContactActions_ajaxHttpGetLinks', $jid);
        }

        if ($hasFingerprints) {
            $this->rpc('ContactActions.getDrawerFingerprints', $jid);
        }
    }

    public function ajaxGetDrawerFingerprints($jid, $deviceId)
    {
        $fingerprints = $this->user->bundles()
                                   ->where('jid', $jid)
                                   ->with('capability.identities')
                                   ->get()
                                   ->keyBy('bundleid');

        $latests = \App\Message::selectRaw('max(published) as latest, bundleid')
                               ->where('user_id', $this->user->id)
                               ->where('jidfrom', $jid)
                               ->groupBy('bundleid')
                               ->pluck('latest', 'bundleid');

        foreach ($fingerprints->keys() as $key) {
            $fingerprints[$key]->latest = $latests->has($key)
                ? $latests[$key]
                : null;
        }

        $tpl = $this->tpl();
        $tpl->assign('fingerprints', $fingerprints);
        $tpl->assign('deviceid', $deviceId);
        $tpl->assign('clienttype', getClientTypes());

        $this->rpc('MovimTpl.fill', '#omemo_fingerprints', $tpl->draw('_contactactions_drawer_fingerprints'));
        $this->rpc('ContactActions.resolveSessionsStates', $jid);
    }

    public function ajaxAdd($form)
    {
        $r = new AddItem;
        $r->setTo((string)$form->searchjid->value)
          ->setName((string)$form->alias->value)
          ->setGroup((string)$form->group->value)
          ->request();

        $p = new Subscribe;
        $p->setTo((string)$form->searchjid->value)
          ->request();

        (new Dialog)->ajaxClear();
    }

    public function ajaxChat($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $c = new Chats;
        $c->ajaxOpen($jid);

        $this->rpc('MovimUtils.redirect', $this->route('chat', $jid));
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

    public function prepareEmbedUrl(EmbedLight $embed)
    {
        return (new \Chat)->prepareEmbed($embed, true);
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new \Post)->prepareTicket($post);
    }
}
