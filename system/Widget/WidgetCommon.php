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
    protected function printPost($post, $comments = false, $public = false) {
        if($post->title)
            $title = '
                <span>
                    '.$post->title.'
                </span><br />';
                
        if($this->user->getLogin() == $post->jid) {
            $class = 'me ';
            if($post->public == 1)
                $access .= 'protect black';
            else
                $access .= 'protect orange';
                
            $avatar = $post->getContact()->getPhoto('s');
        } elseif($post->public == 2) {
            $class .= ' folded';
            $fold = t('Unfold');
            $avatar = $post->getContact()->getPhoto('xs');
        } else {
            $fold = t('Fold');
            $avatar = $post->getContact()->getPhoto('s');
        }
        
        if(!filter_var($post->from, FILTER_VALIDATE_EMAIL) && $post->node != '')
            $group = '
                <span class="group">
                    <a href="?q=node&s='.$post->from.'&n='.$post->node.'">'.$post->node.' ('.$post->from.')</a>
                </span>';
        elseif($post->from != $post->aid)
            $recycle .= '
                <span class="recycle">
                    <a href="?q=friend&f='.$post->from.'">'.$post->from.'</a>
                 </span>';

        if($post->getPlace() != false)
            $place .= '
                <span class="place">
                    <a 
                        target="_blank" 
                        href="http://www.openstreetmap.org/?lat='.$post->lat.'&lon='.$post->lon.'&zoom=10"
                    >'.t('Place').'</a>
                </span>';

        $content = 
                prepareString(html_entity_decode($post->content));
        
        if($post->node == 'urn:xmpp:microblog:0')
            $comments = $this->printComments($post, $comments, $public);
        else
			$comments = '';
        //else
        //$comments = '';
            
        //if($this->user->getLogin() == $post->jid) 
        //    $toolbox = $this->getToolbox($post);
        
        $html = '
            <div class="post '.$class.'" id="'.$post->nodeid.'">
                <a href="?q=friend&amp;f='.$post->jid.'">
                    <img class="avatar" src="'.$avatar.'">
                </a>

                <div id="'.$post->nodeid.'bubble" class="postbubble '.$access.'">
                    <span class="title">'.$title.'</span>
                    <span class="fold">
                        <a 
                        href="#" 
                        onclick="'.
                            $this->genCallAjax(
                                'ajaxPostFold', 
                                "'".$post->nodeid."'").' 
                            movim_toggle_class(\'#'.$post->nodeid.'\',\'folded\')">'.
                            $fold.'
                        </a>
                    </span>
                    <span>
                        <a href="?q=friend&amp;f='.$post->jid.'">'.$post->getContact()->getTrueName().'</a>
                    </span>
                    <span class="date">
                        '.prepareDate(strtotime($post->published)).'
                    </span>
                    <div class="content">
                    '.$content.'
                    </div>
                    '.$comments.'
                    '.$place.'
                    '.$recycle.'
                    '.$group.'
                </div>
                <div class="clear"></div>
                '.$toolbox.'
            </div>
            ';
        return $html;
    }
    
    private function getToolbox($post) {
        $html = '
            <div class="tools">
                '.t("Change the privacy level").' : 
                <a
                    title="'.t("your post will appear in your Movim public feed").'"
                    onclick="'.
                        $this->genCallAjax(
                            'ajaxPrivacyPost', 
                            "'".$this->user->getLogin()."'", 
                            "'".$post->nodeid."'",
                            "'black'").'" >
                    '.t("Everyone").'</a>,
                <a
                    onclick="'.
                        $this->genCallAjax(
                            'ajaxPrivacyPost', 
                            "'".$this->user->getLogin()."'", 
                            "'".$post->nodeid."'",
                            "'orange'").'" >
                    '.t("Your contacts").'</a>
                <!--<a
                    style="float: right; display: none;";
                    id="deleteno"
                    onclick="
                        this.parentNode.querySelector(\'#deleteyes\').style.display = \'none\';
                        this.style.display = \'none\';
                        "
                    onclick="">
                    ✘ '.t("No").'
                </a>
                <a
                    style="float: right; padding-right: 1em; display: none;";
                    id="deleteyes"
                    onclick="'.
                        $this->genCallAjax(
                            'ajaxDeletePost', 
                            "'".$this->user->getLogin()."'", 
                            "'".$post->nodeid."'").'" >
                    ✔ '.t("Yes").' 
                </a>
                <a
                    style="float: right; padding-right: 1em;";
                    onclick="
                        this.parentNode.querySelector(\'#deleteyes\').style.display = \'inline\';
                        this.parentNode.querySelector(\'#deleteno\').style.display = \'inline\';
                        " 
                    title="'.t("Delete this post").'">
                    '.t("Delete this post").'
                </a>-->


            </div>';
            
        return $html;
    }
    
    protected function printComments($post, $comments, $public = false) {
                $tmp .= '
                    <div class="comments" id="'.$post->nodeid.'comments">';

                $commentshtml = $this->prepareComments($comments);
                
                if($commentshtml != false)
                    $tmp .= $commentshtml;

                if($public == false) {
                    $tmp .= '
                             <div class="comment">
                                    <a 
                                        class="getcomments icon bubble" 
                                        style="margin-left: 0px;" 
                                        onclick="'.$this->genCallAjax('ajaxGetComments', "'".$post->commentplace."'", "'".$post->nodeid."'").'; this.innerHTML = \''.t('Loading comments ...').'\'">'.
                                            t('Get the comments').'
                                    </a>
                                </div></div>';
                    $tmp .= '<div class="comments">
                                <div 
                                    class="comment"
                                    style="border-bottom: none;"
                                    onclick="this.parentNode.querySelector(\'#commentsubmit\').style.display = \'table\'; this.style.display =\'none\'">
                                    <a class="getcomments icon bubbleadd">'.t('Add a comment').'</a>
                                </div>
                                <table id="commentsubmit">
                                    <tr>
                                        <td>
                                            <textarea id="'.$post->nodeid.'commentcontent" onkeyup="movim_textarea_autoheight(this);"></textarea>
                                        </td>
                                    </tr>
                                    <tr class="commentsubmitrow">
                                        <td style="width: 100%;"></td>
                                        <td>
                                            <a
                                                onclick="
                                                        if(document.getElementById(\''.$post->nodeid.'commentcontent\').value != \'\') {
                                                            '.$this->genCallAjax(
                                                                'ajaxPublishComment', 
                                                                "'".$post->commentplace."'", 
                                                                "'".$post->nodeid."'", 
                                                                "encodeURIComponent(document.getElementById('".$post->nodeid."commentcontent').value)").
                                                                'document.getElementById(\''.$post->nodeid.'commentcontent\').value = \'\';
                                                        }"
                                                class="button tiny icon submit"
                                                style="padding-left: 28px;"
                                            >'.
                                                t("Submit").'
                                            </a>
                                        </td>
                                    </tr>
                                </table>';
                }
                $tmp .= '</div>';
        return $tmp;

    }
    
    /*
     * @desc Prepare a group of messages
     * @param array of messages
     * @return generated HTML
     */
    protected function preparePosts($posts, $public = false) {
        if($posts == false || empty($posts)) {
			$html = '<div style="padding: 1.5em; text-align: center;">Ain\'t Nobody Here But Us Chickens...</div>';
		} else {
			$html = '';

            $pd = new \modl\PostnDAO();
            $comments = $pd->getComments($posts);
            
            foreach($posts as $post) {
                // We split the interesting comments for each messages
                $i = 0;
                
                $messagecomment = array();
                foreach($comments as $comment) {
                    if('urn:xmpp:microblog:0:comments/'.$post->nodeid == $comments[$i]->node) {
                        array_push($messagecomment, $comment);
                        unset($comment);
                    }
                    $i++;
                }
                
                $html .= $this->printPost($post, $messagecomment, $public);
			}
			
        }
		
		return $html;
    }
    
    protected function testIsSet($element)
    {
        if(isset($element) && $element != '')
            return true;
        else
            return false;
    }    
    /*
    protected function preparePost($message, $comments = false) {        
        $tmp = '<a name="'.$message[0]->getData('nodeid').'"></a>';
        
        if(isset($message[1])) {
            $tmp = '<div class="post ';
            
            if($message[0]->getData('jid') == $this->user->getLogin())
                $tmp .= 'me';

            $tmp .= '" id="'.$message[0]->getData('nodeid').'" >
            
                    <a href="?q=friend&f='.$message[0]->getData('jid').'">
                        <img class="avatar" src="'.$message[1]->getPhoto('s').'">
                    </a>
                    
                    <div id="'.$message[0]->getData('nodeid').'bubble" class="postbubble ';
            if($this->user->getLogin() == $message[0]->getData('jid')) {
                $tmp .= 'me ';
                if($message[0]->getData('public') == 1)
                    $tmp .= 'protect black';
                else
                    $tmp .= 'protect orange';
            }
        
            if($message[1]->getTrueName() == null)
                $name = $message[0]->getData('jid');
            else
                $name = $message[1]->getTrueName();
                    
            $tmp .= '">

                    <span>
                        <a href="?q=friend&f='.$message[0]->getData('jid').'">'.$name.'</a>
                    </span>
                    <span class="date">
                        '.prepareDate(strtotime($message[0]->getData('updated'))).'
                    </span>';                    
                    
            $tmp .= '<div class="content">
                        '.prepareString(html_entity_decode($message[0]->getData('content'))). '</div>';
                                    
            if($message[0]->getPlace() != false)
                $tmp .= '<span class="place">
                            <a 
                                target="_blank" 
                                href="http://www.openstreetmap.org/?lat='.$message[0]->getData('lat').'&lon='.$message[0]->getData('lon').'&zoom=10"
                            >'.$message[0]->getPlace().'</a>
                         </span>';
                         
            if($message[0]->getData('jid') != $message[0]->getData('uri'))
                $tmp .= '<span class="recycle">
                            <a href="?q=friend&f='.$message[0]->getData('uri').'">'.$message[0]->getData('name').'</a>
                         </span>';
                         
            $tmp .= '<div class="clear"></div>';
              
            if($message[0]->getData('commentson') == 1) {
                $tmp .= '<div class="comments" id="'.$message[0]->getData('nodeid').'comments">';

                $commentshtml = $this->prepareComments($comments);
                
                if($commentshtml != false)
                    $tmp .= $commentshtml;

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
                                style="border-bottom: none;"
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
            
              
            $tmp .= '
                </div>';
            
            if($this->user->getLogin() == $message[0]->getData('jid')) {
                $tmp .= '
                    <div class="tools">
                        '.t("Change the privacy level").' : 
                        <a
							title="'.t("your post will appear in your Movim public feed").'"
                            onclick="'.
                                $this->genCallAjax(
                                    'ajaxPrivacyPost', 
                                    "'".$this->user->getLogin()."'", 
                                    "'".$message[0]->getData('nodeid')."'",
                                    "'black'").'" >
                            '.t("Everyone").'</a>,
                        <a
                            onclick="'.
                                $this->genCallAjax(
                                    'ajaxPrivacyPost', 
                                    "'".$this->user->getLogin()."'", 
                                    "'".$message[0]->getData('nodeid')."'",
                                    "'orange'").'" >
                            '.t("Your contacts").'</a>
                        <a
                            style="float: right; display: none;";
                            id="deleteno"
                            onclick="
                                this.parentNode.querySelector(\'#deleteyes\').style.display = \'none\';
                                this.style.display = \'none\';
                                "
                            onclick="">
                            ✘ '.t("No").'
                        </a>
                        <a
                            style="float: right; padding-right: 1em; display: none;";
                            id="deleteyes"
                            onclick="'.
                                $this->genCallAjax(
                                    'ajaxDeletePost', 
                                    "'".$this->user->getLogin()."'", 
                                    "'".$message[0]->getData('nodeid')."'").'" >
                            ✔ '.t("Yes").' 
                        </a>
                        <a
                            style="float: right; padding-right: 1em;";
                            onclick="
                                this.parentNode.querySelector(\'#deleteyes\').style.display = \'inline\';
                                this.parentNode.querySelector(\'#deleteno\').style.display = \'inline\';
                                " 
                            title="'.t("Delete this post").'">
                            '.t("Delete this post").'
                        </a>


                    </div>';
            }
            $tmp .= '</div>';

        }
        return $tmp;
    }*/

    protected function prepareComments($comments) {
        $tmp = false;
        
        $size = sizeof($comments);
    
        $i = 0;
        while($i < $size-1) {
            if($comments[$i]->nodeid == $comments[$i+1]->nodeid)
                unset($comments[$i]);
            $i++;
        }
        
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
                $photo = $comment->getContact()->getPhoto('xs');
                $name = $comment->getContact()->getTrueName();
                                
                $tmp .= '
                    <div class="comment" ';
                if($comcounter > 0) {
                    $tmp .= 'style="display:none;"';
                    $comcounter--;
                }
                    
                $tmp .='>
                        <img class="avatar tiny" src="'.$photo.'">
                        <span><a href="?q=friend&f='.$comment->jid.'">'.$name.'</a></span>
                        <span class="date">'.prepareDate(strtotime($comment->published)).'</span><br />
                        <div class="content tiny">'.prepareString($comment->content).'</div>
                    </div>';
            }
        }
        return $tmp;
    }
    
    protected function prepareSubmitForm($server = '', $node = '') {
		$html = '
			<table id="feedsubmitform">
				<tbody>
					<tr>
						<td>
							<textarea 
								placeholder="'.t("What's new ?").'" 
								id="feedmessagecontent" 
								class="steditor"
								onkeyup="movim_textarea_autoheight(this);"></textarea>
						</td>
					</tr>
					
					<script type="text/javascript">
						var ste = new SimpleTextEditor("feedmessagecontent", "ste");
						ste.init();
					</script>
					
					<tr id="feedsubmitrow">
						<td>
							<a 
								title="Plus"
								href="#" 
								onclick="frameHeight(this);"
								style="float: left;"
								class="button tiny icon add merged left">'.t("Size").'
							</a>
							<a 
								title="Rich"
								href="#" 
								onclick="richText(this);"
								style="float: left;"
								class="button tiny icon yes merged right">'.t("Rich Text").'
							</a>
							<a 
								title="'.t("Submit").'"
								href="#" 
								id="feedmessagesubmit" 
								onclick="ste.submit();'.$this->genCallAjax('ajaxPublishItem', "'".$server."'", "'".$node."'",'getFeedMessage()').'; ste.clearContent();"
								class="button tiny icon submit">'.t("Submit").'
							</a>
						</td>
					</tr>
				</tbody>
			</table>';
                
                
		return $html;
	}
    
    function onComment($parent) {        
        $p = new \modl\ContactPostn();
        $p->nodeid = $parent;
        
        $pd = new \modl\PostnDAO();
        $comments = $pd->getComments($p);

        $html = $this->prepareComments($comments);
        RPC::call('movim_fill', $parent.'comments', $html);
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
        RPC::call('movim_fill', $parent.'comments', $html);
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
        RPC::call('movim_fill', $parent.'comments', $html);
    }
    
	function ajaxGetComments($jid, $id) {
		$c = new moxl\MicroblogCommentsGet();
        $c->setTo($jid)
          ->setId($id)
          ->request();
	}
    
    function ajaxPublishItem($server, $node, $content)
    {
        if($content != '') {
            $p = new moxl\PubsubPostPublish();
            $p->setFrom($this->user->getLogin())
              ->setTo($server)
              ->setNode($node)
              ->setContent(htmlspecialchars(rawurldecode($content)))
              ->request();
        }
    }
    
    function ajaxPublishComment($to, $id, $content) {
        if($content != '') {
            $p = new moxl\MicroblogCommentPublish();
            $p->setTo($to)
              ->setFrom($this->user->getLogin())
              ->setParentId($id)
              ->setContent(htmlspecialchars(rawurldecode($content)))
              ->request();
        }
    }
    
    function ajaxDeletePost($to, $id) {
        $p = new moxl\MicroblogPostDelete();
        $p->setTo($to)
          ->setId($id)
          ->request();
    }
    
    function ajaxPrivacyPost($to, $nodeid, $privacy) {
        $pd = new \modl\PostDAO();
        
        $p = $pd->get($nodeid);

        $p->renew();
        
        if($privacy == 'orange') {
            $p->public = 0;
            $pd->set($p);
        } elseif($privacy == 'black') {
            $p->public = 1;
            $pd->set($p);
        }
        
        RPC::call('movim_change_class', $nodeid.'bubble' , 'postbubble me protect '.$privacy);
        RPC::commit();
    }
    
    function ajaxPostFold($nodeid) {
        $pd = new \modl\PostDAO();
        $p = $pd->get($nodeid);
        
        $p->renew();

        $public = $p->public;
        
        if($public == 0) {
            $p->public = 2;
            $pd->set($p);
        } elseif($public != 0) {
            $p->public = 0;
            $pd->set($p);
        }

    }
    
    function onPostDelete($id) {
        RPC::call('movim_delete', $id);
    }
    
    function onPostDeleteError($params) {
        $html .=
            '<div class="message error">'.t('An error occured : ').$params[1].'</div>';
        RPC::call('movim_fill', $params[0] , $html);
    }
}
