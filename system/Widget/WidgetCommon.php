<?php

/**
 * @file WidgetCommon.php
 * This file is part of MOVIM.
 *
 * @brief The widgets commons methods.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @date 08 march 2012
 *
 * Copyright (C)2010 MOVIM Project
 *
 * See COPYING for licensing information.
 */

class WidgetCommon {
    protected function preparePost($message) {
        global $sdb;
        $user = new User();
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $message->getData('jid')));
        
        $tmp = '';
        
        if(isset($contact[0])) {
            $tmp = '<div class="post ';
                if($user->getLogin() == $message->getData('jid'))
                    $tmp .= 'me';
            $tmp .= '" id="'.$message->getData('nodeid').'" >
                    <img class="avatar" src="'.$contact[0]->getPhoto('m').'">

                    <span><a href="?q=friend&f='.$message->getData('jid').'">'.$contact[0]->getTrueName().'</a></span>
                    <span class="date">'.prepareDate(strtotime($message->getData('updated'))).'</span>
                    <div class="content">
                        '.prepareString($message->getData('content')). '</div>';
                        
            $attachments = AttachmentHandler::getAttachment($message->getData('nodeid'));
            if($attachments) {
                $tmp .= '<div class="attachment">';
                foreach($attachments as $attachment)
                    $tmp .= '<a target="_blank" href="'.$attachment->getData('link').'"><img src="'.$attachment->getData('thumb').'"></a>';
                $tmp .= '</div>';
            }
            
            if($message->getPlace() != false)
                $tmp .= '<span class="place">
                            <a 
                                target="_blank" 
                                href="http://www.openstreetmap.org/?lat='.$message->getData('lat').'&lon='.$message->getData('lon').'&zoom=10"
                            >'.$message->getPlace().'</a>
                         </span>';
                          
            $tmp .= '<div class="comments" id="'.$message->getData('nodeid').'comments">';

            $tmp .= WidgetCommon::prepareComments($message);

            $tmp .= '
                    <div class="comment">
                        <a class="getcomments icon bubble" style="margin-left: 0px;" onclick="'.$this->genCallAjax('ajaxGetComments', "'".$message->getData('jid')."'", "'".$message->getData('nodeid')."'").'; this.innerHTML = \''.t('Loading comments ...').'\'">'.t('Get the comments').'</a>
                    </div>';
            $tmp .= '</div>';
              
            $tmp .= '</div>';

        }
        return $tmp;
    }

    protected function prepareComments($message) {
        global $sdb;
        $user = new User();
        $comments = $sdb->select('Message', array('key' => $user->getLogin(), 'parentid' => $message->getData('nodeid')), 'updated', true);
        
        if($comments) {
            foreach($comments as $comment) {
                $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $comment->getData('jid')));
                
                if(isset($contact[0])) {
                    $photo = $contact[0]->getPhoto('s');
                    $name = $contact[0]->getTrueName();
                }
                else {
                    $photo = "image.php?c=default";
                    $name = $comment->getData('jid');
                }
                
                $tmp .= '
                    <div class="comment">
                        <img class="avatar tiny" src="'.$photo.'">
                        <span><a href="?q=friend&f='.$comment->getData('jid').'">'.$name.'</a></span>
                        <span class="date">'.prepareDate(strtotime($comment->getData('published'))).'</span><br />
                        <div class="content tiny">'.prepareString($comment->getData('content')).'</div>
                    </div>';
            }
        }
        
        return $tmp;
    }
}
