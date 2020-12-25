<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Presence\Subscribe;

use Respect\Validation\Validator;

class ContactActions extends Base
{
    public function load()
    {
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
                                                ->take(8)
                                                ->get());
            $tpl->assign('roster', $this->user->session->contacts()->where('jid', $jid)->first());
        } else {
            $tpl->assign('pictures', collect());
            $tpl->assign('roster', null);
        }

        $tpl->assign('clienttype', getClientTypes());

        Drawer::fill($tpl->draw('_contactactions_drawer'));
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
