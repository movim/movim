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
 *
 */

use \Rain\Tpl;
use \Michelf\Markdown;

use Moxl\Xec\Action\Pubsub\PostPublish;
use Moxl\Xec\Action\Pubsub\PostDelete;
use Moxl\Xec\Action\Microblog\CommentPublish;
use Moxl\Xec\Action\Microblog\CommentsGet;

class WidgetCommon extends WidgetBase {
    private function loadTemplate() {
        $config = array(
            'tpl_dir'       => APP_PATH.'widgets/WidgetCommon/',
            'cache_dir'     => CACHE_PATH,
            'tpl_ext'       => 'tpl',
            'auto_escape'   => false
        );

        // We load the template engine
        $view = new Tpl;
        $view->objectConfigure($config);
        $view->assign('c', $this);
        
        return $view;
    }
    
    protected function printPost($post, $comments = false, $public = false) {
        // Initialize the variables
        $class = $title = $access = $flagcolor = $group = $c = 
        $tags = $toolbox = $place = $recycle = '';

        if($post->title)
            $title = $post->title;
            
        $spoiler = false;
            
        if(current(explode('.', $post->jid)) == 'nsfw')
            $spoiler = ' movim_toggle_class(this, \'show\')';
            
        if($this->user->getLogin() == $post->aid) {
            $class = 'me ';

            if($post->privacy == 1){
                $flagcolor='black';
                $access .= 'protect black';
            }
            else{
                $flagcolor='orange';
                $access .= 'protect orange';
            }

            $avatar = $post->getContact()->getPhoto('m');
        } elseif(isset($post->aid)) {
            $avatar = $post->getContact()->getPhoto('m', $post->aid);
        // A little hack which display a colored avatar for the Groups
        } elseif(!filter_var($post->jid, FILTER_VALIDATE_EMAIL) && $post->node != '') {
            $avatar = $post->getContact()->getPhoto('m', $post->node);
        } else
            $avatar = $post->getContact()->getPhoto('m');
            
        if(!filter_var($post->jid, FILTER_VALIDATE_EMAIL) && $post->node != '')
            $group = '
                <span class="group">
                    <a href="'.Route::urlize('node', array($post->jid, $post->node)).'">'.$post->node.' ('.$post->jid.')</a>
                </span>';
        elseif($post->jid != $post->aid)
            $recycle .= '
                <span class="recycle">
                    <a href="'.Route::urlize('friend', $post->jid).'">'.$post->jid.'</a>
                 </span>';

        if($post->getPlace() != false)
            $place .= '
                <span class="place">
                    <a 
                        target="_blank" 
                        href="http://www.openstreetmap.org/?lat='.$post->lat.'&lon='.$post->lon.'&zoom=10"
                    >'.__('post.place').'</a>
                </span>';

        if(filter_var($post->jid, FILTER_VALIDATE_EMAIL) && $post->jid != '')
            $c = '
                <span>
                    <a href="'.Route::urlize('friend', $post->jid).'">'.$post->getContact()->getTrueName().'</a>
                </span>';
        elseif($post->getContact()->getTrueName() != '' && filter_var($post->aid, FILTER_VALIDATE_EMAIL))
            $c = '
                <span>
                    <a href="'.Route::urlize('friend', $post->aid).'">'.$post->getContact()->getTrueName().'</a>
                </span>';
        elseif($post->aid != '' && filter_var($post->aid, FILTER_VALIDATE_EMAIL))
            $c = '
                <span>
                    <a href="'.Route::urlize('friend', $post->aid).'">'.$post->aid.'</a>
                </span>';
                
        if($post->links)
            $enc = $this->printEnclosures($post->links);
           
        if($post->tags)
            $tags = $this->printTags($post->tags);
                
        if(isset($enc) && $enc != '') {
            $enc = '
                <div class="enclosures">'.
                    $enc.
                '
                    <div class="clear"></div>
                </div>';
        } else
            $enc = '';

        $author = $this->prepareAuthor($post);

        $content = $post->contentcleaned;
        if(!isset($content))
            $content = prepareString(html_entity_decode($post->content));
        
        if($post->node == 'urn:xmpp:microblog:0')
            $comments = $this->printComments($post, $comments, $public);
        else
            $comments = '';
        
        if($this->user->getLogin() == $post->aid) 
            $toolbox = $this->getToolbox($post);
            
        $view = $this->loadTemplate();
        $view->assign('idhash',     md5($post->nodeid));
        $view->assign('id',         $post->nodeid);
        $view->assign('class',      $class);
        $view->assign('access',     $access);
        $view->assign('spoiler',    $spoiler);
        $view->assign('flagtitle',  getFlagTitle($flagcolor));
        if(filter_var($post->jid, FILTER_VALIDATE_EMAIL))
            $view->assign('friend',     Route::urlize('friend', $post->jid));
        elseif(!filter_var($post->jid, FILTER_VALIDATE_EMAIL) && $post->node != '')
            $view->assign('friend',     Route::urlize('node', array($post->jid, $post->node)));
        else
            $view->assign('friend',     '#');
        $view->assign('avatar',     '<img class="avatar" src="'.$avatar.'"/>');
        $view->assign('title',      $title);
        $view->assign('contact',    $c);
        $view->assign('date',       prepareDate(strtotime($post->published)));
        
        $view->assign('content',    $content);
        $view->assign('tags',       $tags);
        $view->assign('toolbox',    $toolbox);
        $view->assign('enc',        $enc);
        $view->assign('comments',   $comments);
        $view->assign('place',      $place);
        $view->assign('recycle',    $recycle);
        $view->assign('group',      $group);
        
        $view->assign('author',     $author);
        
        $html = $view->draw('_post', true);

        return $html;
    }
    
