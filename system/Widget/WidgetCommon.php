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
    protected function preparePost($message) {
        global $sdb;
        
        // We get some informartions about the author
        $user = new User();
        $contact = $sdb->select('Contact', array('key' => $user->getLogin(), 'jid' => $message->getData('jid')));
        
        $tmp = '';
        
        if(isset($contact[0])) {            
            $tmp = '<div class="post ';
                if($user->getLogin() == $message->getData('jid'))
                    $tmp .= 'me';
            $tmp .= '" id="'.$message->getData('nodeid').'" >
                    <img class="avatar" src="'.$contact[0]->getPhoto('s').'">

                    <span><a href="?q=friend&f='.$message->getData('jid').'">'.$contact[0]->getTrueName().'</a></span>
                    <span class="date">'.prepareDate(strtotime($message->getData('updated'))).'</span>
                    <div class="content">
                        '.prepareString($message->getData('content')). '</div>';
                        
            $attachments = AttachmentHandler::getAttachment($user->getLogin(), $message->getData('nodeid'));
            if($attachments) {
                foreach($attachments as $attachment) {
                    $tmp .= '<a target="_blank" href="'.$attachment->getData('link').'"><img alt="'.$attachment->getData('title').'" title="'.$attachment->getData('title').'" src="'.$attachment->getData('thumb').'"></a>';
                $tmp .= '</div>';
            }
            
            $tmp = $post->build();
            
            if($message->getPlace() != false)
                $tmp .= '<span class="place">
                            <a 
                                target="_blank" 
                                href="http://www.openstreetmap.org/?lat='.$message->getData('lat').'&lon='.$message->getData('lon').'&zoom=10"
                            >'.$message->getPlace().'</a>
                         </span>';
                         
            if($message->getData('jid') != $message->getData('uri'))
                $tmp .= '<span class="recycle"><a href="?q=friend&f='.$message->getData('uri').'">'.$message->getData('name').'</a></span>';
              
            if($message->getData('commentson') == 1) {
                $tmp .= '<div class="comments" id="'.$message->getData('nodeid').'comments">';

                $comments = WidgetCommon::prepareComments($message->getData('nodeid'));
                
                if($comments != false)
                    $tmp .= $comments;

                $tmp .= '
                            <div class="comment">
                                <a 
                                    class="getcomments icon bubble" 
                                    style="margin-left: 0px;" 
                                    onclick="'.$this->genCallAjax('ajaxGetComments', "'".$message->getData('commentplace')."'", "'".$message->getData('nodeid')."'").'; this.innerHTML = \''.t('Loading comments ...').'\'">'.
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
                                        <textarea id="'.$message->getData('nodeid').'commentcontent" onkeyup="movim_textarea_autoheight(this);"></textarea>
                                    </td>
                                </tr>
                                <tr class="commentsubmitrow">
                                    <td style="width: 100%;"></td>
                                    <td>
                                        <a
                                            onclick="
                                                    if(document.getElementById(\''.$message->getData('nodeid').'commentcontent\').value != \'\') {
                                                        '.$this->genCallAjax(
                                                            'ajaxPublishComment', 
                                                            "'".$message->getData('commentplace')."'", 
                                                            "'".$message->getData('nodeid')."'", 
                                                            "encodeURIComponent(document.getElementById('".$message->getData('nodeid')."commentcontent').value)").
                                                            'document.getElementById(\''.$message->getData('nodeid').'commentcontent\').value = \'\';
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

    protected function prepareComments($parentid) {
        $user = new User();

        $query = Post::query()
                            ->where(array('key' => $user->getLogin(), 'parentid' => $parentid))
                            ->orderby('published', false);
        $comments = Post::run_query($query);
        
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
            foreach($comments as $comment) {                
                $query = Contact::query()
                                    ->where(array('key' => $user->getLogin(), 'jid' => $comment->getData('jid')));
                $contact = Post::run_query($query);

                if(isset($contact[0])) {
                    $photo = $contact[0]->getPhoto('s');
                    $name = $contact[0]->getTrueName();
                }
                else {
                    $photo = "image.php?c=default";
                    $name = $comment->getData('jid');
                }
                
                $tmp .= '
                    <div class="comment" ';
                if($comcounter > 0) {
                    $tmp .= 'style="display:none;"';
                    $comcounter--;
                }
                    
                $tmp .='>
                        <img class="avatar tiny" src="'.$photo.'">
                        <span><a href="?q=friend&f='.$comment->getData('jid').'">'.$name.'</a></span>
                        <span class="date">'.prepareDate(strtotime($comment->getData('published'))).'</span><br />
                        <div class="content tiny">'.prepareString($comment->getData('content')).'</div>
                    </div>';
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
