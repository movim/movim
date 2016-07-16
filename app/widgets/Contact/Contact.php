<?php

use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Action\Vcard4\Get;
use Respect\Validation\Validator;
use Moxl\Xec\Action\Pubsub\GetItems;

class Contact extends \Movim\Widget\Base
{
    private $_paging = 10;

    function load()
    {
        $this->registerEvent('roster_updateitem_handle', 'onContactEdited', 'contacts');
        $this->registerEvent('vcard_get_handle', 'onVcardReceived', 'contacts');
        $this->registerEvent('vcard4_get_handle', 'onVcardReceived', 'contacts');
    }

    public function onVcardReceived($packet)
    {
        $contact = $packet->content;
        $this->ajaxGetContact($contact->jid);
    }

    public function onContactEdited($packet)
    {
        Notification::append(null, $this->__('edit.updated'));
    }

    function ajaxClear($page = 0)
    {
        $html = $this->prepareEmpty($page);
        RPC::call('movim_fill', 'contact_widget', $html);
    }

    function ajaxGetContact($jid, $page = 0)
    {
        if(!$this->validateJid($jid)) return;

        $html = $this->prepareContact($jid, $page);

        $r = new GetItems;
        $r->setTo($jid)
          ->setNode('urn:xmpp:microblog:0')
          ->request();

        RPC::call('MovimUtils.pushState', $this->route('contact', $jid));

        RPC::call('movim_fill', 'contact_widget', $html);
        RPC::call('MovimTpl.showPanel');
    }

    function ajaxGetDrawer($jid)
    {
        if(!$this->validateJid($jid)) return;

        $tpl = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $cr = $cd->getRosterItem($jid);

        if(isset($cr)) {
            if($cr->value != null) {
                $tpl->assign('presence', getPresencesTxt()[$cr->value]);
            }

            $tpl->assign('contactr', $cr);
            $tpl->assign('caps', $cr->getCaps());
            $tpl->assign('clienttype', getClientTypes());
        }

        $c  = $cd->get($jid);
        $tpl->assign('contact', $c);

        Drawer::fill($tpl->draw('_contact_drawer', true));
    }

    function ajaxEditSubmit($form)
    {
        $rd = new UpdateItem;
        $rd->setTo(echapJid($form->jid->value))
           ->setFrom($this->user->getLogin())
           ->setName($form->alias->value)
           ->setGroup($form->group->value)
           ->request();
    }

    function ajaxRefreshFeed($jid)
    {
        if(!$this->validateJid($jid)) return;

        $r = new GetItems;
        $r->setTo($jid)
          ->setNode('urn:xmpp:microblog:0')
          ->request();
    }

    function ajaxRefreshVcard($jid)
    {
        if(!$this->validateJid($jid)) return;

        $a = new Moxl\Xec\Action\Avatar\Get;
        $a->setTo(echapJid($jid))->request();

        $v = new Moxl\Xec\Action\Vcard\Get;
        $v->setTo(echapJid($jid))->request();

        $r = new Get;
        $r->setTo(echapJid($jid))->request();
    }

    function ajaxEditContact($jid)
    {
        if(!$this->validateJid($jid)) return;

        $rd = new \Modl\RosterLinkDAO();
        $groups = $rd->getGroups();
        $rl     = $rd->get($jid);

        $view = $this->tpl();

        if(isset($rl)) {
            $view->assign('submit',
                $this->call(
                    'ajaxEditSubmit',
                    "MovimUtils.formToJson('manage')"));
            $view->assign('contact', $rl);
            $view->assign('groups', $groups);
        }

        Dialog::fill($view->draw('_contact_edit', true));
    }

    function ajaxChat($jid)
    {
        if(!$this->validateJid($jid)) return;

        $c = new Chats;
        $c->ajaxOpen($jid);

        RPC::call('MovimUtils.redirect', $this->route('chat', $jid));
    }

