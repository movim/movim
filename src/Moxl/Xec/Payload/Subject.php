<?php

namespace Moxl\Xec\Payload;

class Subject extends Payload
{
    public function handle($stanza, $parent = false) {        
        $jid = explode('/',(string)$parent->attributes()->from);
        $to = current(explode('/',(string)$parent->attributes()->to));

        if($parent->subject) {
            $m = new \modl\Message();

            $m->session     = $to;
            $m->jidto      = $to;
            $m->jidfrom    = $jid[0];

            if(isset($jid[1]))
                $m->resource = $jid[1];
            
            $m->type    = (string)$parent->attributes()->type;
            
            $m->body    = (string)$parent->body;
            $m->subject = (string)$parent->subject;

            if($parent->delay)
                $m->published = gmdate('Y-m-d H:i:s', strtotime($parent->delay->attributes()->stamp));
            else
                $m->published = gmdate('Y-m-d H:i:s');
            $m->delivered = date('Y-m-d H:i:s');

            $md = new \modl\MessageDAO();
            $md->set($m);

            $this->pack($m);
            $this->deliver();
        }
    }
}
