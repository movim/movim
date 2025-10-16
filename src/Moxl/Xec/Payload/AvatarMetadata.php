<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Avatar\Get;

class AvatarMetadata extends Payload
{
    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $jid = baseJid((string)$parent->attributes()->from);
        $infos = $stanza->xpath('//info[not(@url)]/@id');

        if (is_array($infos) && !empty($infos)) {
            $c = \App\Contact::firstOrNew(['id' => $jid]);

            if ((string)$infos[0] != $c->avatarhash) {
                $g = new Get;
                $g->setTo($jid)
                    ->request();
            }
        }

        // TODO handle Avatar URLs
    }
}
