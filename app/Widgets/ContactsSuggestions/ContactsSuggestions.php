<?php

namespace App\Widgets\ContactsSuggestions;

use App\Contact;
use App\Post;
use App\Widgets\ContactHeader\ContactHeader;
use Carbon\Carbon;
use Movim\Widget\Base;
use Moxl\Xec\Payload\Packet;

class ContactsSuggestions extends Base
{
    public function load()
    {
        $this->registerEvent('pubsubsubscription_add_handle', 'onSubscription', 'news');
        $this->registerEvent('pubsubsubscription_remove_handle', 'onSubscription', 'news');

        $this->addjs('contactssuggestions.js');
    }

    public function onSubscription(Packet $packet)
    {
        list($jid, $node) = array_values($packet->content);
        if ($node == Post::MICROBLOG_NODE) {
            $this->ajaxHttpGet();
        }
    }

    public function ajaxHttpGet()
    {
        $this->rpc('MovimTpl.fill', '#contactssuggestions_widget', $this->prepareContactsSuggestions(''));
    }

    public function prepareContactsSuggestions(?string $disposition = 'fourth', ?int $take = 4)
    {
        $view = $this->tpl();
        $view->assign('disposition', $disposition);
        $view->assign('contacts', Contact::whereIn('id', function ($query) {
            $query->select('server')
                ->from('posts')
                ->where('node', Post::MICROBLOG_NODE)
                ->whereIn('server', function ($query) {
                    $query->select('jid')
                        ->from('rosters')
                        ->where('session_id', function ($query) {
                            $query->select('id')
                                ->from('sessions')
                                ->where('user_id', $this->me->id);
                        });
                })
                ->whereNotIn('server', function ($query) {
                    $query->select('server')
                        ->from('subscriptions')
                        ->where('jid', $this->me->id)
                        ->where('node', Post::MICROBLOG_NODE);
                })
                ->where('published', '>', Carbon::now()->subMonths(6));
        })->inRandomOrder()->take($take)->get());

        return $view->draw('_contactssuggestions');
    }

    public function ajaxSubscribe(string $jid)
    {
        (new ContactHeader($this->me))->ajaxSubscribe($jid);
    }
}
