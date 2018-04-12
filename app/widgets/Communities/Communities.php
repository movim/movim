<?php

use Respect\Validation\Validator;
use App\Configuration;

class Communities extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('communities.js');
        $this->addcss('communities.css');
    }

    function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#communities', $this->prepareCommunities());
    }

    function prepareCommunities()
    {
        $view = $this->tpl();
        /*$view->assign('communities', $id->getItems(
            false,
            0,
            40,
            true, (Configuration::findOrNew(1)->restrictsuggestions)
                ? $this->user->getServer()
                : false
        ));*/
        $communities = \App\Post::select('server', 'node', 'published')
            ->groupBy('server', 'node', 'published')
            ->orderBy('published', 'desc')
            ->take(40)
            ->get();

        $posts = [];

        foreach ($communities as $community) {
            array_push($posts, \App\Post::where('server', $community->server)
                                        ->where('node', $community->node)
                                        ->where('open', true)
                                        ->orderBy('published', 'desc')
                                        ->first());
        }

        $view->assign('posts', $posts);

        return $view->draw('_communities', true);
    }

    public function prepareTicket(\App\Post $post)
    {
        return (new Post)->prepareTicket($post);
    }
}
