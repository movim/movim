<?php

class Explore extends WidgetCommon {
    function WidgetLoad()
    {
        $this->addcss('explore.css');
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
        $nd = new \modl\NodeDAO();
        
        $servers = $nd->getServers();
        
        $html = '<ul class="list">';

        foreach($servers as $s) {
            $html .= '
                <li>
                    <a href="'.Route::urlize('server', $s->serverid).'">'.
                        $s->serverid. ' 
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
        $cd = new \modl\ContactDAO();
        $users = array_reverse($cd->getAllPublic());
        
        $gender = getGender();
        $marital = getMarital();
                
        foreach($users as $user) {
            $html .= '
                <div class="post">
                    <a href="?q=friend&f='.$user->jid.'">
                        <img class="avatar" src="'.$user->getPhoto('m').'"/>
                        <div class="postbubble">
                            <span class="name">'.
                                $user->getTrueName().'
                            </span>
                            <span class="asv">'.
                                $user->getAge().' '.
                                $gender[$user->gender].' '.
                                $marital[$user->marital].'
                            </span>
                            <div 
                                class="content"
                                style="
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                    white-space: nowrap;
                                    height: 1.5em;
                                "
                            >'.prepareString($user->desc).'</div>
                        </div>
                    </a>
                </div>
                ';
        }

        return $html;
    }

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
            
            <div id="serverresult" class="paddedtop">
                <h2><?php echo t('Discussion Servers'); ?></h2>
                <?php echo $this->prepareServers(); ?>
            </div>

            <div class="paddedtopbottom">
            <h2><?php echo t('Last registered'); ?></h2>
            </div>
            <div id="contactsresult">
                <?php echo $this->prepareContacts(); ?>
            </div>
        </div>
    <?php

    }
}
