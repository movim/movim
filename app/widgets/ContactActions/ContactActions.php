<?php

use Movim\EmbedLight;
use Movim\Widget\Base;

use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Presence\Subscribe;

use Respect\Validation\Validator;

include_once WIDGETS_PATH.'Chat/Chat.php';
include_once WIDGETS_PATH . 'Post/Post.php';

class ContactActions extends Base
{
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
        $view->assign('groups', $this->user->session->contacts()->select('group')->groupBy('group')->pluck('group')->toArray());

        Dialog::fill($view->draw('_contactactions_add'));
    }

    public function ajaxGetDrawer($jid)
    {
        if (!$this->validateJid($jid)) {
            return;
        }

        $tpl = $this->tpl();
        $tpl->assign('contact', \App\Contact::firstOrNew(['id' => $jid]));
        if ($jid != $this->user->id) {
            $tpl->assign('pictures', \App\Message::jid($jid)
                                                ->where('picture', true)
                                                ->orderBy('published', 'desc')
                                                ->take(24)
                                                ->get());
            $tpl->assign('links', \App\Message::jid($jid)
                                                ->where('picture', false)
                                                ->whereNotNull('urlid')
                                                ->orderBy('published', 'desc')
                                                ->take(24)
                                                ->get());
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

        if ($hasFingerprints) {
            $this->rpc('ContactActions.getDrawerFingerprints', $jid);
        }
    }

    public function ajaxGetDrawerFingerprints($jid, $deviceId)
    {
        $fingerprints = $this->user->bundles()
                                   ->where('jid', $jid)
                                   ->with('sessions')
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
        if (!$this->validateJid($jid)) {
            return;
        }

        $c = new Chats;
        $c->ajaxOpen($jid);

        $this->rpc('MovimUtils.redirect', $this->route('chat', $jid));
    }

    public function prepareEmbedUrl(EmbedLight $embed)
    {
        return (new \Chat)->prepareEmbed($embed, true);
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new \Post)->prepareTicket($post);
    }

    /**
     * @brief Validate the jid
     *
     * @param string $jid
     */
    private function validateJid($jid)
    {
        $validate_jid = Validator::stringType()->noWhitespace()->length(6, 60);
        return ($validate_jid->validate($jid));
    }
}