    private function printTags($tags) {
        $html = '';
        
        $tags = unserialize($tags);
        foreach($tags as $t)
            $html .= '<span class="tag">'.$t.'</span>';
            
        return $html;
    }
    
    private function printEnclosures($links) {
        $enc = '';
        $links = unserialize($links);

        foreach($links as $l) {
            /*
            if(isset($l['rel'])
                && $l['rel'] == 'enclosure'
                && $l['type'] != 'text/html') {
                $enc .= '
                    <a href="'.$l['href'].'" class="imglink" target="_blank">
                        <img src="'.$l['href'].'"/>
                    </a>';
            } elseif(
                isset($l['rel'])
                && $l['rel'] == 'alternate' 
                && isset($l['title'])) {
                $enc .= '
                    <a href="'.$l['href'].'" class="imglink" target="_blank">
                        '.$l['title'].'
                    </a>';
            } elseif(isset($l['href'])) {
                $url = parse_url($l['href']);
                if(substr($l['href'], 0, 5) != 'xmpp:') {
                    if($url['host'] == 'www.youtube.com') {
                        $enc .= '
                        <a href="'.$l['href'].'" target="_blank">
                            <img src="http://img.youtube.com/vi/'.substr($url['query'], 2, 11).'/1.jpg"/>
                            <img src="http://img.youtube.com/vi/'.substr($url['query'], 2, 11).'/2.jpg"/>
                            <img src="http://img.youtube.com/vi/'.substr($url['query'], 2, 11).'/3.jpg"/>
                        </a><br />';
                    }

                    if($url['host'] == 'youtu.be') {
                        $enc .= '
                        <a href="'.$l['href'].'" target="_blank">
                            <img src="http://img.youtube.com/vi/'.substr($url['path'], 1, 11).'/1.jpg"/>
                            <img src="http://img.youtube.com/vi/'.substr($url['path'], 1, 11).'/2.jpg"/>
                            <img src="http://img.youtube.com/vi/'.substr($url['path'], 1, 11).'/3.jpg"/>
                        </a><br />';
                    }

                    $enc .= '
                        <a href="'.$l['href'].'" class="imglink" target="_blank">
                            <img class="icon" src="https://duckduckgo.com/i/'.$url['host'].'.ico"/>'.$url['scheme'].'://'.$url['host'].$url['path'].'
                        </a><br />';
                }
            }
            */

            switch($l['rel']) {
                case 'enclosure' :
                    if(in_array($l['type'], array('image/jpeg', 'image/png', 'image/jpg'))) {
                        $enc .= '
                            <a
                                href="'.$l['href'].'"
                                class="enclosure"
                                type="'.$l['type'].'"
                                target="_blank"><img
                                    src="'.$l['href'].'"/>
                            </a>';
                    /*} elseif(in_array($l['type'], array('audio/mpeg', 'audio/ogg'))) {
                        $enc .= '
                            <audio controls>
                                <source src="'.$l['href'].'" type="'.$l['type'].'">
                            </audio> ';
                    }*/} else {
                        $enc .= '
                            <a
                                href="'.$l['href'].'"
                                class="enclosure"
                                type="'.$l['type'].'"
                                target="_blank">'.$l['href'].'
                            </a>';
                    }
                    break;
                case 'alternate' :
                    $url = parse_url($l['href']);

                    if(!isset($url['host']))
                        $url['host'] = '';
                    
                    $enc .= '
                        <a
                            href="'.$l['href'].'"
                            class="alternate"
                            target="_blank"><img
                                src="http://g.etfv.co/'.$l['href'].'"/>'.$url['scheme'].'://'.$url['host'].$url['path'].'
                        </a>';
                    break;
            }
        }

        return $enc;
    }
    
