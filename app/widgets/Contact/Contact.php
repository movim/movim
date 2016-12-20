<?php

use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Action\Vcard4\Get;
use Respect\Validation\Validator;
use Moxl\Xec\Action\Pubsub\GetItemsId;
use Moxl\Xec\Action\PubsubSubscription\Get as GetSubscriptions;

class Contact extends \Movim\Widget\Base
{
    private $_paging = 12;

    function load()
    {
        $this->registerEvent('vcard_get_handle', 'onVcardReceived', 'contacts');
        $this->registerEvent('vcard4_get_handle', 'onVcardReceived', 'contacts');
        $this->registerEvent('pubsubsubscription_get_handle', 'onSubscriptions', 'contacts');

        $this->addjs('contact.js');
    }

    public function onVcardReceived($packet)
    {
        $contact = $packet->content;
        $this->ajaxGetContact($contact->jid);
        $this->ajaxRefreshSubscriptions($contact->jid);
    }

    public function onSubscriptions($packet)
    {
        $view = $this->tpl();

        $items = [];
        $id = new \Modl\ItemDAO;

        foreach($packet->content as $subscription) {
            $item = $id->getItem($subscription['server'], $subscription['node']);

            if($item) {
                array_push($items, $item);
            }
        }

        $view->assign('subscriptions', $items);

        RPC::call('MovimTpl.fill', '#contact_subscriptions', $view->draw('_contact_subscriptions', true));
    }

    function ajaxClear($page = 0)
    {
        $html = $this->prepareEmpty($page);

        RPC::call('MovimUtils.pushState', $this->route('contact'));
        RPC::call('MovimTpl.fill', '#contact_widget', $html);
    }

    function ajaxGetContact($jid, $page = 0)
    {
        if(!$this->validateJid($jid)) return;

        $html = $this->prepareContact($jid, $page);

        $this->ajaxRefreshSubscriptions($jid);

        RPC::call('MovimUtils.pushState', $this->route('contact', $jid));

        RPC::call('MovimTpl.fill', '#contact_widget', $html);
        RPC::call('MovimTpl.showPanel');

        $r = new GetItemsId;
        $r->setTo($jid)
          ->setNode('urn:xmpp:microblog:0')
          ->request();
    }

    function ajaxGetGallery($jid)
    {
        if(!$this->validateJid($jid)) return;

        $view = $this->tpl();

        $pd = new \Modl\PostnDAO;
        $view->assign('jid', $jid);
        $view->assign('gallery', $pd->getGallery($jid, 0, 20));

        RPC::call('MovimTpl.fill', '#contact_tab', $view->draw('_contact_gallery', true));
    }

    function ajaxGetBlog($jid)
    {
        if(!$this->validateJid($jid)) return;

        $view = $this->tpl();

        $pd = new \Modl\PostnDAO;
        $view->assign('jid', $jid);
        $view->assign('blog', $pd->getPublic($jid, 'urn:xmpp:microblog:0', 0, 18));

        RPC::call('MovimTpl.fill', '#contact_tab', $view->draw('_contact_blog', true));
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

        $r = new GetItemsId;
        $r->setTo($jid)
          ->setNode('urn:xmpp:microblog:0')
          ->request();
    }

    function ajaxRefreshSubscriptions($jid)
    {
        if(!$this->validateJid($jid)) return;

        $r = new GetSubscriptions;
        $r->setTo($jid)
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
            $view->assign('users', $users);
            $view->assign('page', $page);
            $view->assign('presencestxt', getPresencesTxt());
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
        || $c->isOld()) {
            if($c == null) {
                $c = new \Modl\Contact;
                $c->jid = $jid;
            }
            $this->ajaxRefreshVcard($jid);
        }

        $cr = $cd->getRosterItem($jid);

        $view = $this->tpl();

        $view->assign('page', $page);

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

            return $view->draw('_contact', true);
        } elseif(isset($cr)) {
            $view->assign('contact', null);
            $view->assign('contactr', $cr);

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
