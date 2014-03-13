<?php

class Discover extends WidgetCommon {
    function load()
    {

    }

    function prepareContacts() {
        $html = '';
                
        $cd = new \modl\ContactDAO();
        $users = $cd->getAllPublic();
        if(isset($users)) {
            $users = array_reverse($users);
            
            $gender = getGender();
            $marital = getMarital();
                    
            foreach($users as $user) {
                $html .= '
                    <article class="block">
                        <header>
                            <a href="'.Route::urlize('blog', array($user->jid, 'urn:xmpp:microblog:0')).'">
                                <img class="avatar" src="'.$user->getPhoto('m').'"/>
                            </a>
                            <span class="name">
                                <a href="'.Route::urlize('blog', array($user->jid, 'urn:xmpp:microblog:0')).'">'.$user->getTrueName().'</a>
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

    function build()
    {
    ?>
        <div id="discover">
            <h1><?php echo t('Last registered'); ?></h1>
            <div class="paddedtopbottom">
            </div>
            <div id="contactsresult">
                <?php echo $this->prepareContacts(); ?>
            </div>
        </div>
    <?php

    }
}