    private function prepareAuthor($post) {
        $html = $content = '';
        if($post->aname != null) {
            $content .= ' <span>'.__('post.by').'</span> '.$post->aname;
        }

        if($post->aemail != null) {
            $content .= ' <span>'.__('post.email').'</span> '.$post->aemail;
        }

        if($post->aid != null) {
            $content .= ' <span>'.__('post.jid').'</span> '.$post->aid;
        }
        
        if($content .= '')
            $html .= '<div class="author">'.$content.'</div>';
        
        return $html;
    }
    
    private function getToolbox($post) {
        $view = $this->loadTemplate();
        
        $view->assign(
            'privacy_post_orange', 
            $this->genCallAjax(
                'ajaxPrivacyPost', 
                "'".$post->nodeid."'",
                "'orange'"));
                
        $view->assign(
            'privacy_post_black', 
            $this->genCallAjax(
                'ajaxPrivacyPost', 
                "'".$post->nodeid."'",
                "'black'"));
                
        $view->assign(
            'delete_post', 
            $this->genCallAjax(
                'ajaxDeletePost', 
                "'".$post->jid."'",
                "'".$post->node."'",
                "'".$post->nodeid."'"));
                
        $html = $view->draw('_post_toolbox', true);
        
        return $html;
    }
    
    protected function printComments($post, $comments, $public = false) {
        $view = $this->loadTemplate();
        $view->assign('post',       $post);
        $view->assign('comments',   $this->prepareComments($comments));
        $view->assign('getcomments',
            $this->genCallAjax(
                'ajaxGetComments', 
                "'".$post->commentplace."'", 
                "'".$post->nodeid."'")
            );
            
        $view->assign('publishcomment',
            $this->genCallAjax(
                'ajaxPublishComment', 
                "'".$post->commentplace."'", 
                "'".$post->nodeid."'", 
                "encodeURIComponent(document.getElementById('".$post->nodeid."commentcontent').value)")
            );
        
        $html = $view->draw('_comments_toolbox', true);

        return $html;

    }
    
