<?php

namespace App\Widgets\ContactDataPublic;

use App\Widgets\ContactData\ContactData;
use App\Widgets\ContactSubscriptions\ContactSubscriptions;
use Movim\Widget\Base;

class ContactDataPublic extends Base
{
    public function prepareCard($contact)
    {
        return (new ContactData($this->me))->prepareCard($contact);
    }

    public function prepareSubscriptions($jid)
    {
        return (new ContactSubscriptions($this->me))->prepareSubscriptions($jid);
    }

    public function display()
    {
        $jid = $this->get('f');

        $user = \App\User::where('nickname', $jid)->first();
        if ($user) {
            $jid = $user->id;
        }

        $this->view->assign('contact', \App\Contact::firstOrNew(['id' => $jid]));
        $this->view->assign('jid', $jid);
    }
}
