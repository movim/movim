<?php

/**
 * @package Widgets
 *
 * @file Account.php
 * This file is part of MOVIM.
 *
 * @brief The account creation widget.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 25 November 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */
 
class Account extends WidgetBase {
    
    function ajaxDiscoverServer($ndd) {
        if($ndd['ndd'] == '') {
            RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=account&err=datamissing"));
            RPC::commit();
            exit;
        }
        
        
        try {
            $dns = dns_get_record('_xmpp-client._tcp.'.$ndd['ndd']);

            if(isset($dns[0]['target']) && $dns[0]['target'] != null)
                $domain = $dns[0]['target'];
            
            $f = fsockopen($domain, 5222, $errno, $errstr, 10);

            if(!$f) {
                RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=account&err=xmppconnect"));
                RPC::commit();
                exit;
            }
            
            $stream = simplexml_load_string('<?xml version="1.0"?><stream:stream xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0"><iq type="get" id="reg1"><query xmlns="jabber:iq:register"/></iq></stream:stream>');
            $stream->addAttribute('to', $ndd['ndd']);
            fwrite($f, $stream->asXML());
            
            unset($stream);

            $response = stream_get_contents($f);
            
	        if(!$response) {
                	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountCreate&err=xmppcomm"));
                    RPC::commit();
     	            exit;
		    }

	        fclose($f); unset($f);

	        $response = simplexml_load_string($response);
            
            $elements = (array)$response->iq->query;
            
            if(!empty($elements)) {
                $html .= '
                    <form name="data">
                        <fieldset>
                                <legend>'.t('Step 2 - Fill in your informations').'</legend><br />';

                if(!isset($elements['x'])) {
                    foreach($elements as $element => $val) {
                        if($element == 'instructions')
                            $html .= '<p>'.$val.'</p><br />';
                        else {
                            $html .= '
                                <div class="element">
                                    <label for="'.$element.'">'.t(ucfirst($element)).'</label>
                                    <input
                                        name="'.$element.'"
                                        id="'.$element.'"
                                        placeholder="'.t(ucfirst($element)).'"
                                    />
                                </div>';
                        }
                    }
                } elseif(isset($elements['x'])) {
                    $html .= '<p>'.(string)$elements['x']->instructions.'</p><br />';
                    
                    foreach($elements['x']->field as $element) {
                        if((string)$element->attributes()->type != ''/* && (string)$element->attributes()->label != ''*/) {
                            $html .= '
                                <div class="element">
                                    <label for="'.(string)$element->attributes()->var.'">'.(string)$element->attributes()->label.'</label>
                                    <input
                                        name="'.(string)$element->attributes()->var.'"
                                        type="'.(string)$element->attributes()->type.'"
                                        id="'.(string)$element->attributes()->var.'"
                                        placeholder="'.(string)$element->attributes()->label.'"
                                    />
                                </div>';
                        }
                    }
                } 
                
                                    
                if(isset($elements['data'])) {
                    $html .= '<img src="data:image/jpg;base64,'.$elements['data'].'"/>';
                }
                
                $submit = $this->genCallAjax('ajaxSubmitData', "movim_parse_form('data')");
                
                $html .= '
                        <a
                            class="button icon yes" 
                            style="float: right;"
                            onclick="'.$submit.'"
                        >
                            '.t('Validate').'
                        </a>';
                
                $html .= '
                        </fieldset>
                    </form>';
            
                RPC::call('movim_fill', 'fillform', RPC::cdata($html));
                RPC::commit();
                
            } else {
                $html = '
                    <div class="message warning">
                        '.t('No account creation form founded on the server').'
                    </div>';
                
                RPC::call('movim_fill', 'fillform', RPC::cdata($html));
                RPC::commit();
            }


            
            
        } catch(Exception $e) {
            header(sprintf('HTTP/1.1 %d %s', $e->getCode(), $e->getMessage()));
            header('Content-Type: text/plain; charset=utf-8');
            echo $e->getMessage(),"\n";
        }
    }
    