    protected function printMap($posts, $c = null) {
        $html = '<div style="height: 13em;" id="postsmap"></div>';
        
        $javascript = '
            <script type="text/javascript">
            var postsmap = L.map("postsmap").setView([40,0], 2);
            
            L.tileLayer("http://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, <a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, Mapnik ©",
                maxZoom: 18
            }).addTo(postsmap);';
        
        $id = 0;
        
        $posfound = false;
        
        $bound = '';
        
        foreach($posts as $post) {
            if($post->getPlace() != false) {
                $posfound = true;
                
                $javascript .= "
                    var marker".$id." = L.marker([".$post->lat.",".$post->lon."]).addTo(postsmap);
                    marker".$id.".on('click', function() {document.location = '#".md5($post->nodeid)."'});
                    "; 
                    
                $bound .= '['.$post->lat.','.$post->lon.'],';
                    
                $id++;
            }
        }
        
        if(isset($c) && $c->loclatitude != '' && $c->loclongitude != '') {    
            $posfound = true;          
            
            $popup  = '<img style=\'float: left; margin-right: 1em;\' src=\''.$c->getPhoto('s').'\'/>';
            $popup .= '<div style=\'padding: 0.5em;\'>'.$c->getPlace().'<br />'.prepareDate(strtotime($c->loctimestamp)).'</div>';
            $popup .= '<div class=\'clear\'></div>';
            
            $javascript .= '
                var red = L.icon({
                    iconUrl: "'.BASE_URI.'/themes/movim/img/marker-icon.png",
                    iconSize:     [25,41], // size of the icon
                    shadowSize:   [50, 64], // size of the shadow
                    iconAnchor:   [13, 41]
                });
            
                var marker = L.marker(['.$c->loclatitude.' ,'.$c->loclongitude.'], {icon: red}).addTo(postsmap);
                marker.bindPopup("'.$popup.'").openPopup();
                ';
             $bound .= '['.$c->loclatitude.','.$c->loclongitude.'],';
        }
        
        $javascript .= '
        postsmap.fitBounds(['.$bound.']);
        </script>';
        
        if($posfound)
            return $html.$javascript;
        else
            return '';
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

            $pd = new \Modl\PostnDAO();
            $comments = $pd->getComments($posts);

