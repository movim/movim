<?php

namespace App\Widgets\Notifications;

use App\User;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Notif\Notif;
use Moxl\Xec\Action\Presence\Subscribed;
use Moxl\Xec\Action\Presence\Unsubscribed;
use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Presence\Subscribe;

use Movim\Widget\Base;
use Movim\Session;
use Moxl\Xec\Action\Presence\Unsubscribe;
use Moxl\Xec\Action\Roster\RemoveItem;

class Notifications extends Base
{
    public function load()
    {
        $this->addjs('notifications.js');
        $this->addcss('notifications.css');

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
            $contact = \App\Contact::find($from);

            // Don't notify if the contact is not in stored already, for spam reasons
            if ($contact) {
                Notif::append(
                    'invite|' . $from,
                    $contact->truename,
                    $this->__('invitations.wants_to_talk', $contact->truename),
                    $contact->getPicture(),
                    4
                );
            }
        }

        $this->ajaxSetCounter();
    }

    public function ajaxRequest()
    {
        Drawer::fill('notifications', $this->prepareNotifications());

        $this->user->notifications_since = date(MOVIM_SQL_DATE);
        $this->user->save();

        $this->ajaxSetCounter();
        (new Notif)->ajaxClear('comments');
    }

    public function ajaxSetCounter()
    {
        $since = User::me(true)->notifications_since ?? date(MOVIM_SQL_DATE, 0);

        $count = \App\Post::whereIn('parent_id', function ($query) {
            $query->select('id')
                ->from('posts')
                ->where('aid', $this->user->id);
        })->where('published', '>', $since)
            ->where('aid', '!=', $this->user->id)->count();

        $count += $this->user->session->presences()
                        ->whereIn('type', ['subscribe', 'subscribed'])
                        ->count();

        $this->rpc('Notifications.setCounters', ($count > 0) ? $count : '');
    }

    public function ajaxAddAsk($jid)
    {
        $view = $this->tpl();
        $view->assign('contact', \App\Contact::firstOrNew(['id' => $jid]));
        $view->assign('groups', $this->user->session->contacts()
                                                    ->select('group')
                                                    ->whereNotNull('group')
                                                    ->distinct()
                                                    ->pluck('group'));

        Dialog::fill($view->draw('_notifications_add'));
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

    public function ajaxDeleteContact($jid)
    {
        if (!validateJid($jid)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('jid', $jid);

        Dialog::fill($view->draw('_notifications_delete'));
    }

    public function ajaxDelete(string $jid)
    {
        $r = new RemoveItem;
        $r->setTo($jid)
          ->request();

        $p = new Unsubscribe;
        $p->setTo($jid)
          ->request();
    }

    public function ajaxAccept(string $jid)
    {
        $jid = echapJid($jid);

        $roster = $this->user->session->contacts()->where('jid', $jid)->first();

        $this->user->session->presences()
             ->whereIn('type', ['subscribe', 'subscribed'])
             ->where('jid', $jid)
             ->delete();

        if (!$roster) {
            $r = new AddItem;
            $r->setTo($jid)
              ->request();
        }

        if (!$roster || $roster->subscription == 'none' || $roster->subscription == 'from') {
            $p = new Subscribe;
            $p->setTo($jid)
              ->request();
        }

        $p = new Subscribed;
        $p->setTo($jid)
          ->request();

        $this->removeInvitation($jid);
    }

    public function ajaxRefuse(string $jid)
    {
        $jid = echapJid($jid);

        if ($this->user->session->contacts()->where('jid', $jid)->exists()) {
            $r = new RemoveItem;
            $r->setTo($jid)
                ->request();
        }

        $p = new Unsubscribed;
        $p->setTo($jid)
            ->request();

        $this->user->session->presences()->where('jid', $jid)->delete();

        $this->removeInvitation($jid);
    }

    private function removeInvitation(string $jid)
    {
        $n = new Notif;
        $n->ajaxClear('invite|' . $jid);

        $this->rpc('MovimTpl.remove', '#invitation-' . cleanupId($jid));
        $this->ajaxSetCounter();
    }

    /*
     * Create the list of notifications
     * @return string
     */
    private function prepareNotifications()
    {
        $notifs = \App\Post::whereIn('parent_id', function ($query) {
            $query->select('id')
                ->from('posts')
                ->where('aid', $this->user->id)
                ->orderBy('published', 'desc');
        })
            ->where('aid', '!=', $this->user->id)
            ->orderBy('published', 'desc')
            ->limit(30)
            ->with('parent')
            ->get();

        $since = User::me(true)->notifications_since ?? date(MOVIM_SQL_DATE, 0);

        $view = $this->tpl();
        $view->assign('hearth', addEmojis('♥'));
        $view->assign('notifs', $notifs);
        $view->assign('subscriptionRoster', $this->user->session->contacts()
                                     ->where('subscription' , 'none')
                                     ->orderBy('ask', 'desc')
                                     ->get());
        $view->assign('subscribePresences', $this->user->session->presences()
                                                 ->with('contact')
                                                 ->whereIn('type', ['subscribe', 'subscribed'])
                                                 ->get());
        $view->assign('since', $since);

        return $view->draw('_notifications');
    }
}
