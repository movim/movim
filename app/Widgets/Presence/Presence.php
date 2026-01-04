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
use Moxl\Xec\Action\Pubsub\GetSubscriptions;
use Moxl\Xec\Payload\Packet;

class Presence extends Base
{
    public function load()
    {
        $this->addcss('presence.css');
        $this->addjs('presence.js');
        $this->registerEvent('avatar_get_handle', 'tonMyPresence');
        $this->registerEvent('mypresence', 'tonMyPresence');
        $this->registerEvent('session_up', 'onSessionUp');
        $this->registerEvent('session_down', 'onSessionDown');
    }

    public function onSessionUp()
    {
        $p = $this->xmpp(new Chat);
        $p->request();
    }

    public function onSessionDown()
    {
        $p = $this->xmpp(new Away);
        $p->setLast(Session::DOWN_TIMER)
            ->request();
    }

    public function tonMyPresence(Packet $packet)
    {
        $this->rpc('MovimTpl.fill', '#presence_widget', $this->preparePresence());
    }

    public function start()
    {
        $this->rpc('Notif.inhibit', 15);

        if ($this->me->session->type == 'bind1') {
            // http://xmpp.org/extensions/xep-0280.html
            \Moxl\Stanza\Carbons::enable(); // TODO fixme
        }

        // We refresh the roster
        $r = $this->xmpp(new GetList);
        $r->request();

        // We refresh the blocklist
        $blocked = $this->xmpp(new Request);
        $blocked->request();

        // We refresh the messages
        (new Chats($this->me))->ajaxGetMAMHistory();
        $this->ajaxServerCapsGet();
        $this->ajaxBookmarksGet();
        $this->ajaxPubsubSubscriptionsGet();
        $this->ajaxFeedRefresh();
        $this->ajaxServerDisco();
        $this->ajaxProfileRefresh();
        $this->ajaxGetFollowers();
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

        $this->me->encryptedPasswords()->delete();

        if (CurrentCall::getInstance()->isStarted()) {
            //(new Visio)->ajaxEnd(CurrentCall::getInstance()->jid, CurrentCall::getInstance()->id);
        }

        $p = $this->xmpp(new Unavailable);
        $p->setType('terminate')
            ->setResource($this->me->session->resource)
            ->setTo($this->me->id)
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
        $s = $this->xmpp(new Get);
        $s->request();
    }

    public function ajaxPubsubSubscriptionsGet()
    {
        // Private Subscritions
        $ps = $this->xmpp(new GetPubsubSubscriptions);
        $ps->setTo($this->me->id)
            ->setPEPNode('urn:xmpp:pubsub:movim-public-subscription')
            ->request();

        // Public Subscritions
        $ps = $this->xmpp(new GetPubsubSubscriptions);
        $ps->setTo($this->me->id)
            ->request();
    }

    // We get the server capabilities
    public function ajaxServerCapsGet()
    {
        $c = $this->xmpp(new \Moxl\Xec\Action\Disco\Request($this->me));
        $c->setTo($this->me->session->host)
            ->request();

        $c->setTo($this->me->id)
            ->request();
    }

    // We discover the server services
    public function ajaxServerDisco()
    {
        $c = $this->xmpp(new \Moxl\Xec\Action\Disco\Items($this->me));
        $c->setTo($this->me->session->host)
            ->request();
    }

    // We refresh the profile
    public function ajaxProfileRefresh()
    {
        $a = $this->xmpp(new \Moxl\Xec\Action\Avatar\Get($this->me));
        $a->setTo($this->me->id)
            ->request();

        $v = $this->xmpp(new \Moxl\Xec\Action\Vcard4\Get($this->me));
        $v->setTo($this->me->id)
            ->request();
    }

    // We refresh the bookmarks
    public function ajaxBookmarksGet()
    {
        $b = $this->xmpp(new \Moxl\Xec\Action\Bookmark2\Get($this->me));
        $b->setTo($this->me->id)
            ->request();

        // Also get the old Bookmarks
        $b = $this->xmpp(new \Moxl\Xec\Action\Bookmark2\Get($this->me));
        $b->setTo($this->me->id)
            ->setVersion('0')
            ->request();
    }

    // We refresh our personnal feed
    public function ajaxFeedRefresh()
    {
        $r = $this->xmpp(new GetItemsId);
        $r->setTo($this->me->id)
            ->setNode(Post::MICROBLOG_NODE)
            ->request();
    }

    // We refresh the blog followers
    public function ajaxGetFollowers()
    {
        $gs = $this->xmpp(new GetSubscriptions);
        $gs->setTo($this->me->id)
            ->setNode(Post::MICROBLOG_NODE)
            ->setNotify(false)
            ->request();
    }

    public function preparePresence()
    {
        // If the user is still on a logued-in page after a daemon restart
        if ($this->me->id == false) {
            $this->rpc('MovimUtils.disconnect');
            return false;
        }

        // We reload the user instance in memory
        \App\User::me(true);

        $presence = $this->me->session?->presence;
        $contact = $this->me->contact;

        $presencetpl = $this->tpl();

        $presencetpl->assign('me', ($contact == null) ? new \App\Contact : $contact);
        $presencetpl->assign('presence', ($presence == null) ? new \App\Presence : $presence);
        $presencetpl->assign('presencetxt', getPresencesTxt());

        return $presencetpl->draw('_presence', true);
    }

    public function display()
    {
        $this->view->assign('page', $this->_view);
    }
}
