<?php

/**
 * Prepare the string (add the a to the links and show the smileys)
 *
 * @param string $string
 * @return string
 */
function prepareString($string) {
    $smileys = 
        array(
            // HFR icons
            ":'\("  => 'cry.gif',
            ':love:'=> 'love.gif',
            'O:\)'  => 'ange.gif',
            'O:-\)' => 'ange.gif',
            ':redface:' => 'redface.gif',
            ':petrus:'  => 'petrus75.gif',
            
            // famfamfam icons
            ':\)\)' => 'grin.png',
            ':\)'   => 'smiley.png',
            ':-\)'  => 'smiley.png',
            ':\('   => 'sad.png',
            ':o'    => 'shocked.png',
            ':O'    => 'shocked.png',
            ':D'    => 'grin.png',
            ':d'    => 'grin.png',
            ':p'    => 'tongue.png',
            ':P'    => 'tongue.png',
            ':-P'   => 'tongue.png',
            ';D'    => 'wink.png',
            ';d'    => 'wink.png',
            ';\)'   => 'wink.png',
            '\^\^'  => 'happy.png',
            '\(k\)' => 'heart.png',
            'B\)'   => 'cool.png',
            ':s'    => 'confused.png',
            ':S'    => 'confused.png',
            ':\/'   => 'wondering.png',
            ':evil:'=> 'evil.png',
            ":\|"   => 'neutral.png',
            
            // Meme icons
            ':okay:'        => 'okay.gif',
            ':trolldad:'    => 'trolldad.png',
            ':epic:'        => 'epic.png',
            ':aloneyeah:'   => 'aloneyeah.png',
            ':fapfap:'      => 'fapfap.png',
            ':megusta:'     => 'gusta.png',
            ':trollface:'   => 'trollface.png',
            ':troll:'       => 'trollface.png',
            ':lol:'         => 'trollol.png',
            ':genius:'      => 'genius.png',
        );

    //replace begin by www
    $string = preg_replace_callback(
            '/(^|\s|>)(www.[^<> \n\r]+)/ix', function ($match) {
                //print '<br />preg[1]';\system\Debug::dump($match);
                if (strlen($match[2])>0) {
                    return stripslashes($match[1].'<a href=\"http://'.$match[2].'\" target=\"_blank\">'.$match[2].'</a>');
                } else {
                    return $match[2];
                }
            }, ' ' . $string
    );

    //replace  begin by http - https (before www)
    $string = preg_replace_callback(
            '/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\'])((?:https?):\/\/([^<> \n\r]+)))/ix', function ($match) {
                if (isset($match[2]) && strlen($match[2])>0) {
                    return stripslashes($match[1].'<a href=\"'.$match[2].'\" target=\"_blank\">'.$match[3].'</a>');
                } else {
                    return $match[0];
                }
            }, ' ' . $string
    );
    
    // We remove all the style attributes
    $string = preg_replace_callback(
        '/(<[^>]+) style=".*?"/i', function($match) {
            return $match[1];
        }, $string    
    );
    
    // Twitter hashtags
    $string = preg_replace_callback(
        "/ #[a-zA-Z0-9_-]*/", function ($match) {
            return
                ' <a class="twitter hastag" href="http://twitter.com/search?q='.
                    urlencode(trim($match[0])).
                    '&src=hash" target="_blank">'.
                    trim($match[0]).
                '</a>';
        }, ' ' . $string
    );

    $string = preg_replace_callback(
        "/ @[a-zA-Z0-9_-]*/", function ($match) {
            return
                ' <a class="twitter at" href="http://twitter.com/'.
                    trim($match[0]).
                    '" target="_blank">'.
                    trim($match[0]).
                '</a>';
      }, ' ' . $string
    );

    //remove all scripts
    $string = preg_replace_callback(
            '#<[/]?script[^>]*>#is', function ($match) {
                return '';
            }, ' ' . $string
    );
    //remove all iframe
    $string = preg_replace_callback(
            '#<[/]?iframe[^>]*>#is', function ($match) {
                return '';
            }, ' ' . $string
    );
   
    // We add some smileys...
    $cd = new \Modl\ConfigDAO();
    $config = $cd->get();
    $theme = $config->theme;
    
    $path = BASE_URI . 'themes/' . $theme . '/img/smileys/';

    foreach($smileys as $key => $value) {
        $replace = ' <img class="smiley" alt="smiley" src="'.$path.$value.'">';
        $string = preg_replace('/(^|[ ])('.$key.')/',  $replace, $string);
    }
    
    return trim($string);
}


/**
 * Fix self-closing tags
 */
function fixSelfClosing($string) {
    return preg_replace_callback('/<([^\s<]+)\/>/',
        function($match) {
            return '<'.$match[1].'></'.$match[1].'>';
        }
        , $string);
}

/**
 * Remove the content, body and html tags
 */
function cleanHTMLTags($string) {
    return str_replace(
        array(
            '<content type="html">',
            '<html xmlns="http://jabber.org/protocol/xhtml-im">',
            '<body xmlns="http://www.w3.org/1999/xhtml">',
            '</body>',
            '</html>', 
            '</content>'),
        '',
        $string);
}

/**
 * Return an array of informations from a XMPP uri
 */
function explodeURI($uri) {
    $arr = parse_url(urldecode($uri));
    $result = array();
    
    if(isset($arr['query'])) {
        $query = explode(';', $arr['query']);


        foreach($query as $elt) {
            if($elt != '') {
                list($key, $val) = explode('=', $elt);
                $result[$key] = $val;
            }
        }

        $arr = array_merge($arr, $result);
    }
    
    return $arr;

}

/*
 * Echap the JID 
 */
function echapJid($jid)
{
    return str_replace(' ', '\40', $jid);
}

/*
 * Clean the ressource of a jid
 */
function cleanJid($jid)
{
    return reset(explode('/', $jid));
}

/**
 * Return a URIfied string
 * @param string
 * @return string
 */
function stringToUri($url) {
    $url = utf8_decode($url);
    $url = strtolower(strtr($url, utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ()[]\'"~$&%*@ç!?;,:/\^¨€{}<>|+- '),  'aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn    --      c  ---    e      --'));
    $url = str_replace(' ', '', $url);
    $url = str_replace('---', '-', $url);
    $url = str_replace('--', '-', $url);
    $url = trim($url,'-');
    return $url;
}

/**
 * Return a human readable filesize
 * @param string size in bytes
 * @return string
 */
function sizeToCleanSize($size)
{
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}
