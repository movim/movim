<?php

/**
 * @package Widgets
 *
 * @file AccountError.php
 * This file is part of MOVIM.
 *
 * @brief The account creation widget error.
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
 
class AccountError extends WidgetBase {
    
    function load()
    {
        $warning = '';
        
        if(isset($_GET['err']))
        switch ($_GET['err']) {
            case 'datamissing':
                $warning = '
                        <div class="message warning">
                            '.t('Some data are missing !').'
                        </div> ';
                break;
            case 'jiderror':
                $warning = '
                        <div class="message warning">
                            '.t('Wrong ID').'
                        </div> ';
                break;
            case 'passworddiff':
                $warning = '
                        <div class="message info">
                            '.t('You entered different passwords').'
                        </div> ';
                break;
            case 'nameerr':
                $warning = '
                        <div class="message warning">
                            '.t('Invalid name').'
                        </div> ';
                break;
            case 'notacceptable':
                $warning = '
                        <div class="message error">
                            '.t('Request not acceptable').'
                        </div> ';
                break;
            case 'userconflict':
                $warning = '
                        <div class="message warning">
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

        $this->view->assign('warning', $warning);
    }
}
