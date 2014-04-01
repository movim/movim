<?php

namespace Moxl\Stanza;

function locationPublish($to, $geo)
{
    $xml = '
        <pubsub xmlns="http://jabber.org/protocol/pubsub">
            <publish node="http://jabber.org/protocol/geoloc">
                <item>
                    <geoloc xmlns="http://jabber.org/protocol/geoloc">
                        <lat>'.$geo['latitude'].'</lat>
                        <lon>'.$geo['longitude'].'</lon>
                        <altitude>'.$geo['altitude'].'</altitude>
                        <country>'.$geo['country'].'</country>
                        <countrycode>'.$geo['countrycode'].'</countrycode>
                        <region>'.$geo['region'].'</region>
                        <postalcode>'.$geo['postalcode'].'</postalcode>
                        <locality>'.$geo['locality'].'</locality>
                        <street>'.$geo['street'].'</street>
                        <building>'.$geo['building'].'</building>
                        <text>'.$geo['text'].'</text>
                        <uri>'.$geo['uri'].'</uri>
                        <timestamp>'.date('c').'</timestamp>
                    </geoloc>
                </item>
            </publish>
        </pubsub>';
    $xml = \Moxl\iqWrapper($xml, $to, 'set');
    \Moxl\request($xml);
}
