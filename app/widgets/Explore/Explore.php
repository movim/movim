<?php
class Explore extends WidgetCommon {

    function load() {

    }

    function display() {
        $this->view->assign('servers', $this->prepareServers());
        
        $jid = $this->user->getLogin();
        $server = explode('@', $jid);
        $this->view->assign('myserver', Route::urlize('server', $server[1]));

        $cd = new \modl\ContactDAO();
        $users = $cd->getAllPublic();
        $this->view->assign('users', array_reverse($users));
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
}
