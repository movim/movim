<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH.'ContactData/ContactData.php';
include_once WIDGETS_PATH.'ContactSubscriptions/ContactSubscriptions.php';

class ContactDataPublic extends Base
{
    public function prepareCard($contact)
    {
        return (new ContactData)->prepareCard($contact);
    }

    public function prepareSubscriptions($jid)
    {
        return (new ContactSubscriptions)->prepareSubscriptions($jid);
    }

    public function display()
    {
        $jid = $this->get('f');

        $user = \App\User::where('nickname', $jid)->first();
        if ($user) {
            $jid = $user->id;
        } else {
            $user = \App\User::where('id', $jid)->first();
        }

        $this->view->assign('public', ($user && (bool)$user->public));
        $this->view->assign('contact', App\Contact::firstOrNew(['id' => $jid]));
        $this->view->assign('jid', $jid);
    }
}
