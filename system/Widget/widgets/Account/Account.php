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
    
    function __construct() {
        $this->addjs('account.js');
        parent::__construct(true);
    }
    
	function ajaxSubmit($data) {movim_log($data);
	    foreach($data as $value) {
	        if($value == NULL || $value == '') {
	            RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=account&err=datamissing"));
	            RPC::commit();
	            exit;
	        }
	    }
	        
	    foreach($data as $value) {
            if(!filter_var($data['pseudo'].'@'.$data['server'], FILTER_VALIDATE_EMAIL)) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=account&err=jiderror"));
                RPC::commit();
                exit;
            } elseif($data['password'] != $data['passwordconf']) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=account&err=passworddiff"));
                RPC::commit();
 	            exit;
            } elseif(!preg_match("/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/", $data['password'])) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=account&err=password"));
                RPC::commit();
 	            exit;
            } elseif(eregi('[^a-zA-Z0-9_]', $data['name'])) {
            	RPC::call('movim_reload', RPC::cdata(BASE_URI."index.php?q=account&err=nameerr"));
                RPC::commit();
 	            exit;
            }
	    }
	}    
	
	function build()
	{
        switch ($_GET['err']) {
            case 'datamissing':
	            $warning = '
	                    <div class="error">
	                        '.t('Some data are missing !').'
	                    </div> ';
                break;
            case 'jiderror':
	            $warning = '
	                    <div class="error">
	                        '.t('Wrong ID').'
	                    </div> ';
                break;
            case 'password':
	            $warning = '
	                    <div class="error">
	                        '.t('Wrong password').'
	                    </div> ';
                break; 
            case 'passworddiff':
	            $warning = '
	                    <div class="error">
	                        '.t('You entered different passwords').'
	                    </div> ';
                break; 
            case 'nameerr':
	            $warning = '
	                    <div class="error">
	                        '.t('Invalid name').'
	                    </div> ';
                break; 
        }
	
	$conf = Conf::getServerConf();
	$submit = $this->genCallAjax('ajaxSubmit', "movim_parse_form('account')");
	?>	
	<div id="account" style="width: 730px; margin: 0 auto;">
        <?php echo $warning; ?>
	    <form  style="width: 500px; float: left;" name="account">
	        <input type="hidden" name="server" value="<?php echo $conf['domain']; ?>">
	        <h1>Create a new account</h1>
	        <p style="margin-top: 20px;">
	            <input 
	                onfocus="accountAdvices('<p><?php echo t('Firstly fill in this blank with a brand new account ID, this adress will follow you on all the Movim network !'); ?></p><p><?php echo t('Only alphanumerics elements are authorized'); ?></p>');" 
	                onblur="accountAdvices(); document.querySelector('#name').value = this.value;" 
	                pattern="[a-zA-Z0-9]+" 
	                autofocus 
	                placeholder="<?php echo t("My address"); ?>" 
	                class="big" 
	                style="text-align: right;" 
	                name="pseudo"/>
	                <span style="font-size: 17px;">@<?php echo $conf['domain']; ?></span>
	        </p>
	        
	        <p>
	            <input 
	                type="password"
	                onfocus="
	                    accountAdvices('<p><?php echo t('Make sure your password is safe :'); ?> <ul><li><?php echo t('A capital letter, a digit and a special character are required'); ?></li><li><?php echo t('8 characters minimum'); ?></li></ul></p><p><?php echo t('Example :'); ?> m0vimP@ss</p>');" 
	                onblur="accountAdvices();" 
	                pattern="^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$" 
	                placeholder="<?php echo t("Password"); ?>" 
	                class="big" 
	                name="password"
	            />
	        </p>
	        
	        <p>
	            <input 
	                type="password"
	                onfocus="accountAdvices('<p><?php echo t('Same here !'); ?></p>');" 
	                onblur="accountAdvices();"
	                pattern="^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$" 
	                placeholder="<?php echo t("Retype"); ?>" 
	                class="big" 
	                name="passwordconf"
	            />
	        </p>
	        
	        <p>
	            <input 
	                pattern="[a-zA-Z0-9]+"
	                placeholder="<?php echo t("Pseudo"); ?>" 
	                class="big" 
	                name="name"
	                id="name"
	            />
	        </p>
	        
	        <p>
	            <input type="button" class="big icon submit" style="float: right;" value="   <?php echo t('Create'); ?>" onclick="<?php echo $submit;?>">
	        </p>
	        
	    </form>    
	    <div id="advices" class="warning" style="width: 200px; height: 160px; float: right; margin-top: 60px;">
	    </div>
	</div>
	<?php
	}
}
