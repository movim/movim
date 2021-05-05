<?php

use Movim\Widget\Base;

include_once WIDGETS_PATH.'ContactData/ContactData.php';

class ContactDataPublic extends Base
{
    public function prepareCard($contact)
    {
        return (new ContactData)->prepareCard($contact);
    }

    public function prepareSubscriptions($subscriptions)
    {
        return (new ContactData)->prepareSubscriptions($subscriptions);
    }

    public function display()
    {
        $jid = $this->get('f');

        $user = \App\User::where('nickname', $jid)->first();
        if ($user) {
            $jid = $user->id;
        }

        $this->view->assign('contact', App\Contact::firstOrNew(['id' => $jid]));
        $this->view->assign('subscriptions', \App\Subscription::where('jid', $jid)
                                                              ->where('public', true)
                                                              ->get());
        $this->view->assign('jid', $jid);
    }
}
