<?php

use Movim\Widget\Base;

use Respect\Validation\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Capsule\Manager as DB;
use Moxl\Xec\Action\IqGateway;
use Moxl\Utils;

use App\Post;
use App\Tag;
use App\Contact;
use App\Info;

use Post as PostWidget;

class Search extends Base
{
    public function load()
    {
        $this->addjs('search.js');
        $this->addcss('search.css');
        $this->registerEvent('iqgateway_set_handle', 'onIqGatewaySet');
    }

    public function foundJid($prompt, $jid) {
        $session = \Movim\Session::start();
        $jids = $session->get('iqgateway_result_'.$prompt, []);
        $jids[''.$jid] = 1;
        $session->set('iqgateway_result_'.$prompt, $jids, 360);
    }

    public function onIqGatewaySet($packet)
    {
        $jid = $packet->content['query']->jid;
        $prompt = $packet->content['prompt'];
        $this->foundJid($prompt, $jid);
        $this->rpc('Search.searchCurrent');
    }

    public function ajaxRequest($chatroomActions = false)
    {
        $view = $this->tpl();

        $view->assign('empty', $this->prepareSearch(''));
        $view->assign('chatroomactions', $chatroomActions);
        Drawer::fill($view->draw('_search'), true);

        $this->rpc('Search.init');
    }

    public function ajaxHttpInitRoster()
    {
        $view = $this->tpl();
        $view->assign('contacts', $this->user->session->topContacts()
            ->with('presence.capability')
            ->get());

        $this->rpc('MovimTpl.fill', '#roster', $view->draw('_search_roster'));
        $this->rpc('Search.searchCurrent');
    }

    public function prepareSearch($key)
    {
        $view = $this->tpl();

        $key = str_replace(['#', 'xmpp:'], '', $key);

        if (Validator::stringType()->length(2, 64)->validate($key)) {
            $view->assign('posts', new Collection);

            if ($this->user->hasPubsub()) {
                $tagIds = Tag::where('name', 'like', '%' . strtolower($key) . '%')->pluck('id');

                $tags = DB::table('post_tag')
                    ->select(DB::raw('count(*) as count, name'))
                    ->join('tags', 'tag_id', '=', 'tags.id')
                    ->whereIn('tag_id', $tagIds)
                    ->groupBy('name')
                    ->orderBy('count', 'desc')
                    ->take(4)
                    ->get()
                    ->pluck('name', 'count');

                $view->assign('tags', $tags);

                $posts = Post::whereIn('id', function ($query) use ($tagIds) {
                    $query->select('post_id')
                          ->from('post_tag')
                          ->whereIn('tag_id', $tagIds);
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

            $contacts = Contact::public($key)
                ->notInRoster($this->user->session->id)
                ->orderByPresence()
                ->where('id', '!=', $this->user->id)
                ->limit(10)
                ->get();

            if (validateJid($key) && strstr($key, '.')) {
                $contact = new Contact;
                $contact->id = $key;
                $contacts->push($contact);
            }

            $session = \Movim\Session::start();
            $jids = $session->get('iqgateway_result_'.$key, []);
            foreach (array_keys($jids) as $jid) {
                $contact = new Contact;
                $contact->id = $jid;
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

            return $view->draw('_search_results');
        }

        return $this->prepareEmpty();
    }

    public function prepareEmpty()
    {
        $view = $this->tpl();

        $users = Contact::public()
            ->notInRoster($this->user->session->id)
            ->orderByPresence()
            ->where('id', '!=', $this->user->id)
            ->limit(10)
            ->get();

        $view->assign('users', $users);

        return $view->draw('_search_results_empty');
    }

    public function ajaxSearch($key)
    {

        $session = \Movim\Session::start();
        if ($key && !$session->has('iqgateway_result_'.$key)) {
            foreach ($this->user->session->contacts()->with('presence.capability')->get() as $contact) {
                if (!$contact || !$contact->presence || !$contact->presence->capability) continue;
                if ($contact->presence->capability->hasFeature("jabber:iq:gateway")) {
                    $r = (new IqGateway\Set)
                        ->setTo($contact->presence->jid . ($contact->presence->resource ? '/' . $contact->presence->resource : ''))
                        ->setPrompt($key)
                        ->request();
                } else if ($contact->presence->capability->getGatewayTypeAttribute()) {
                    if ($contact->presence->capability->hasFeature("jid\\20escaping")) {
                        $this->foundJid($key, Utils::escapeJidLocalpart($key).'@'.$contact->presence->jid);
                    } else {
                        $this->foundJid($key, str_replace('@', '%', $key).'@'.$contact->presence->jid);
                    }
                }
            }
        }

        $this->rpc('MovimTpl.fill', '#results', $this->prepareSearch($key));
        $this->rpc('Search.searchClear');
    }

    public function ajaxChat($jid)
    {
        $contact = new ContactActions;
        $contact->ajaxChat($jid);
    }

    public function prepareTicket(Post $post)
    {
        return (new PostWidget)->prepareTicket($post);
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
