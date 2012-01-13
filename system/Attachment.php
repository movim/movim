<?php

class Attachment extends StorageBase {
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
        $this->key      = StorageType::varchar(128);
        $this->jid      = StorageType::varchar(128);
        
        $this->node      = StorageType::varchar(128);
        $this->attachmentid      = StorageType::varchar(128);
             
        $this->title = StorageType::varchar(128);
        $this->type = StorageType::varchar(128);
        
        $this->length  = StorageType::int();

        $this->link = StorageType::varchar(128);
        $this->thumb = StorageType::varchar(128);
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
