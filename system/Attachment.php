<?php

class Attachment extends DatajarBase {
    protected $key;
    protected $jid;
    
    protected $node;
    protected $attachmentid;
    
    protected $title;
    protected $type;
    
    protected $length;
    
    protected $link;
    protected $thumb;
    
    protected function type_init() {
        $this->key      = DatajarType::varchar(128);
        $this->jid      = DatajarType::varchar(128);
        
        $this->node      = DatajarType::varchar(128);
        $this->attachmentid      = DatajarType::varchar(128);
             
        $this->title = DatajarType::varchar(128);
        $this->type = DatajarType::varchar(128);
        
        $this->length  = DatajarType::int();

        $this->link = DatajarType::varchar(128);
        $this->thumb = DatajarType::varchar(128);
    }
    
    public function getData($data) {
        return $this->$data->getval();
    }
}

class AttachmentHandler {
    function getAttachment($node) {
        global $sdb;
        $attachment = $sdb->select('Attachment', array('node'=> $node));
        if($attachment != false)
            return $attachment;
        else
            return false;
    }

    function saveAttachment($array, $jid, $from, $node) {
        global $sdb;
        $attachment = $sdb->select('Attachment', array(
                                                'key' => $jid, 
                                                'jid' => $from,
                                                'node'=> $node,
                                                'attachmentid' => sha1($array['@attributes']['href'].
                                                                       $array['@attributes']['title'].
                                                                       $array['@attributes']['length'])
                                                      ));
                                                
        if($attachment == false) {
            $attachment = new Attachment();
            $attachment->key = $jid;
            $attachment->jid = $from;
            
            $attachment->node = $node;
            $attachment->attachmentid = sha1($array['@attributes']['href'].
                                             $array['@attributes']['title'].
                                             $array['@attributes']['length']);
            
            $attachment->title = $array['@attributes']['title'];
            $attachment->type = $array['@attributes']['type'];
            
            $attachment->length = $array['@attributes']['length'];
            
            $attachment->link = $array['@attributes']['href'];
            $attachment->thumb = $array['link'][0]['@attributes']['href'];
            
            $sdb->save($attachment);
        } else {
            global $sdb;
            $attachment = new Attachment();
            $sdb->load($attachment, array(
                                                    'key' => $jid, 
                                                    'jid' => $from,
                                                    'node'=> $node,
                                                    'attachmentid' => sha1($array['@attributes']['href'].
                                                                           $array['@attributes']['title'].
                                                                           $array['@attributes']['length'])
                                                          ));
                                                
            $attachment->title = $array['@attributes']['title'];
            $attachment->type = $array['@attributes']['type'];
            
            $attachment->length = $array['@attributes']['length'];
            
            $attachment->link = $array['@attributes']['href'];
            $attachment->thumb = $array['link'][0]['@attributes']['href'];
            
            $sdb->save($attachment);

        }
    }
}
