<?php

namespace Moxl\Stanza;

class MAM {
    static function get($jid, $start = false, $end = false, $limit = false)
    {
        $param = '';

        if($start) {
            $param .= '
                <field var="start">
                    <value>'.date('Y-m-d\TH:i:s\Z', $start+1).'</value>
                </field>';
        }

        if($end) {
            $param .= '
                <field var="end">
                    <value>'.date('Y-m-d\TH:i:s\Z', $end+1).'</value>
                </field>';
        }

        if($limit) {
            $param .= '
                <field var="limit">
                    <value>'.$limit.'</value>
                </field>';
        }

        $xml = '
            <query xmlns="urn:xmpp:mam:0">
                <x xmlns="jabber:x:data">
                    <field var="FORM_TYPE">
                        <value>urn:xmpp:mam:0</value>
                    </field>
                    <field var="with">
                        <value>'.$jid.'</value>
                    </field>

                    '.$param.'
                </x>
            </query>
        ';

        $xml = \Moxl\API::iqWrapper($xml, null, 'set');
        \Moxl\API::request($xml);
    }
}
