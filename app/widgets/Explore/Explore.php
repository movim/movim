<?php

class Explore extends WidgetCommon {
    function WidgetLoad()
    {
        $this->addcss('explore.css');
    }

    function display()
    {
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
        return str_replace(
                $search, 
                '<span style="background-color: yellow;">'.$search.'</span>',
                $text
                );
    }
    
    function prepareServers() {
        $nd = new \modl\ItemDAO();
        
        $groups = $nd->getGroupServers();
        $chatrooms = $nd->getConferenceServers();

        // A little filter
        $i = 0;
        foreach($chatrooms as $c) {
            if(filter_var($c->server, FILTER_VALIDATE_EMAIL))
                unset($chatrooms[$i]);

            if(count(explode('.', $c->server)) < 3)
                unset($chatrooms[$i]);

            $i++;
        }
        
        $html = '';

        $groupsview = $this->tpl();
        $groupsview->assign('groups', $groups);
        $html .= $groupsview->draw('_explore_groups', true);
        
        $chatroomsview = $this->tpl();
        $chatroomsview->assign('chatrooms', $chatrooms);
        $html .= $chatroomsview->draw('_explore_chatrooms', true);
        
        return $html;
    }

    function prepareContacts($form = false) {
        $html = '';
                
        $cd = new \modl\ContactDAO();
        $users = $cd->getAllPublic();

        $gender = getGender();
        $marital = getMarital();

        if($users) {
            $users = array_reverse($users);
            
            foreach($users as $user) {
                $html .= '
                    <article class="block">
                        <header>
                            <a href="'.Route::urlize('friend', $user->jid).'">
                                <img class="avatar" src="'.$user->getPhoto('m').'"/>
                            </a>

                            <span class="name">
                                <a href="'.Route::urlize('friend', $user->jid).'">'.$user->getTrueName().'</a>
                            </span>
                            <span class="asv">'.
                                $user->getAge().' '.
                                $gender[$user->gender].' '.
                                $marital[$user->marital].'
                            </span>
                        </header>

                        <section class="content">'.prepareString($user->description).'</section>

                        <footer></footer>
                    </article>
                    ';
            }
        }

        return $html;
    }
}
