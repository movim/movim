<?php

class Explore extends WidgetCommon {
    function WidgetLoad()
    {
        $this->addcss('explore.css');
    }
    
    function build()
    {
        $users_limit = 10;
       
        $gender = getGender();
        $marital = getMarital();

        $query = Contact::query()->select()
                       ->where(array(
                               'public' => 1))
                       ->orderby('id', true)
                       ->limit(0, $users_limit);
        $users = Contact::run_query($query);
        
        /*$users_number = sizeof($users);
        
        if($users_number < $users_limit) {
            $users_fill = array();
            for($i = 0; $i<$users_limit-$users_number; $i++)
                array_push($users_fill, new Contact());
                
            $users = array_merge($users, $users_fill);
        }*/
        
        //shuffle($users);
    ?>
        <div id="explore">
            <div class="filters">
                <ul>
                    <li class="on""><?php echo t('Last registered');?></li>
                </ul>
            </div>
            <div class="clear"></div>
    <?php
        foreach($users as $user) {
            echo '
                <a href="?q=friend&f='.$user->getData('jid').'">
                    <div class="contactbox">
                        <img class="avatar" src="'.$user->getPhoto('m').'"/>
                        <div class="desc">'.prepareString($user->getData('desc')).'</div>
                        <span class="name">'.$user->getTrueName().'</span>
                        <span class="asv">'.
                            $user->getAge().' '.
                            $gender[$user->getData('gender')].'<br />'.
                            $marital[$user->getData('marital')].'
                        </span>
                    </div>
                </a>';
        } 
    ?>
        </div>
    <?php
    }
}
