<?php

use Moxl\Xec\Action\OMEMO\DeleteBundle;
use Moxl\Xec\Action\Register\ChangePassword;
use Moxl\Xec\Action\Register\Remove;
use Moxl\Xec\Action\Register\Get;
use Moxl\Xec\Action\Register\Set;
use Respect\Validation\Validator;

class Account extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('account.js');
        $this->registerEvent('register_changepassword_handle', 'onPasswordChanged');
        $this->registerEvent('register_remove_handle', 'onRemoved');
        $this->registerEvent('register_remove_error', 'onRegisterError');
        $this->registerEvent('register_get_handle', 'onRegister', 'account');
        $this->registerEvent('register_get_error', 'onRegisterError', 'account');
        $this->registerEvent('register_get_errorfeaturenotimplemented', 'onRegisterError', 'account');
        $this->registerEvent('register_set_handle', 'onRegistered', 'account');
        $this->registerEvent('register_set_error', 'onRegisterError', 'account');
        $this->registerEvent('omemo_setdevicelist_handle', 'onDeviceList', 'account');
    }

    public function onDeviceList()
    {
        $this->rpc('Account.refreshFingerprints');
    }

    public function onPasswordChanged()
    {
        $this->rpc('Account.resetPassword');
        Toast::send($this->__('account.password_changed'));

        $this->rpc('Presence_ajaxLogout');
    }

    public function onRemoved()
    {
        $this->user->messages()->delete();
        \App\Post::restrictToMicroblog()->where('server', $this->user->id)->delete();
        $this->rpc('Presence_ajaxLogout');
    }

    public function onRegistered()
    {
        $this->rpc('MovimTpl.fill', '#account_gateways', $this->prepareGateways());
        Toast::send($this->__('client.registered'));
    }

    public function onRegister($package)
    {
        $content = $package->content;

        $view = $this->tpl();

        if (isset($content->x)) {
            $xml = new \XMPPtoForm;
            $form = $xml->getHTML($content->x);

            $view->assign('form', $form);
            $view->assign('from', $package->from);
            $view->assign('attributes', $content->attributes());
            $view->assign('actions', null);
            if (isset($content->actions)) {
                $view->assign('actions', $content->actions);
            }

            Dialog::fill($view->draw('_account_form'), true);
        }
    }

    public function onRegisterError($packet)
    {
        Toast::send(
            $packet->content ??
            $this->__('error.oops')
        );
    }

    public function ajaxChangePassword()
    {
        $view = $this->tpl();
        $view->assign('jid', $this->user->id);
        Dialog::fill($view->draw('_account_password'));
    }

    public function ajaxChangePasswordConfirm($form)
    {
        $validate = Validator::stringType()->length(6, 40);
        $p1 = $form->password->value;
        $p2 = $form->password_confirmation->value;

        if ($validate->validate($p1)
        && $validate->validate($p2)) {
            if ($p1 == $p2) {
                $arr = explodeJid($this->user->id);

                $this->rpc('Dialog_ajaxClear');

                $cp = new ChangePassword;
                $cp->setTo($arr['server'])
                   ->setUsername($arr['username'])
                   ->setPassword($p1)
                   ->request();
            } else {
                Toast::send($this->__('account.password_not_same'));
                $this->rpc('Account.resetPassword');
            }
        } else {
            Toast::send($this->__('account.password_not_valid'));
            $this->rpc('Account.resetPassword');
        }
    }

    public function ajaxRemoveAccount()
    {
        $this->rpc('Presence.clearQuick');
        $view = $this->tpl();
        $view->assign('jid', $this->user->id);
        Dialog::fill($view->draw('_account_remove'));
    }

    public function ajaxClearAccount()
    {
        $view = $this->tpl();
        $view->assign('jid', $this->user->id);
        Dialog::fill($view->draw('_account_clear'));
    }

    public function ajaxClearAccountConfirm()
    {
        $this->onRemoved();
    }

    public function ajaxHttpGetPresences()
    {
        $view = $this->tpl();

        $presences = $this->user->session->ownPresences;

        if ($presences->count() > 0) {
            $view->assign('presences', $presences);
            $view->assign('clienttype', getClientTypes());

            $this->rpc('MovimTpl.fill',
                '#account_presences',
                $view->draw('_account_presences')
            );
        }
    }

    public function ajaxHttpGetFingerprints($identity, array $resolvedDeviceIds)
    {
        $view = $this->tpl();

        $fingerprints = $this->user->bundles()->where('jid', $this->user->id)->get();

        foreach($fingerprints as $fingerprint) {
            $fingerprint->self = ($fingerprint->identitykey == $identity);
            $fingerprint->built = in_array($fingerprint->bundleid, $resolvedDeviceIds);
        }

        $fingerprints = $fingerprints->sortByDesc('self')->keyBy('bundleid');

        $latests = \App\Message::selectRaw('max(published) as latest, bundleid')
                               ->where('user_id', $this->user->id)
                               ->where('jidfrom', $this->user->id)
                               ->groupBy('bundleid')
                               ->pluck('latest', 'bundleid');

        foreach ($fingerprints->keys() as $key) {
            $fingerprints[$key]->latest = $latests->has($key)
                ? $latests[$key]
                : null;
        }

        $view->assign('fingerprints', $fingerprints);

        $this->rpc('MovimTpl.fill',
            '#account_fingerprints',
            $view->draw('_account_fingerprints')
        );
        $this->rpc('Account.resolveSessionsStates');
    }

    public function ajaxDeleteBundleConfirm(int $id)
    {
        $view = $this->tpl();
        $view->assign('bundle', $this->user->bundles()
                                           ->where('jid', $this->user->id)
                                           ->where('bundleid', $id)
                                           ->first());
        Dialog::fill($view->draw('_account_delete_bundle'));
    }

    public function ajaxDeleteBundle(int $id)
    {
        $db = new DeleteBundle;
        $db->setId($id)
           ->request();
    }

    public function ajaxRemoveAccountConfirm($form)
    {
        if ($form->jid->value == $this->user->id) {
            $da = new Remove;
            $da->request();
        } else {
            Toast::send($this->__('account.delete_text_error'));
        }
    }

    public function ajaxGetRegistration($server)
    {
        if (!validateServer($server)) {
            return;
        }

        $da = new Get;
        $da->setTo($server)
           ->request();
    }

    public function ajaxRegister($server, $form)
    {
        if (!validateServer($server)) {
            return;
        }
        $s = new Set;
        $s->setTo($server)
          ->setData($form)
          ->request();
    }

    public function prepareGateways()
    {
        $view = $this->tpl();
        $view->assign(
            'gateways',
            \App\Info::where('parent', $this->user->session->host)
                     ->whereCategory('gateway')
                     ->get()
        );

        return $view->draw('_account_gateways');
    }

    public function display()
    {
        $this->view->assign('gateways', $this->prepareGateways());
    }
}
