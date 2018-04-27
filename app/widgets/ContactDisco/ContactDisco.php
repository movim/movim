<?php

use Illuminate\Database\Capsule\Manager as DB;

class ContactDisco extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('contactdisco.js');
    }

    public function ajaxGet()
    {
        $this->rpc('MovimTpl.fill', '#contactdisco', $this->prepareContacts());
    }

    public function prepareContacts()
    {
        $view = $this->tpl();

        $users = \App\Contact::whereIn('id', function ($query) {
            $query->select('id')
                  ->from('users')
                  ->where('public', true);
        })
        ->join(DB::raw('(
            select min(value) as value, jid
            from presences
            group by jid) as presences
            '), 'presences.jid', '=', 'contacts.id')
        ->whereNotIn('id', function ($query) {
            $query->select('jid')
                  ->from('rosters')
                  ->where('session_id', $this->user->session->id);
        })
        ->where('id', '!=', $this->user->id)
        ->orderBy('presences.value')
        ->limit(40)->get();

        $view->assign('presencestxt', getPresencesTxt());
        $view->assign('users', $users);

        return $view->draw('_contactdisco', true);
    }
}
