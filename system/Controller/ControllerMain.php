<?php
/**
 * @file ControllerMain.php
 * This file is part of MOVIM.
 *
 * @brief Handles incoming static pages requests.
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 21 October 2010
 *
 * Copyright (C)2010 MOVIM Project
 *
 * See COPYING for licensing deatils.
 */

class ControllerMain extends ControllerBase
{
	protected $default_handler = 'mainPage';
	private $page;

	function __construct()
	{
		parent::__construct();

		$this->page = new TplPageBuilder();
        $this->page->addScript('hash.js');
        $this->page->addScript('movimrpc.js');
		$this->page->addScript('movim.js');
	}

	function mainPage()
	{
		$user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			$this->page->setTitle(t('%s - Welcome to Movim', APP_TITLE));
            $this->page->menuAddLink(t('Home'), '?q=mainPage', true);
			$this->page->menuAddLink(t('Configuration'), '?q=config');

			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('main.tpl'));
			echo $this->page->build('page.tpl');
		}
	}

	function friend()
	{
		$user = new User();
		if(!$user->isLogged()) {
			$this->login();
		} else {
			if(isset($_GET['f']) && $_GET['f'] != "" ) {
				$this->page->setTitle(t('%s - Welcome to Movim', APP_TITLE));
                $this->page->menuAddLink(t('Home'), '?q=mainPage');
                
                $sess = Session::start(APP_NAME);
                $sess->set('currentcontact', $_GET['f']);

                global $sdb;
                $user = new User();
                $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $_GET['f']));
                if(isset($contact[0]))
                    $name = $contact[0]->getTrueName();
                else
                    $name = $_GET['f'];

				$this->page->menuAddLink($name, false, true);

				$this->page->menuAddLink(t('Configuration'), '?q=config');
				$content = new TplPageBuilder($user);

				$this->page->setContent($content->build('friend.tpl'));
				echo $this->page->build('page.tpl');
			}
			else
				$this->mainPage();
		}
	}

	function config()
	{
		$user = new User();

		if(!$user->isLogged()) {
			$this->login();
		} else {
			$this->page->setTitle(t('%s - Configuration', APP_TITLE));
            $this->page->menuAddLink(t('Home'), '?q=mainPage');
			$this->page->menuAddLink(t('Configuration'), '?q=config', true);

			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('config.tpl'));
			echo $this->page->build('page.tpl');
		}
	}

	function account()
	{
		if(Conf::getServerConfElement("accountCreation") == 1) {
			$this->page->setTitle(t('%s - Account Creation', APP_TITLE));
            $this->page->menuAddLink(t('Home'), '?q=mainPage', true);
			$content = new TplPageBuilder($user);

			$this->page->setContent($content->build('account.tpl'));
			echo $this->page->build('page.tpl');

		} else {
			$this->login();
		}
	}

	/**
	 * Show login interface (hard-coded).
	 */
	function login()
	{
		$this->page->setTitle(t('%s - Login to Movim', APP_TITLE));
		$this->page->menuAddLink('Movim | Human Network', 'http://www.movim.eu/');
		if(Conf::getServerConfElement("accountCreation") == 1)
			$this->page->menuAddLink(t('Account Creation'), '?q=account');

		switch ($_GET['err']) {
            case 'noaccount':
			    $warning = '
			            <div class="warning">
			                '.t('Wrong username').'
			            </div> ';
                break;
            case 'invalidjid':
			    $warning = '
			            <div class="warning">
			                '.t('Invalid JID').'
			            </div> ';
                break;
            case 'failauth':
			    $warning = '
			            <div class="warning">
			                '.t('The XMPP authentification failed').'
			            </div> ';
                break;
            case 'bosherror':
			    $warning = '
			            <div class="warning">
			                '.t('The current BOSH URL in invalid').'
			            </div> ';
                break;
            case 'internal':
			    $warning = '
			            <div class="error">
			                '.t('Internal server error').'
			            </div> ';
                break;
            case 'session':
			    $warning = '
			            <div class="error">
			                '.t('Session error').'
			            </div> ';
                break;
        }

        if(!BROWSER_COMP)
            $browser_comp = '
			            <div class="warning">
			                '.t('Your web browser is too old to use with Movim.').'
			            </div> ';

		$serverconf = Conf::getServerConf();
		ob_start();
		?>
		<noscript>
            <style type="text/css">
                #loginpage {display:none;}
            </style>
            <div class="warning">
            <?php echo t("You don't have javascript enabled.  Good luck with that."); ?>
            </div>
        </noscript>
		    <div id="loginpage">
		        <?php echo $browser_comp; 
		        if(file_exists(BASE_PATH.'install/part1.php')) { ?>
                    <div class="warning">
                    <?php echo t('Please remove the %s folder in order to complete the installation', 'install/'); ?>
                    </div>
                <?php
                }
		        ?>

		        <div id="quote">
                    <blockquote>
                    He say I know you, you know me<br />
                    One thing I can tell you is<br />
                    You got to be free<br /><br />

                    Come together, right now<br />
                    Over me<br />
                    </blockquote>
                <cite>
                    <a href="http://wikipedia.org/wiki/Lennon/McCartney">John Lennon & Paul McCartney</a>, in
                    <a href="http://wikipedia.org/wiki/Come_Together">"Come Together"</a> on
                    <a href="http://wikipedia.org/wiki/Abbey_Road_(album)">Abbey Road</a> by
                    <a href="http://wikipedia.org/wiki/The_Beatles">The Beatles</a>
                </cite>

		        </div>
			    <form id="connectform" action="index.php" method="post">
		            <?php echo $warning; ?>
			        <input type="email" name="login" id="login" autofocus
			            value="<?php echo t("My address"); ?>" onfocus="myFocus(this);" onblur="myBlur(this);"/><br />
			        <input type="password" name="pass" id="pass"
			            value="<?php echo t("Password"); ?> "  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />

			        <a href="#" class="showoptions" onclick="getElementById('options').style.display = 'block';"><?php echo t('Options'); ?></a>

                    <fieldset id="options" style="display: none;">
			            <label class="tiny"><?php echo t('First Login'); ?></label>
			                <input type="checkbox" class="tiny" name="create" id="create"><br />
			                <hr />

			            <label class="tiny"><?php echo t('Bosh Host'); ?></label>
			                <input type="text" class="tiny" name="host" id="host"
			                    value="<?php echo $serverconf['defBoshHost']; ?>"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />

			            <label class="tiny"><?php echo t('Bosh Suffix'); ?></label>
			                <input type="text" class="tiny" name="suffix" id="suffix"
			                    value="<?php echo $serverconf['defBoshSuffix']; ?>"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />

			            <label class="tiny"><?php echo t('Bosh Port'); ?></label>
			                <input type="text" class="tiny" name="port" id="port"
			                    value="<?php echo $serverconf['defBoshPort']; ?>"  onfocus="myFocus(this);" onblur="myBlur(this);"/><br />
			        </fieldset>
			        <input onclick="this.value = '<?php echo t('Connecting...');?>'; this.className='button icon loading'"  type="submit" name="submit" value="<?php echo t("Come in!"); ?>"/>
			    </form>
			</div>
	    <?php
		$this->page->setContent(ob_get_contents());
        ob_end_clean();
		echo $this->page->build('page.tpl');
	}

	function disconnect()
	{
		$user = new User();
		$user->desauth();
		$this->login();
	}
}
