<?php

/**
 * @file Utils.php
 * This file is part of PROJECT.
 * 
 * @brief Description
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 February 2011
 *
 * Copyright (C)2011 Etenil
 * 
 * All rights reserved.
 */

// Handy.
function println($string)
{
    $args = func_get_args();
    echo call_user_func_array('sprintf', $args) . PHP_EOL;
}

function sprintln($string)
{
    $args = func_get_args();
    return call_user_func_array('sprintf', $args) . PHP_EOL;
}

/**
 * Prepare the string (add the a the the links)
 *
 * @param string $string
 * @return string
 */
function prepareString($string) {
  return  preg_replace(
     array(
       '/(?(?=<a[^>]*>.+<\/a>)
             (?:<a[^>]*>.+<\/a>)
             |
             ([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+)
         )/iex',
       '/<a([^>]*)target="?[^"\']+"?/i',
       '/<a([^>]+)>/i',
       '/(^|\s)(www.[^<> \n\r]+)/iex',
       '/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)
       (\\.[A-Za-z0-9-]+)*)/iex'
       ),
     array(
       "stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\">\\2</a>\\3':'\\0'))",
       '<a\\1',
       '<a\\1 target="_blank">',
       "stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\">\\2</a>\\3':'\\0'))",
       "stripslashes((strlen('\\2')>0?'<a href=\"mailto:\\0\">\\0</a>':'\\0'))"
       ),
       $string
   );

}

function preparePost($message) {
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

        $tmp .= prepareComments($message);

        /*$tmp .= '
                <div class="comment">
                    <a class="getcomments icon bubble" style="margin-left: 0px;" onclick="'.$this->genCallAjax('ajaxGetComments', "'".$message->getData('jid')."'", "'".$message->getData('nodeid')."'").'; this.innerHTML = \''.t('Loading comments ...').'\'">'.t('Get the comments').'</a>
                </div>';*/
        $tmp .= '</div>';
          
        $tmp .= '</div>';

    }
    return $tmp;
}

function prepareComments($message) {
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

function prepareDate($time) {

    $today = strtotime(date('M j, Y'));
    $reldays = ($time - $today)/86400;

    if ($reldays >= 0 && $reldays < 1) {
        return t('Today') .' - '. date('H:i', $time);
    } else if ($reldays >= 1 && $reldays < 2) {
        return t('Tomorrow') .' - '. date('H:i', $time);
    } else if ($reldays >= -1 && $reldays < 0) {
        return t('Yesterday') .' - '. date('H:i', $time);
    }

    if (abs($reldays) < 7) {
        if ($reldays > 0) {
            $reldays = floor($reldays);
            return 'In ' . $reldays . ' '.t('day') . ($reldays != 1 ? 's' : '');
        } else {
            $reldays = abs(floor($reldays));
            return t(' %d days ago', $reldays); // . ' '.t('day') . ($reldays != 1 ? 's' : '') . ' ago';
        }
    }
    if (abs($reldays) < 182) {
        return date('l, j F',$time ? $time : time());
    } else {
        return date('l, j F, Y',$time ? $time : time());
    }
}

function movim_log($log) {
	ob_start();
//    var_dump($log);
	print_r($log);
	$dump = ob_get_clean();
	$fh = fopen(BASE_PATH . 'log/movim.log', 'w');
	fwrite($fh, $dump);
	fclose($fh);
}

?>