            foreach($posts as $post) {
                // We split the interesting comments for each messages
                $i = 0;
                
                $messagecomment = array();
                
                if(isset($comments)) {
                    foreach($comments as $comment) {
                        if('urn:xmpp:microblog:0:comments/'.$post->nodeid == $comments[$i]->node) {
                            array_push($messagecomment, $comment);
                            unset($comment);
                        }
                        $i++;
                    }
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
            $tmp = '<article
                        class="comment"
                        onclick="
                            com = this.parentNode.querySelectorAll(\'.comment\'); 
                            for(i = 0; i < com.length; i++) { com.item(i).style.display = \'block\';};
                            this.style.display = \'none\';">
                        <div class="comments">
                            <a class="getcomments">
                                <i class="fa fa-history"></i> '.__('post.comments_older').'
                            </a>
                        </div>
                    </article>';
            $comcounter = $size - 3;
        }
        
        if($comments) {
            foreach($comments as $comment) {
                $photo = $comment->getContact()->getPhoto('xs', $comment->aid);
                $name = $comment->getContact()->getTrueName();
                                
                $tmp .= '
                    <article class="comment" ';
                if($comcounter > 0) {
                    $tmp .= 'style="display:none;"';
                    $comcounter--;
                }
                    
                $tmp .='>
                        <header>
                            <img src="'.$photo.'">
                            <span><a href="'.Route::urlize('friend', $comment->aid).'">'.$name.'</a></span>
                            <span class="date">'.prepareDate(strtotime($comment->published)).'</span>
                        </header>
                        <section>'.prepareString($comment->content).'</section>
                    </article>';
            }
        }
        return $tmp;
    }
    
    protected function prepareSubmitForm($server = '', $node = '') {  
        $view = $this->loadTemplate();
                
        $view->assign('toggle_position', $this->genCallAjax('ajaxShowPosition', "poss"));
        $view->assign('gallery', $this->prepareGallery());
        
        $view->assign(
            'publish_item', 
            $this->genCallAjax(
                'ajaxPublishItem', 
                "'".$server."'", 
                "'".$node."'",
                "movim_parse_form('postpublish')"));
                
        $view->assign(
            'post_preview',
            $this->genCallAjax(
                'ajaxPostPreview', 
                "document.querySelector('#postpublishcontent').value"));
                
        $html = $view->draw('_submit_form', true);
                
        return $html;
    }

    private function prepareGallery() {
        $arr = array();
        $p = new \Picture;

        foreach($this->user->getDir() as $pic) {
            array_push($arr,
                array(
                    'thumb' => $p->get($this->user->userdir.$pic, 300),
                    'uri'   => $p->get($this->user->userdir.$pic)
                    )
                );
        }

        return $arr;
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
            $content = Markdown::defaultTransform($content);
            RPC::call('movim_fill', 'postpreviewcontent' , $content);
        } else
            RPC::call('movim_fill', 'postpreviewcontent' , __('post.empty'));

        RPC::commit();
    }
    
    function ajaxPublishItem($server, $node, $form)
    {
        $content = $form['content'];
        $title   = $form['title'];

        $geo = false;

        if(isset($form['latlonpos']) && $form['latlonpos'] != '') {
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
        }

        if($content != '') {
            $content = Markdown::defaultTransform($content);
            
            $p = new PostPublish;
            $p->setFrom($this->user->getLogin())
              ->setTo($server)
              ->setNode($node)
              ->setLocation($geo)
              ->setTitle($title)
              ->setContentHtml(rawurldecode($content))
              ->enableComments()
              ->request();
        }
        
    }
    
    function onComment($parent) {        
        $p = new \Modl\ContactPostn();
        $p->nodeid = $parent;
        
        $pd = new \Modl\PostnDAO();
        $comments = $pd->getComments($p);

        $html = $this->prepareComments($comments);
        RPC::call('movim_fill', $parent.'comments', $html);
    }
    
    function onNoComment($parent) {     
        $html = '
            <div class="comment">
                <a 
                    class="getcomments" >
                    <i class="fa fa-comments-o"></i> '.__('post.no_comments').
                '</a>
            </div>';
        RPC::call('movim_fill', $parent.'comments', $html);
    }
    
    function onNoCommentStream($parent) { 
        $html = '
            <div class="comment">
                <a 
                    class="getcomments" >
                    <i class="fa fa-comments-o"></i> '.__('post.no_comments_stream').
                '</a>
            </div>';
        RPC::call('movim_fill', $parent.'comments', $html);
    }
    
    function ajaxGetComments($jid, $id) {
        $c = new CommentsGet;
        $c->setTo($jid)
          ->setId($id)
          ->request();
    }
    
    function ajaxPublishComment($to, $id, $content) {
        if($content != '') {
            $p = new CommentPublish;
            $p->setTo($to)
              ->setFrom($this->user->getLogin())
              ->setParentId($id)
              ->setContent(htmlspecialchars(rawurldecode($content)))
              ->request();
        }
    }
    
    function ajaxDeletePost($to, $node, $id) {
        $p = new PostDelete;
        $p->setTo($to)
          ->setNode($node)
          ->setId($id)
          ->request();
    }
    
    function ajaxPrivacyPost($nodeid, $privacy) {
        $pd = new \Modl\PrivacyDAO();
        
        $p = $pd->get($nodeid);

        if($privacy == 'orange') {
            \Modl\Privacy::set($nodeid, 0);
        } elseif($privacy == 'black') {
            \Modl\Privacy::set($nodeid, 1);
        }

        RPC::call('movim_change_class', $nodeid , 'protect '.$privacy, getFlagTitle($privacy));
        RPC::commit();
    }
}
