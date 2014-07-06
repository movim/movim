<?php
class Explore extends WidgetCommon {

    function load() {
        $this->addcss('explore.css');
    }

    function display() {
        $this->view->assign('contacts', $this->prepareContacts());
        $this->view->assign('servers', $this->prepareServers());
        $jid = $this->user->getLogin();
        $server = explode('@', $jid);
        $this->view->assign('myserver', Route::urlize('server', $server[1]));
    }

    function ajaxSearchContacts($form) {
        $html = $this->prepareContacts($form);
        RPC::call('movim_fill', 'contactsresult', $html);
        RPC::commit();
    }

    function colorSearch($search, $text) {
        return str_replace($search, '<span style="background-color: yellow;">' . $search . '</span>', $text);
    }

    // A little filter
    private function cleanServers($servers) {
        $i = 0;
        foreach($servers as $c) {
            if(filter_var($c->server, FILTER_VALIDATE_EMAIL)) {
                unset($servers[$i]);
            } elseif(count(explode('.', $c->server))<3) {
                unset($servers[$i]);
            }
            $i++;
        }
        return $servers;
    }

    function prepareServers() {
        $nd = new \modl\ItemDAO();
        
        $groups = $this->cleanServers($nd->getGroupServers());
        $chatrooms = $this->cleanServers($nd->getConferenceServers());
        
        $html = '';
        
        $groupsview = $this->tpl();
        $groupsview->assign('groups', $groups);
        $html .= $groupsview->draw('_explore_groups', true);
        
        //$chatroomsview = $this->tpl();
        //$chatroomsview->assign('chatrooms', $chatrooms);
        //$html .= $chatroomsview->draw('_explore_chatrooms', true);
        
        return $html;
    }

    function prepareContacts($form=false) {
        $html = '';
        $cd = new \modl\ContactDAO();
        $users = $cd->getAllPublic();
        
        $gender = getGender();
        $marital = getMarital();

        if($users) {
            $users = array_reverse($users);
            foreach($users as $user) {
                $g = $m = null;

                if($user->gender != null && $user->gender != 'N') {
                    $g = $gender[$user->gender];
                }

                if($user->marital != null && $user->marital != 'none') {
                    $m = $marital[$user->marital];
                }
                
                $userview = $this->tpl();
                $userview->assign('user', $user);
                $userview->assign('gender', $g);
                $userview->assign('marital', $m);
                $html .= $userview->draw('_explore_user', true);

            }
        }
        return $html;
    }
}
