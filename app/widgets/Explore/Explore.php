<?php

class Explore extends WidgetCommon {
    function WidgetLoad()
    {
        $this->addcss('explore.css');
        
        $this->view->assign('contacts', $this->prepareContacts());
        $this->view->assign('servers', $this->prepareServers());
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

        foreach($servers as $s) {
            list($type) = explode('.', $s->server);
            
            switch ($type) {
                case 'conference':
                    $cat = '<span class="tag green">'.t('Chatrooms').'</span>';
                    break;
                case 'muc':
                    $cat = '<span class="tag green">'.t('Chatrooms').'</span>';                
                    break;
                case 'discussion':
                    $cat = '<span class="tag green">'.t('Chatrooms').'</span>';
                    break;
                case 'pubsub':
                    $cat = '<span class="tag orange">'.t('Groups').'</span>';
                    break;
                default:
                    $cat = '';
                    break;
            }
            
            $html .= '
                <li>
                    <a href="'.Route::urlize('server', $s->server).'">'.
                        $cat.
                        $s->server. ' 
                        <span class="tag">'.$s->number.'</span>
                    </a>
                </li>';
        }

        $html .= '</ul>';
        
        return $html;
        //var_dump($nd->getServers());
    }

    function prepareContacts($form = false) {
        /*if(!$form){
            $where = array('public' => 1);
        }
        else{
            $where = array(
                'public' => 1, 
                array(
                    'fn%' => '%'.$form['search'].'%',
                    '|jid%' => '%'.$form['search'].'%',
                    '|name%' => '%'.$form['search'].'%',
                    '|email%' => '%'.$form['search'].'%',
                    '|nickname%' => '%'.$form['search'].'%'
                )
            );
        }
        $users_limit = 20;

        $gender = getGender();
        $marital = getMarital();

        $query = Contact::query()->select()
                       ->where($where)
                       //s->orderby('id', true)
                       ->limit(0, $users_limit);
        $users = Contact::run_query($query);

        $html = '
                <div class="posthead">
                    <!--<ul class="filters">
                        <li class="on">'.t('Last registered').'</li>
                    </ul>-->
   
                    <div class="clear"></div>
                </div>';*/
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

    /*
    function build()
    {
    ?>
        <div id="explore">
            <!--<form name="searchform" style="margin: 1em 1.5em;" onsubmit="event.preventDefault();">
                <div class="element" style="min-height: 0em;">
                    <input
                        id="addjid"
                        class="tiny"
                        name="search"
                        placeholder="<?php echo t('Search a contact'); ?>"
                        onkeypress="if(event.keyCode==13){<?php $this->callAjax("ajaxSearchContacts","movim_parse_form('searchform')"); ?>}"
                    />
                </div>
                <div class="element" style="min-height: 0em; margin-top: 5px;">
                    <a
                        class="button icon submit"
                        href="#"
                        onclick="<?php $this->callAjax("ajaxSearchContacts","movim_parse_form('searchform')"); ?> "
                        style="">
                        <?php echo t('Search'); ?>
                    </a>
                </div>
            </form>-->
    <?php

    }
    */
}
