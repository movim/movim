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

class WidgetCommon extends WidgetBase {
    protected function preparePost($message, $comments = false) {        
        $tmp = '';
        
        if(isset($message[1])) {
            $tmp = '<div class="post ';
                if($this->user->getLogin() == $message[0]->getData('jid'))
                    $tmp .= 'me';
            $tmp .= '" id="'.$message[0]->getData('nodeid').'" >
                    <img class="avatar" src="'.$message[1]->getPhoto('s').'">

                    <span><a href="?q=friend&f='.$message[0]->getData('jid').'">'.$message[1]->getTrueName().'</a></span>
                    <span class="date">'.prepareDate(strtotime($message[0]->getData('updated'))).'</span>
                    <div class="content">
                        '.prepareString($message[0]->getData('content')). '</div>';
                        
            //$attachments = AttachmentHandler::getAttachment($this->user->getLogin(), $message[0]->getData('nodeid'));
            /*if($attachments) {
                $tmp .= '<div class="attachment">';
                foreach($attachments as $attachment)
                    $tmp .= '<a target="_blank" href="'.$attachment->getData('link').'"><img alt="'.$attachment->getData('title').'" title="'.$attachment->getData('title').'" src="'.$attachment->getData('thumb').'"></a>';
                $tmp .= '</div>';
            }*/
            
            
            if($message[0]->getPlace() != false)
                $tmp .= '<span class="place">
                            <a 
                                target="_blank" 
                                href="http://www.openstreetmap.org/?lat='.$message[0]->getData('lat').'&lon='.$message[0]->getData('lon').'&zoom=10"
                            >'.$message[0]->getPlace().'</a>
                         </span>';
                         
            if($message[0]->getData('jid') != $message[0]->getData('uri'))
                $tmp .= '<span class="recycle"><a href="?q=friend&f='.$message[0]->getData('uri').'">'.$message[0]->getData('name').'</a></span>';
              
            if($message[0]->getData('commentson') == 1) {
                $tmp .= '<div class="comments" id="'.$message[0]->getData('nodeid').'comments">';

                $comments = WidgetCommon::prepareComments($comments);
                
                if($comments != false)
                    $tmp .= $comments;

                $tmp .= '
                         <div class="comment">
                                <a 
                                    class="getcomments icon bubble" 
                                    style="margin-left: 0px;" 
                                    onclick="'.$this->genCallAjax('ajaxGetComments', "'".$message[0]->getData('commentplace')."'", "'".$message[0]->getData('nodeid')."'").'; this.innerHTML = \''.t('Loading comments ...').'\'">'.
                                        t('Get the comments').'
                                </a>
                            </div></div>';
                $tmp .= '<div class="comments">
                            <div 
                                class="comment"
                                onclick="this.parentNode.querySelector(\'#commentsubmit\').style.display = \'table\'; this.style.display =\'none\'">
                                <a class="getcomments icon bubbleadd">'.t('Add a comment').'</a>
                            </div>
                            <table id="commentsubmit">
                                <tr>
                                    <td>
                                        <textarea id="'.$message[0]->getData('nodeid').'commentcontent" onkeyup="movim_textarea_autoheight(this);"></textarea>
                                    </td>
                                </tr>
                                <tr class="commentsubmitrow">
                                    <td style="width: 100%;"></td>
                                    <td>
                                        <a
                                            onclick="
                                                    if(document.getElementById(\''.$message[0]->getData('nodeid').'commentcontent\').value != \'\') {
                                                        '.$this->genCallAjax(
                                                            'ajaxPublishComment', 
                                                            "'".$message[0]->getData('commentplace')."'", 
                                                            "'".$message[0]->getData('nodeid')."'", 
                                                            "encodeURIComponent(document.getElementById('".$message[0]->getData('nodeid')."commentcontent').value)").
                                                            'document.getElementById(\''.$message[0]->getData('nodeid').'commentcontent\').value = \'\';
                                                    }"
                                            class="button tiny icon submit"
                                            style="padding-left: 28px;"
                                        >'.
                                            t("Submit").'
                                        </a>
                                    </td>
                                </tr>
                            </table>';
                $tmp .= '</div>';
            }
              
            $tmp .= '</div>';

        }
        return $tmp;
    }

    protected function prepareComments($comments) {
        $tmp = false;
        
        $size = sizeof($comments);
        
        $comcounter = 0;
        
        if($size > 3) {
            $tmp = '<div 
                        class="comment"
                        onclick="
                            com = this.parentNode.querySelectorAll(\'.comment\'); 
                            for(i = 0; i < com.length; i++) { com.item(i).style.display = \'block\';};
                            this.style.display = \'none\';">
                        <a class="getcomments icon bubbleold">'.t('Show the older comments').'</a>
                    </div>';
            $comcounter = $size - 3;
        }
        
        if($comments) {
            $i = 0;
            foreach($comments as $comment) {
                if(isset($comments[$i+1]) && $comments[$i][0]->getData('nodeid') != $comments[$i+1][0]->getData('nodeid')) {

                    if(isset($comment[1])) {
                        $photo = $comment[1]->getPhoto('s');
                        $name = $comment[1]->getTrueName();
                    }
                    else {
                        $photo = "image.php?c=default";
                        $name = $comment[0]->getData('jid');
                    }
                    
                    $tmp .= '
                        <div class="comment" ';
                    if($comcounter > 0) {
                        $tmp .= 'style="display:none;"';
                        $comcounter--;
                    }
                        
                    $tmp .='>
                            <img class="avatar tiny" src="'.$photo.'">
                            <span><a href="?q=friend&f='.$comment[0]->getData('jid').'">'.$name.'</a></span>
                            <span class="date">'.prepareDate(strtotime($comment[0]->getData('published'))).'</span><br />
                            <div class="content tiny">'.prepareString($comment[0]->getData('content')).'</div>
                        </div>';
                }
                $i++;
            }
        }
        
        return $tmp;
    }
    
    function onComment($parent) {        
        $html = $this->prepareComments($parent);
        RPC::call('movim_fill', $parent.'comments', RPC::cdata($html));
    }
    
    function onNoComment($parent) {     
        $html = '
            <div class="comment">
                <a 
                    class="getcomments icon bubble" 
                    style="margin-left: 0px;">'.
                    t('No comments').
                '</a>
            </div>';
        RPC::call('movim_fill', $parent.'comments', RPC::cdata($html));
    }
    
    function onNoCommentStream($parent) { 
        $html = '
            <div class="comment">
                <a 
                    class="getcomments icon bubble" 
                    style="margin-left: 0px;">'.
                    t('No comments stream').
                '</a>
            </div>';
        RPC::call('movim_fill', $parent.'comments', RPC::cdata($html));
    }
    
	function ajaxGetComments($jid, $id) {
		$this->xmpp->getComments($jid, $id);
	}
    
    function ajaxPublishComment($to, $id, $content) {
        if($content != '')
            $this->xmpp->publishComment($to, $id, htmlentities(rawurldecode($content)));
    }
}