    function ajaxDeleteContact($jid)
    {
        if(!$this->validateJid($jid)) return;

        $view = $this->tpl();

        $view->assign('jid', $jid);

        Dialog::fill($view->draw('_contact_delete', true));
    }

    function prepareEmpty($page = 0, $jid = null)
    {
        if($jid == null) {
            $cd = new \modl\ContactDAO();
            $count = $cd->countAllPublic();
            if($count != 0){
                $view = $this->tpl();
                $view->assign('users', $this->preparePublic($page));
                return $view->draw('_contact_explore', true);
            } else {
                return '';
            }
        } else {
            $view = $this->tpl();
            $view->assign('jid', $jid);
            return $view->draw('_contact_empty', true);
        }
    }

    function ajaxPublic($page = 0)
    {
        $validate_page = Validator::intType();
        if(!$validate_page->validate($page)) return;

        RPC::call('MovimTpl.fill', '#public_list', $this->preparePublic($page));
    }

    private function preparePublic($page = 0)
    {
        $cd = new \modl\ContactDAO();
        $users = $cd->getAllPublic($page*$this->_paging, $this->_paging);
        $count = $cd->countAllPublic();
        if($users != null){
            $view = $this->tpl();
            $view->assign('pages', array_fill(0, (int)($count/$this->_paging), 'p'));
            $view->assign('users', array_reverse($users));
            $view->assign('page', $page);
            return $view->draw('_contact_public', true);
        }
    }

    function prepareContact($jid, $page = 0)
    {
        if(!$this->validateJid($jid)) return;

        $cd = new \Modl\ContactDAO;
        $c  = $cd->get($jid, true);

        if($c == null
        || $c->created == null
        //|| $c->isEmpty()
        || $c->isOld()) {
            if($c == null) {
                $c = new \Modl\Contact;
                $c->jid = $jid;
            }
            $this->ajaxRefreshVcard($jid);
        }

        $cr = $cd->getRosterItem($jid);

        $view = $this->tpl();

        $pd = new \Modl\PostnDAO;
        $gallery = $pd->getGallery($jid, 0, 12);
        $blog    = $pd->getPublic($jid, 'urn:xmpp:microblog:0', 0, 4);

        $view->assign('page', $page);
        $view->assign('edit',
            $this->call(
                'ajaxEditContact',
                "'".echapJS($jid)."'"));
        $view->assign('delete',
            $this->call(
                'ajaxDeleteContact',
                "'".echapJS($jid)."'"));

        if(isset($c)) {
            $view->assign('mood', getMood());
            $view->assign('clienttype', getClientTypes());

            $view->assign('contact', $c);
            $view->assign('contactr', $cr);

            if($cr->value != null) {
                $view->assign('presence', getPresencesTxt()[$cr->value]);
            }

            if(isset($cr)) {
                $view->assign('caps', $cr->getCaps());
            }

            $view->assign('gallery', $gallery);
            $view->assign('blog', $blog);

            $view->assign('chat',
                $this->call(
                    'ajaxChat',
                    "'".echapJS($c->jid)."'"));

            return $view->draw('_contact', true);
        } elseif(isset($cr)) {
            $view->assign('contact', null);
            $view->assign('contactr', $cr);

            $view->assign('chat',
                $this->call(
                    'ajaxChat',
                    "'".echapJS($cr->jid)."'"));

            return $view->draw('_contact', true);
        } else {
            return $this->prepareEmpty(0, $jid);
        }
    }

    /**
     * @brief Validate the jid
     *
     * @param string $jid
     */
    private function validateJid($jid)
    {
        $validate_jid = Validator::stringType()->noWhitespace()->length(6, 60);
        if(!$validate_jid->validate($jid)) return false;
        else return true;
    }

    function display()
    {
        $validate_jid = Validator::email()->length(6, 40);

        $this->view->assign('jid', false);
        if($validate_jid->validate($this->get('f'))) {
            $this->view->assign('jid', $this->get('f'));
        }
    }
}
