<?php

namespace Moxl\Stanza;

class Muc {
    static function message($to, $content)
    {
        $session = \Sessionx::start();
        $xml = '
            <message to="'.str_replace(' ', '\40', $to).'" type="groupchat" id="'.$session->id.'">
                <body>'.$content.'</body>
            </message>';
        \Moxl\API::request($xml);
    }
}
