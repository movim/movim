<?php

class Explore extends WidgetCommon {
    function WidgetLoad()
    {
        $this->addcss('explore.css');
    }

    function ajaxSearchContacts($form) {
        $html = $this->prepareContacts($form);
        movim_log($html);
        RPC::call('movim_fill', 'contactsresult', RPC::cdata($html));
        RPC::commit();
    }
    
    function colorSearch($search, $text) {
        return str_replace(
                $search, 
                '<span style="background-color: yellow;">'.$search.'</span>',
                $text
                );
    }

    function prepareContacts($form = false) {
        if(!$form){
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
                       ->orderby('id', true)
                       ->limit(0, $users_limit);
        $users = Contact::run_query($query);

        $html = '';
        foreach($users as $user) {
            $html .= '
                <a href="?q=friend&f='.$user->getData('jid').'">
                    <div class="post">
                        <img class="avatar" src="'.$user->getPhoto('m').'"/>
                        <span class="name">'.
                            $this->colorSearch($form['search'], $user->getTrueName()).'
                        </span>
                        <span class="asv">'.
                            $user->getAge().' '.
                            $gender[$user->getData('gender')].' '.
                            $marital[$user->getData('marital')].'
                        </span>
                        <div 
                            class="content"
                            style="
                                overflow: hidden;
                                text-overflow: ellipsis;
                                white-space: nowrap;
                            "
                        >'.prepareString($user->getData('desc')).'</div>
                    </div>

                </a>
                ';
        }

        return $html;
    }

    function build()
    {
    ?>
        <div id="explore">
            <form name="searchform" style="margin: 1em 1.5em;" onsubmit="event.preventDefault();">
                <div class="element large" style="min-height: 0em;">
                    <a
                        class="button icon submit"
                        href="#"
                        onclick="<?php $this->callAjax("ajaxSearchContacts","movim_parse_form('searchform')"); ?> "
                        style="float:right; width: auto; margin-right: 0px;">
                        <?php echo t('Search'); ?>
                    </a>
                    <input
                        id="addjid"
                        class="tiny"
                        name="search"
                        placeholder="<?php echo t('Search a contact'); ?>"
                        style="width:70%;"
                        onkeypress="if(event.keyCode==13){<?php $this->callAjax("ajaxSearchContacts","movim_parse_form('searchform')"); ?>}"
                    />
                </div>
            </form>
            <div class="filters">
                <ul>
                    <li class="on"><?php echo t('Last registered');?></li>
                </ul>
            </div>
            <div class="clear"></div>
            <div id="contactsresult">
                <?php echo $this->prepareContacts(); ?>
            </div>
        </div>
    <?php

    }
}
