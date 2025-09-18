<?php

namespace App\Widgets\Search;

use Movim\Widget\Base;

use Respect\Validation\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Capsule\Manager as DB;

use App\Post;
use App\Contact;
use App\Info;
use App\Widgets\ContactActions\ContactActions;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Post\Post as WidgetPost;
use Moxl\Xec\Action\ExtendedChannelSearch\Search as RoomSearch;

class Search extends Base
{
    public function load()
    {
        $this->addjs('search.js');
        $this->addcss('search.css');
    }

    public function ajaxRequest($chatroomActions = false)
    {
        $view = $this->tpl();

        $view->assign('chatroomactions', $chatroomActions);
        Drawer::fill('search', $view->draw('_search'), true);

        $this->rpc('Search.init');
    }

    public function ajaxHttpInitRoster()
    {
        $view = $this->tpl();
        $view->assign('contacts', $this->me->session->topContacts()
            ->with('presence.capability')
            ->get());

        $this->rpc('MovimTpl.fill', '#roster', $view->draw('_search_roster'));
        $this->rpc('Search.searchCurrent');
    }

    public function prepareSearch(string $key)
    {
        $view = $this->tpl();

        $key = str_replace(['#', 'xmpp:'], '', $key);

        if (Validator::stringType()->length(2, 64)->isValid($key)) {
            $view->assign('posts', new Collection);

            if ($this->me->hasPubsub()) {
                $tags = DB::table('post_tag')
                    ->select(DB::raw('count(*) as count, name'))
                    ->join('tags', 'tag_id', '=', 'tags.id')
                    ->whereIn('tag_id', function ($query) use ($key) {
                        $query->select('id')
                              ->from('tags')
                              ->where('name', 'like', '%' . strtolower($key) . '%');
                    })
                    ->groupBy('name')
                    ->orderBy('count', 'desc')
                    ->take(20)
                    ->get()
                    ->pluck('name', 'count');

                $view->assign('tags', $tags);

                $posts = Post::whereIn('id', function ($query) use ($key) {
                    $query->select('post_id')
                          ->from('post_tag')
                          ->whereIn('tag_id', function ($query) use ($key) {
                            $query->select('id')
                                  ->from('tags')
                                  ->where('name', 'like', '%' . strtolower($key) . '%');
                        });
                })
                ->whereIn('id', function ($query) {
                    $filters = DB::table('posts')->where('id', -1);

                    $filters = \App\Post::withMineScope($filters);
                    $filters = \App\Post::withContactsScope($filters);
                    $filters = \App\Post::withSubscriptionsScope($filters);

                    $query->select('id')->from(
                        $filters,
                        'posts'
                    );
                })
                ->orderBy('published', 'desc')
                ->take(5)
                ->get();

                $view->assign('posts', $posts);
            }

            $contacts = Contact::suggest($key)->limit(10)->get();

            if (validateJid($key)) {
                $contact = new Contact;
                $contact->id = $key;
                $contacts->push($contact);
            }

            $view->assign('contacts', $contacts);

            $communities = Info::whereRaw('lower(node) like ?', '%'.strtolower($key).'%')
                ->whereRaw('lower(node) not like ?', 'urn:xmpp:microblog:0%')
                ->whereCategory('pubsub')
                ->whereType('leaf')
                ->where('pubsubaccessmodel', 'open')
                ->take(5)
                ->get();

            $view->assign('communities', $communities);

            if (!\App\Configuration::get()->restrictsuggestions) {
                $s = new RoomSearch;
                $s->setKeyword($key)
                  ->setMax(5)
                  ->enableGlobalSearch()
                  ->request();
            }

            return $view->draw('_search_results');
        }

        return $this->prepareEmpty();
    }

    public function prepareEmpty()
    {
        $view = $this->tpl();

        $users = Contact::suggest()
            ->limit(12)
            ->get();

        $view->assign('users', $users);

        return $view->draw('_search_results_empty');
    }

    public function ajaxSearch($key)
    {
        $this->rpc('MovimTpl.fill', '#results', $this->prepareSearch($key));
        $this->rpc('Search.searchClear');
    }

    public function ajaxChat(string $jid, bool $muc = false)
    {
        $contact = new ContactActions();
        $contact->ajaxChat($jid, $muc);
    }

    public function prepareTicket(Post $post)
    {
        return (new WidgetPost())->prepareTicket($post);
    }

    public function prepareUsers($users)
    {
        $view = $this->tpl();

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('presences', getPresences());
        $view->assign('users', $users);

        return $view->draw('_search_results_contacts');
    }
}
