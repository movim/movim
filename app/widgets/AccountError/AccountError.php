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
                            '.$this->__('error.data_missing').'
                        </div> ';
                break;
            case 'jiderror':
                $warning = '
                        <div class="message warning">
                            '.$this->__('error.jid_error').'
                        </div> ';
                break;
            case 'passworddiff':
                $warning = '
                        <div class="message info">
                            '.$this->__('error.password_diff').'
                        </div> ';
                break;
            case 'nameerr':
                $warning = '
                        <div class="message warning">
                            '.$this->__('error.name_error').'
                        </div> ';
                break;
            case 'notacceptable':
                $warning = '
                        <div class="message error">
                            '.$this->__('error.not_acceptable').'
                        </div> ';
                break;
            case 'userconflict':
                $warning = '
                        <div class="message warning">
                            '.$this->__('error.user_conflict').'
                        </div> ';
                break;
            case 'xmppconnect':
                $warning = '
                        <div class="message error">
                            '.$this->__('error.xmpp_connect').'
                        </div> ';
                break;
            case 'xmppcomm':
                $warning = '
                        <div class="message error">
                            '.$this->__('error.xmpp_communicate').'
                        </div> ';
                break;
            case 'unknown':
                $warning = '
                        <div class="message error">
                            '.$this->__('error.unknown').'
                        </div> ';
                break;
        }

        $this->view->assign('warning', $warning);
    }
}