    function ajaxSubmitData($datas) {
        movim_log($datas);
    }
    
	function build()
	{
        switch ($_GET['err']) {
            case 'datamissing':
	            $warning = '
	                    <div class="message error">
	                        '.t('Some data are missing !').'
	                    </div> ';
                break;
            case 'jiderror':
	            $warning = '
	                    <div class="message error">
	                        '.t('Wrong ID').'
	                    </div> ';
                break;
            case 'passworddiff':
	            $warning = '
	                    <div class="message error">
	                        '.t('You entered different passwords').'
	                    </div> ';
                break;
            case 'nameerr':
	            $warning = '
	                    <div class="message error">
	                        '.t('Invalid name').'
	                    </div> ';
                break;
            case 'userconflict':
	            $warning = '
	                    <div class="message error">
	                        '.t('Username already taken').'
	                    </div> ';
                break;
            case 'xmppconnect':
	            $warning = '
	                    <div class="message error">
	                        '.t('Could not connect to the XMPP server').'
	                    </div> ';
                break;
            case 'xmppcomm':
	            $warning = '
	                    <div class="message error">
	                        '.t('Could not communicate with the XMPP server').'
	                    </div> ';
                break;
            case 'unknown':
	            $warning = '
	                    <div class="message error">
	                        '.t('Unknown error').'
	                    </div> ';
                break;
        }
        
        $submit = $this->genCallAjax('ajaxDiscoverServer', "movim_parse_form('account')");
        ?>
        <div id="main">
            <div id="left">
                <?php echo $warning; ?>
            </div>
            <div id="center">
                <h1><?php echo t('Create a new account'); ?></h1>
                <div style="margin: 35px 20px;">
                    <form name="account">
                        <fieldset>
                            <legend><?php echo t('Step 1 - Search the server'); ?></legend>
                                <div class="element">
                                    <label for="ndd"><?php echo t("Server"); ?></label>
                                    <input
                                        pattern="^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$"
                                        placeholder="<?php echo t("Enter the server domain (ex: movim.eu)"); ?>"
                                        name="ndd"
                                        id="ndd"
                                    />
                                </div>                    
                                <div class="clear"></div>
                                <a
                                    class="button icon next" 
                                    style="float: right;"
                                    onclick="<?php echo $submit;?>; document.getElementById('fillform').innerHTML ='<?php echo t('Searching...');?>'"
                                >
                                    <?php echo t('Search'); ?>
                                </a>                    
                    </form>
                    <div class="clear"></div>
                    <div id="fillform"></div>
                </div>
            </div>
        </div>
        <?php
    }
}
/*
class Account extends WidgetBase {

    function __construct() {
        $this->addjs('account.js');
        parent::__construct(true);
    }

    function xmppRegistration($data) {
    	$conf = Conf::getServerConf();

    	// We prevent hacks from the browser request
        $valid_fields = array('username', 'nick', 'password', 'name', 'first',
	        'last', 'email', 'address', 'city', 'state', 'zip', 'phone', 'url',
	        'date', 'misc', 'text', 'key');

        define(XMPP_HOST, $conf['host']);
        define(XMPP_CONN, $conf['domain']);
        define(XMPP_PORT, 5222);

        try {

            // We create the XML Stanza
	        $stream = simplexml_load_string('<?xml version="1.0"?><stream:stream xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0"><iq id="register" type="set"><query xmlns="jabber:iq:register"/></iq></stream:stream>');

	        $stream->addAttribute('to', XMPP_HOST);

	        foreach($data as $key => $value)
		        if(in_array($key, $valid_fields))
			        $stream->iq->query->addChild($key, $value);

            // We try to connect to the XMPP Server
	        $f = fsockopen(XMPP_CONN, XMPP_PORT, $errno, $errstr, 10);

	        if(!$f) {
                	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountCreate&err=xmppconnect"));
                    RPC::commit();
     	            exit;
		    }

	        fwrite($f, $stream->asXML());
	        unset($stream);

	        $response = stream_get_contents($f);

	        if(!$response) {
                	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountCreate&err=xmppcomm"));
                    RPC::commit();
     	            exit;
		    }

	        fclose($f); unset($f);

	        $response = simplexml_load_string($response);

	        if(!$response) throw new Exception('The XMPP server sent an invalid response', 500);

	        if($stream_error = $response->xpath('/stream:stream/stream:error')) {
		        list($stream_error) = $stream_error;
		        list($cond) = $stream_error->children();

		        throw new Exception($stream_error->text ? $stream_error->text : $cond->getName(), 500);
	        }

	        $iq = $response->iq;

	        if($iq->error) {
		        list($cond) = $iq->error->children();
		        if($cond->getName() == 'conflict') {
                	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountCreate&err=userconflict"));
                    RPC::commit();
     	            exit;
		        }
		        throw new Exception($iq->error->text ? $iq->error->text : $cond->getName(), 400);
	        }

	        if($iq = $response->iq and $iq->attributes()->type == 'result') {
	            $this->localRegistration($data, $conf);
	        } else {
                	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountCreate&err=unknown"));
                    RPC::commit();
     	            exit;
		    }
        } catch(Exception $e) {
	        header(sprintf('HTTP/1.1 %d %s', $e->getCode(), $e->getMessage()));
	        header('Content-Type: text/plain; charset=utf-8');
	        echo $e->getMessage(),"\n";
        }
    }

    function localRegistration($data) {
       	$confvar = Conf::getServerConf();

        global $sdb;
        $conf = new ConfVar();
        
        $conf
            ->set('login', $data['username'].'@'.$data['server'])
            ->set('pass', sha1($data['password']))
            ->set('host', $confvar['host'])
            ->set('domain', $confvar['domain'])
            ->set('port', $confvar['port'])
            ->set('boshHost', $confvar['defBoshHost'])
            ->set('boshSuffix', $confvar['defBoshSuffix'])
            ->set('boshPort', $confvar['defBoshPort'])
            ->set('language', $confvar['defLang'])
            ->set('first', false);

        $sdb->save($conf);

    	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=mainPage&err=acccreated"));
        RPC::commit();
        exit;
    }

	function ajaxSubmit($data) {
	    foreach($data as $value) {
	        if($value == NULL || $value == '') {
	            RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountCreate&err=datamissing"));
	            RPC::commit();
	            exit;
	        }
	    }

	    foreach($data as $value) {
            if(!filter_var($data['username'].'@'.$data['server'], FILTER_VALIDATE_EMAIL)) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountCreate&err=jiderror"));
                RPC::commit();
                exit;
            } elseif($data['password'] != $data['passwordconf']) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountCreate&err=passworddiff"));
                RPC::commit();
 	            exit;
            } elseif(eregi('[^a-zA-Z0-9_]', $data['nick'])) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=accountCreate&err=nameerr"));
                RPC::commit();
 	            exit;
            }
	    }

	    unset($data['passwordconf']);
	    $this->xmppRegistration($data);
	}

	function build()
	{
        // Do we still allow user registration?
        $conf = Conf::getServerConf();

        $users = count(ConfVar::select(array()));

        if($conf['maxUsers'] > -1 && $users > $conf['maxUsers']) {
            echo '<br /><br /><br />';
            echo '<div class="message error">'.t('User registration is disabled.').'</div>';
            return;
        }
        
        switch ($_GET['err']) {
            case 'datamissing':
	            $warning = '
	                    <div class="message error">
	                        '.t('Some data are missing !').'
	                    </div> ';
                break;
            case 'jiderror':
	            $warning = '
	                    <div class="message error">
	                        '.t('Wrong ID').'
	                    </div> ';
                break;
            case 'passworddiff':
	            $warning = '
	                    <div class="message error">
	                        '.t('You entered different passwords').'
	                    </div> ';
                break;
            case 'nameerr':
	            $warning = '
	                    <div class="message error">
	                        '.t('Invalid name').'
	                    </div> ';
                break;
            case 'userconflict':
	            $warning = '
	                    <div class="message error">
	                        '.t('Username already taken').'
	                    </div> ';
                break;
            case 'xmppconnect':
	            $warning = '
	                    <div class="message error">
	                        '.t('Could not connect to the XMPP server').'
	                    </div> ';
                break;
            case 'xmppcomm':
	            $warning = '
	                    <div class="message error">
	                        '.t('Could not communicate with the XMPP server').'
	                    </div> ';
                break;
            case 'unknown':
	            $warning = '
	                    <div class="message error">
	                        '.t('Unknown error').'
	                    </div> ';
                break;
        }

	$submit = $this->genCallAjax('ajaxSubmit', "movim_parse_form('account')");
	?>
    <div id="main">
        <div id="left">
            <?php echo $warning; ?>
        </div>
        <div id="center">
            <h1><?php echo t('Create a new account'); ?></h1>
            <div style="margin: 20px">
                <form name="account">
                    <input type="hidden" name="server" value="<?php echo $conf['host']; ?>">
                    
                        <div class="element">
                            <label for="fn"><?php echo t("My address"); ?></label>
                            <input
                                onfocus="accountAdvices('<p><?php echo t('Firstly fill in this blank with a brand new account ID, this address will follow you on all the Movim network !'); ?></p><p><?php echo t('Only alphanumerics elements are authorized'); ?></p>');"
                                onblur="accountAdvices(); document.querySelector('#nick').value = this.value;"
                                pattern="[a-zA-Z0-9]+"
                                autofocus
                                placeholder="<?php echo t("My address"); ?>"
                                class="big"
                                name="username"/>
                        </div>
                        
                        <div class="element">
                            <label for="fn"><?php echo t("Password"); ?></label>
                            <input
                                type="password"
                                onfocus="
                                    accountAdvices('<p><?php echo addslashes(t('Make sure your password is safe :')); ?> <ul><li><?php echo addslashes(t('A capital letter, a digit and a special character are recommended')); ?></li><li><?php echo t('8 characters'); ?></li></ul></p><p><?php echo t('Example :'); ?> m0vimP@ss</p>');"
                                onblur="accountAdvices();"
                                placeholder="<?php echo t("Password"); ?>"
                                class="big"
                                name="password"
                            />
                        </div>
                        
                        <div class="element">
                            <label for="fn"><?php echo t("Retype password"); ?></label>
                            <input
                                type="password"
                                onfocus="accountAdvices('<p><?php echo t('Same here !'); ?></p>');"
                                onblur="accountAdvices();"
                                placeholder="<?php echo t("Retype"); ?>"
                                class="big"
                                name="passwordconf"
                            />
                        </div>
                        
                        <div class="element">
                            <label for="nick"><?php echo t("Pseudo"); ?></label>
                            <input
                                pattern="[a-zA-Z0-9]+"
                                placeholder="<?php echo t("Pseudo"); ?>"
                                class="big"
                                name="nick"
                                id="nick"
                            />
                        </div>

                        <input
                            onfocus="accountAdvices('<p><?php echo t('Firstly fill in this blank with a brand new account ID, this address will follow you on all the Movim network !'); ?></p><p><?php echo t('Only alphanumerics elements are authorized'); ?></p>');"
                            onblur="accountAdvices(); document.querySelector('#nick').value = this.value;"
                            pattern="[a-zA-Z0-9]+"
                            autofocus
                            placeholder="<?php echo t("My address"); ?>"
                            class="big"
                            style="text-align: right; width: 300px;"
                            name="username"/>
                            <span style="font-size: 17px;">@<?php echo $conf['host']; ?></span>
                    
                    
                        <input type="button" class="button big icon submit" style="margin-left: 400px;" value="<?php echo t('Create'); ?>" onclick="<?php echo $submit;?> this.className='button big icon loading';">
                    
                        <div id="advices" class="message info"></div>
                </form>
            </div>
        </div>
    </div>
	<?php
	}
}
*/
