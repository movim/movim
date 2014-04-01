<?php

namespace Moxl\Stanza;

function messageMuc($to, $content)
{
    $session = \Sessionx::start();
    $xml = '
        <message to="'.str_replace(' ', '\40', $to).'" type="groupchat" id="'.$session->id.'">
            <body>'.$content.'</body>
        </message>';
    \Moxl\request($xml);
}
