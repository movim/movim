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

            if($post->privacy == 1){
                $flagcolor='black';
                $access .= 'protect black';
            }
            else{
                $flagcolor='orange';
                $access .= 'protect orange';
            }
                
            $avatar = $post->getContact()->getPhoto('s');
        } elseif($post->public == 2) 
            $avatar = $post->getContact()->getPhoto('xs');
        else 
            $avatar = $post->getContact()->getPhoto('s');
        
        if(!filter_var($post->from, FILTER_VALIDATE_EMAIL) && $post->node != '')
            $group = '
                <span class="group">
                    <a href="'.Route::urlize('node', array($post->from, $post->node)).'">'.$post->node.' ('.$post->from.')</a>
                </span>';
        elseif($post->from != $post->aid)
            $recycle .= '
                <span class="recycle">
                    <a href="'.Route::urlize('friend', $post->from).'">'.$post->from.'</a>
                 </span>';

        if($post->getPlace() != false)
            $place .= '
                <span class="place">
                    <a 
                        target="_blank" 
                        href="http://www.openstreetmap.org/?lat='.$post->lat.'&lon='.$post->lon.'&zoom=10"
                    >'.t('Place').'</a>
                </span>';

        if($post->jid != '')
			$c = '
				<span>
					<a href="'.Route::urlize('friend', $post->jid).'">'.$post->getContact()->getTrueName().'</a>
				</span>';
        elseif($post->aid != '')
			$c = '
				<span>
					<a href="'.Route::urlize('friend', $post->aid).'">'.$post->aid.'</a>
				</span>';
                
        if($post->links)
			$enc = $this->printEnclosures($post->links);
           
        if($post->tags)
            $tags = $this->printTags($post->tags);
                
        if($enc)
			$enc = '
				<div class="enclosure">'.
					$enc.
				'
                    <div class="clear"></div>
                </div>';

        $content = prepareString($post->content);
        
        if($post->node == 'urn:xmpp:microblog:0')
            $comments = $this->printComments($post, $comments, $public);
        else
			$comments = '';
            
        if($this->user->getLogin() == $post->jid) 
            $toolbox = $this->getToolbox($post);
        
        $html = '
            <div class="post '.$class.'" id="'.$post->nodeid.'">
                <div class="'.$access.'" title="'.getFlagTitle($flagcolor).'" style="z-index:1;"></div>
                <a href="'.Route::urlize('friend', $post->jid).'">
                    <img class="avatar" src="'.$avatar.'">
                </a>

                <div id="'.$post->nodeid.'bubble" class="postbubble">
					<div class="header">
						<span class="title">'.$title.'</span>
						'.$c.'
						<span class="date">
							'.prepareDate(strtotime($post->published)).'
						</span>
                    </div>
                    <div class="content">
                    '.$content.'<br />
                    </div>
                    '.$tags.'
					'.$toolbox.'
                    '.$enc.'
                    '.$comments.'
                    '.$place.'
                    '.$recycle.'
                    '.$group.'
                </div>  
                        
            </div>
            ';
        return $html;
    }
    
    private function printTags($tags) {
        $html = '<br />';
        
        $tags = unserialize($tags);
        foreach($tags as $t)
            $html .= '<span class="tag">'.$t.'</span>';
            
        return $html;
    }
    
    private function printEnclosures($links) {
		$enc = '';
		$links = unserialize($links);

		foreach($links as $l) {
			if($l['rel'] == 'enclosure') {
				if(isset($l['thumb']))
					$enc .= '
						<a href="'.$l['href'].'" class="imglink" target="_blank">
							<img src="'.$l['thumb']['href'].'"/>
						</a>
					';
				else
					$enc .= '
						<a href="'.$l['href'].'" class="imglink" target="_blank">
							<img src="'.$l['href'].'"/>
						</a>';
			} elseif($l['rel'] == 'alternate' && isset($l['title'])) {
				$enc .= '
					<a href="'.$l['href'].'" class="imglink" target="_blank">
						'.$l['title'].'
					</a>';
			}
		}
		
		return $enc;
	}
    
    private function getToolbox($post) {
        $html = '
            <div class="tools">
                '.t("Share with").' : 
                <a
                    title="'.t("your post will appear in your Movim public feed").'"
                    onclick="'.
                        $this->genCallAjax(
                            'ajaxPrivacyPost', 
                            "'".$post->nodeid."'",
                            "'black'").
                '" >
                    '.t("Everyone").'</a>,
                <a
                    onclick="'.
                        $this->genCallAjax(
                            'ajaxPrivacyPost', 
                            "'".$post->nodeid."'",
                            "'orange'").
                '" >
                    '.t("Your contacts").'</a><br />
                <a
                    style="padding-right: 1em;";
                    onclick="
                        this.parentNode.querySelector(\'#deleteyes\').style.display = \'inline\';
                        this.parentNode.querySelector(\'#deleteno\').style.display = \'inline\';
                        " 
                    title="'.t("Delete this post").'">
                    '.t("Delete this post").'
                </a>
                <a
                    style="padding-right: 1em; display: none;";
                    id="deleteyes"
                    onclick="'.
                        $this->genCallAjax(
                            'ajaxDeletePost', 
                            "'".$this->user->getLogin()."'",
                            "'".$post->node."'",
                            "'".$post->nodeid."'").'" >
                    ✔ '.t("Yes").' 
                </a>
                <a
                    style="display: none;";
                    id="deleteno"
                    onclick="
                        this.parentNode.querySelector(\'#deleteyes\').style.display = \'none\';
                        this.style.display = \'none\';
                        "
                    onclick="">
                    ✘ '.t("No").'
                </a>
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
                                        class="getcomments icon chat" 
                                        onclick="'.$this->genCallAjax('ajaxGetComments', "'".$post->commentplace."'", "'".$post->nodeid."'").'; this.innerHTML = \''.t('Loading comments ...').'\'">'.
                                            t('Get the comments').'
                                    </a>
                                </div></div>';
                    $tmp .= '<div class="comments">
                                <div 
                                    class="comment"
                                    onclick="this.parentNode.querySelector(\'#commentsubmit\').style.display = \'table\'; this.style.display =\'none\'">
                                    <a class="addcomment icon chat">'.t('Add a comment').'</a>
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
                                                class="button color green icon yes"
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
                        <a class="getcomments icon chat">'.t('Show the older comments').'</a>
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
                        <span><a href="'.Route::urlize('friend', $comment->jid).'">'.$name.'</a></span>
                        <span class="date">'.prepareDate(strtotime($comment->published)).'</span><br />
                        <div class="content tiny">'.prepareString($comment->content).'</div>
                    </div>';
            }
        }
        return $tmp;
    }
    
    protected function prepareSubmitForm($server = '', $node = '') {
		$html = '
			<script type="text/javascript">
				function showPosition(poss) {
					'.$this->genCallAjax('ajaxShowPosition', "poss").'
				}
			</script>
            
            <div class="popup post" id="postpreview">
				<div class="content" id="postpreviewcontent">

				</div>
				<a 
					class="button color icon no" 
					onclick="
						movim_toggle_display(\'#postpreview\');"
				>'.t('Close').'</a>
            </div>
			<table id="feedsubmitform">
				<tbody>
					<form name="postpublish" id="postpublish">
						<tr>
							<td>
								<textarea 
									name="content" 
									id="postpublishcontent"
									onkeyup="movim_textarea_autoheight(this);" 
									placeholder="'.t("What's new ?").'" ></textarea>
							</td>
						</tr>
						<tr id="feedsubmitrow">
							<td>
								<input type="hidden" id="latlonpos" name="latlonpos"/>
								<a 
									title="'.t("Submit").'"
									href="#" 
									id="feedmessagesubmit" 
									onclick="'.$this->genCallAjax('ajaxPublishItem', "'".$server."'", "'".$node."'","movim_parse_form('postpublish')").';
											document.querySelector(\'#postpublish\').reset();
											movim_textarea_autoheight(document.querySelector(\'#postpublishcontent\'));"
									class="button icon color green icon yes">'.
									t("Submit").'
								</a>
								<a 
									class="button icon color alone merged left preview"
									style="float: left;"
									title="'.t('Preview').'"
									onclick="
										movim_toggle_display(\'#postpreview\');
										'.$this->genCallAjax('ajaxPostPreview', "document.querySelector('#postpublishcontent').value").'"
								></a>

								<!--<a 
									title="Plus"
									href="#"
									id="postpublishsize"
									onclick="frameHeight(this, document.querySelector(\'#postpublishcontent\'));"
									style="float: left;"
									class="button color icon alone add merged"
                                ></a>--><a 
									class="button color icon alone help merged" 
									style="float: left;"
                                    href="http://daringfireball.net/projects/markdown/basics"
									target="_blank"
								></a><a title="'.t("Geolocalisation").'"
									onclick="setPosition(document.querySelector(\'#latlonpos\'));"
									style="float: left;"
									class="button icon color icon alone geo merged right"></a>
								<span id="postpublishlocation"></span>

							</td>
						</tr>
					</form>
				</tbody>
			</table>';
                
                
		return $html;
	}
	
	function ajaxShowPosition($pos)
	{
		list($lat,$lon) = explode(',', $pos);	
		
		$pos = json_decode(
					file_get_contents('http://nominatim.openstreetmap.org/reverse?format=json&lat='.$lat.'&lon='.$lon.'&zoom=27&addressdetails=1')
				);

        RPC::call('movim_fill', 'postpublishlocation' , (string)$pos->display_name);
        RPC::commit();
	}

	function ajaxPostPreview($content)
	{
		if($content != '') {
			$content = Michelf\Markdown::defaultTransform($content);
			RPC::call('movim_fill', 'postpreviewcontent' , $content);
		} else
			RPC::call('movim_fill', 'postpreviewcontent' , t('No content'));

		RPC::commit();
	}
	
    function ajaxPublishItem($server, $node, $form)
    {
		$content = $form['content'];

		list($lat,$lon) = explode(',', $form['latlonpos']);
		
		$pos = json_decode(
					file_get_contents('http://nominatim.openstreetmap.org/reverse?format=json&lat='.$lat.'&lon='.$lon.'&zoom=27&addressdetails=1')
				);
				
		$geo = array(
			'latitude'      => (string)$pos->lat,
			'longitude'     => (string)$pos->lon,
			'altitude'      => (string)$pos->alt,
			'country'       => (string)$pos->address->country,
			'countrycode'   => (string)$pos->address->country_code,
			'region'        => (string)$pos->address->county,
			'postalcode'    => (string)$pos->address->postcode,
			'locality'      => (string)$pos->address->city,
			'street'        => (string)$pos->address->path,
			'building'      => (string)$pos->address->building,
			'text'          => (string)$pos->display_name,
			'uri'           => ''//'http://www.openstreetmap.org/'.urlencode('?lat='.(string)$pos->lat.'&lon='.(string)$pos->lon.'&zoom=10')
			);
			
        if($content != '') {
			$content = Michelf\Markdown::defaultTransform($content);

            $p = new moxl\PubsubPostPublish();
            $p->setFrom($this->user->getLogin())
              ->setTo($server)
              ->setNode($node)
              ->setLocation($geo)
              ->setContentHtml(rawurldecode($content))
              ->enableComments()
              ->request();
        }
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
                    class="getcomments icon chat" >'.
                    t('No comments').
                '</a>
            </div>';
        RPC::call('movim_fill', $parent.'comments', $html);
    }
    
    function onNoCommentStream($parent) { 
        $html = '
            <div class="comment">
                <a 
                    class="getcomments icon chat" >'.
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
    
    function ajaxDeletePost($to, $node, $id) {
        $p = new moxl\PubsubPostDelete();
        $p->setTo($to)
          ->setNode($node)
          ->setId($id)
          ->request();
    }
    
    function ajaxPrivacyPost($nodeid, $privacy) {
        $pd = new \modl\PrivacyDAO();
        
        $p = $pd->get($nodeid);

        if($privacy == 'orange') {
			\modl\Privacy::set($nodeid, 0);
        } elseif($privacy == 'black') {
			\modl\Privacy::set($nodeid, 1);
        }

        RPC::call('movim_change_class', $nodeid , 'protect '.$privacy, getFlagTitle($privacy));
        RPC::commit();
    }
    
    /*function onPostDelete($id) {
        RPC::call('movim_delete', $id);
    }
    
    function onPostDeleteError($params) {
        $html .=
            '<div class="message error">'.t('An error occured : ').$params[1].'</div>';
        RPC::call('movim_fill', $params[0] , $html);
    }*/
}
