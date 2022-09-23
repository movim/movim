<?php

use Moxl\Xec\Action\Presence\Subscribed;
use Moxl\Xec\Action\Presence\Unsubscribed;
use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Presence\Subscribe;

use Movim\Widget\Base;
use Movim\Session;

class Notifications extends Base
{
    public function load()
    {
        $this->addjs('notifications.js');

        $this->registerEvent('post', 'onPost');
        $this->registerEvent('pubsub_getitem_handle', 'onPost');
        $this->registerEvent('subscribe', 'onInvitations');
        $this->registerEvent('roster', 'onRoster');
        $this->registerEvent('roster_additem_handle', 'onInvitations');
        $this->registerEvent('roster_updateitem_handle', 'onInvitations');
        $this->registerEvent('presence_subscribe_handle', 'onInvitations');
        $this->registerEvent('presence_subscribed_handle', 'onInvitations');
    }

    public function onPost($packet)
    {
        $post = $packet->content;
        if ($post->isComment() && !$post->isMine()) {
            $this->ajaxSetCounter();
        }
    }

    public function onRoster($packet)
    {
        $contact = $this->user->session->contacts()->where('jid', $packet->content)->first();

        // If the invitation was accepted or removed from another connected client
        if (($contact && $contact->subscription == 'both') || !$contact) {
            $this->removeInvitation($packet->content);
        }
    }

    public function onInvitations($from = false)
    {
        if (is_string($from)) {
            $contact = App\Contact::firstOrNew(['id' => $from]);

            Notification::append(
                'invite|'.$from,
                $contact->truename,
                $this->__('invitations.wants_to_talk', $contact->truename),
                $contact->getPhoto(),
                4
            );
        }

        $this->ajaxSetCounter();
    }

    public function ajaxRequest()
    {
        Drawer::fill($this->prepareNotifications());
        \App\Cache::c('notifs_since', date(MOVIM_SQL_DATE));
        $this->ajaxSetCounter();
        (new Notification)->ajaxClear('comments');
    }

    public function ajaxSetCounter()
    {
        $since = \App\Cache::c('notifs_since');
        if (!$since) {
            $since = date(MOVIM_SQL_DATE, 0);
        }

        $count = \App\Post::whereIn('parent_id', function ($query) {
            $query->select('id')
                  ->from('posts')
                  ->where('aid', $this->user->id);
        })->where('published', '>', $since)
        ->where('aid', '!=', $this->user->id)->count();

        $session = Session::start();
        $notifs = $session->get('activenotifs', []);

        if (is_array($notifs)) {
            $count += count($notifs);
        }

        $this->rpc('Notifications.setCounters', ($count > 0) ? $count : '');
    }

    public function ajaxAccept($jid)
    {
        $jid = echapJid($jid);

        if ($this->user->session->contacts()->where('jid', $jid)->count() == 0) {
            $r = new AddItem;
            $r->setTo($jid)
              ->request();
        }

        $p = new Subscribe;
        $p->setTo($jid)
          ->request();

        $p = new Subscribed;
        $p->setTo($jid)
          ->request();

        // TODO : move in Moxl
        $session = Session::start();
        $notifs = $session->get('activenotifs', []);

        unset($notifs[$jid]);

        $session->set('activenotifs', $notifs);
        $n = new Notification;
        $n->ajaxClear('invite|'.$jid);

        Drawer::fill($this->prepareNotifications());
        $this->ajaxSetCounter();
    }

    public function ajaxRefuse($jid)
    {
        $jid = echapJid($jid);

        $p = new Unsubscribed;
        $p->setTo($jid)
          ->request();

        $this->removeInvitation($jid);
    }

    private function removeInvitation($jid)
    {
        // TODO : move in Moxl
        $session = Session::start();
        $notifs = $session->get('activenotifs', []);

        unset($notifs[$jid]);

        $session->set('activenotifs', $notifs);

        $n = new Notification;
        $n->ajaxClear('invite|'.$jid);

        Drawer::fill($this->prepareNotifications());

        $this->ajaxSetCounter();
    }

    /*
     * Create the list of notifications
     * @return string
     */
    private function prepareNotifications()
    {
        $invitations = [];

        $session = Session::start();
        $notifs = $session->get('activenotifs', []);

        foreach ($notifs as $key => $value) {
            array_push($invitations, \App\Contact::firstOrNew(['id' =>$key]));
        }

        $notifs = \App\Post::whereIn('parent_id', function ($query) {
            $query->select('id')
                  ->from('posts')
                  ->where('aid', $this->user->id)
                  ->orderBy('published', 'desc');
        })
        ->orderBy('published', 'desc')
        ->limit(30)
        ->with('parent')
        ->get();

        $since = \App\Cache::c('notifs_since');
        if (!$since) {
            $since = date(MOVIM_SQL_DATE, 0);
        }

        $view = $this->tpl();
        $view->assign('hearth', addEmojis('♥'));
        $view->assign('notifs', $notifs);
        $view->assign('since', $since);
        $view->assign('invitations', array_reverse($invitations));

        return $view->draw('_notifications');
    }
}
