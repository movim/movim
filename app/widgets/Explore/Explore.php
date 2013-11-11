<?php

class Explore extends WidgetCommon {
    function WidgetLoad()
    {
        $this->addcss('explore.css');
        
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
        
        $servers = $nd->getServers();
        
        $html = '<ul class="list">';

        $chatrooms = '';
        $pubsubs = '';

        foreach($servers as $s) {
            list($type) = explode('.', $s->server);
            
            switch ($type) {
                case 'conference':
                    $cat = 'chatroom';
                    break;
                case 'muc':
                    $cat = 'chatroom';            
                    break;
                case 'discussion':
                    $cat = 'chatroom';
                    break;
                case 'pubsub':
                    $cat = 'pubsub';
                    break;
                default:
                    $cat = null;
                    break;
            }

            if(!filter_var($s->server, FILTER_VALIDATE_EMAIL) && isset($cat)) {
                if($cat == 'chatroom') {
                    $chatrooms .= '
                        <li>
                            <a href="'.Route::urlize('server', $s->server).'">
                                <span class="tag green">'.t('Chatrooms').'</span>'.
                                $s->server. ' 
                                <span class="tag">'.$s->number.'</span>
                            </a>
                        </li>';
                } elseif($cat == 'pubsub') {
                    $pubsubs .= '
                        <li>
                            <a href="'.Route::urlize('server', $s->server).'">
                                <span class="tag orange">'.t('Groups').'</span>'.
                                $s->server. ' 
                                <span class="tag">'.$s->number.'</span>
                            </a>
                        </li>';
                }
            }
        }

        $html .= $pubsubs.$chatrooms;

        $html .= '</ul>';
        
        return $html;
    }

    function prepareContacts($form = false) {
        $html = '';
                
        $cd = new \modl\ContactDAO();
        $users = array_reverse($cd->getAllPublic());
        
        $gender = getGender();
        $marital = getMarital();
                
        foreach($users as $user) {
            $html .= '
                <div class="post">
                    <a href="'.Route::urlize('friend', $user->jid).'">
                        <img class="avatar" src="'.$user->getPhoto('m').'"/>
                    </a>
                    <div class="postbubble profile">
                        <span class="name">
                            <a href="'.Route::urlize('friend', $user->jid).'">'.$user->getTrueName().'</a>
                        </span>
                        <span class="asv">'.
                            $user->getAge().' '.
                            $gender[$user->gender].' '.
                            $marital[$user->marital].'
                        </span>
                        <div class="content">'.prepareString($user->description).'</div>
                    </div>
                    
                </div>
                ';
        }

        return $html;
    }
}
