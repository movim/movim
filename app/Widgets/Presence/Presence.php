<?php

namespace App\Widgets\Presence;

use Movim\Widget\Base;

use Moxl\Xec\Action\Presence\Chat;
use Moxl\Xec\Action\Presence\Away;
use Moxl\Xec\Action\Presence\Unavailable;
use Moxl\Xec\Action\Roster\GetList;
use Moxl\Xec\Action\Pubsub\GetItemsId;
use Moxl\Xec\Action\Storage\Get;
use Moxl\Xec\Action\PubsubSubscription\Get as GetPubsubSubscriptions;
use Moxl\Stanza\Stream;

use Movim\Daemon\Session;

use App\Post;
use App\Widgets\Chats\Chats;
use App\Widgets\Dialog\Dialog;
use Movim\CurrentCall;
use Moxl\Xec\Action\Blocking\Request;
use Moxl\Xec\Action\OMEMO\GetDevicesList;

class Presence extends Base
{
    public function load()
    {
        $this->addcss('presence.css');
        $this->addjs('presence.js');
        $this->registerEvent('avatar_get_handle', 'onMyPresence');
        $this->registerEvent('mypresence', 'onMyPresence');
        $this->registerEvent('session_up', 'onSessionUp');
        $this->registerEvent('session_down', 'onSessionDown');
    }

    public function onSessionUp()
    {
        $p = new Chat;
        $p->request();
    }

    public function onSessionDown()
    {
        $p = new Away;
        $p->setLast(Session::DOWN_TIMER)
            ->request();
    }

    public function onMyPresence($packet)
    {
        $this->rpc('MovimTpl.fill', '#presence_widget', $this->preparePresence());
    }

    public function start()
    {
        $this->rpc('Notif.inhibit', 15);

        if ($this->user->session->type == 'bind1') {
            // http://xmpp.org/extensions/xep-0280.html
            \Moxl\Stanza\Carbons::enable();
        }

        // We refresh the roster
        $r = new GetList;
        $r->request();

        // We refresh the blocklist
        $blocked = new Request;
        $blocked->request();

        // We refresh the messages
        (new Chats)->ajaxGetMAMHistory();
        $this->ajaxServerCapsGet();
        $this->ajaxBookmarksGet();
        $this->ajaxPubsubSubscriptionsGet();
        $this->ajaxFeedRefresh();
        $this->ajaxServerDisco();
        $this->ajaxProfileRefresh();
        $this->onSessionUp();
    }

    public function ajaxAskLogout()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_presence_logout'));
    }

    public function ajaxLogout()
    {
        $this->rpc('Presence.clearQuick');

        $this->user->encryptedPasswords()->delete();

        if (CurrentCall::getInstance()->isStarted()) {
            //(new Visio)->ajaxEnd(CurrentCall::getInstance()->jid, CurrentCall::getInstance()->id);
        }

        $p = new Unavailable;
        $p->setType('terminate')
            ->setResource($this->user->session->resource)
            ->setTo($this->user->id)
            ->request();

        Stream::end();
    }

    public function ajaxHttpGetPresence()
    {
        $html = $this->preparePresence();
        if ($html) {
            $this->rpc('MovimTpl.fill', '#presence_widget', $html);
        }
    }

    public function ajaxConfigGet()
    {
        $s = new Get;
        $s->request();
    }

    public function ajaxPubsubSubscriptionsGet()
    {
        // Private Subscritions
        $ps = new GetPubsubSubscriptions;
        $ps->setTo($this->user->id)
            ->setPEPNode('urn:xmpp:pubsub:movim-public-subscription')
            ->request();

        // Public Subscritions
        $ps = new GetPubsubSubscriptions;
        $ps->setTo($this->user->id)
            ->request();
    }

    // We get the server capabilities
    public function ajaxServerCapsGet()
    {
        $c = new \Moxl\Xec\Action\Disco\Request;
        $c->setTo($this->user->session->host)
            ->request();

        $c->setTo($this->user->id)
            ->request();
    }

    // We discover the server services
    public function ajaxServerDisco()
    {
        $c = new \Moxl\Xec\Action\Disco\Items;
        $c->setTo($this->user->session->host)
            ->request();
    }

    // We refresh the profile
    public function ajaxProfileRefresh()
    {
        $a = new \Moxl\Xec\Action\Avatar\Get;
        $a->setTo($this->user->id)
            ->request();

        $v = new \Moxl\Xec\Action\Vcard4\Get;
        $v->setTo($this->user->id)
            ->request();
    }

    // We refresh the bookmarks
    public function ajaxBookmarksGet()
    {
        $b = new \Moxl\Xec\Action\Bookmark2\Get;
        $b->setTo($this->user->id)
            ->request();

        // Also get the old Bookmarks
        $b = new \Moxl\Xec\Action\Bookmark2\Get;
        $b->setTo($this->user->id)
            ->setVersion('0')
            ->request();
    }

    // We refresh our personnal feed
    public function ajaxFeedRefresh()
    {
        $r = new GetItemsId;
        $r->setTo($this->user->id)
            ->setNode(Post::MICROBLOG_NODE)
            ->request();
    }

    public function preparePresence()
    {
        // If the user is still on a logued-in page after a daemon restart
        if ($this->user->id == false) {
            $this->rpc('MovimUtils.disconnect');
            return false;
        }

        // We reload the user instance in memory
        \App\User::me(true);

        $presence = $this->user->session?->presence;
        $contact = $this->user->contact;

        $presencetpl = $this->tpl();

        $presencetpl->assign('me', ($contact == null) ? new \App\Contact : $contact);
        $presencetpl->assign('presence', ($presence == null) ? new \App\Presence : $presence);
        $presencetpl->assign('presencetxt', getPresencesTxt());

        return $presencetpl->draw('_presence', true);
    }

    public function display()
    {
        $contact = $this->user->contact;
        $this->view->assign('page', $this->_view);
        $this->view->assign('me', ($contact == null) ? new \App\Contact : $contact);
    }
}
