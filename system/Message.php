<?php

class Message extends StorageBase {
    protected $key;
    protected $jid;
    
    protected $nodeid;
    protected $title;
    protected $content;
    
    protected $published;
    protected $updated;
    
    protected function type_init() {
        $this->key      = StorageType::varchar(128);
        $this->jid      = StorageType::varchar(128);
        
        $this->nodeid       = StorageType::varchar(128);
        $this->title    = StorageType::varchar(128);
        $this->content  = StorageType::text();

        $this->published = StorageType::datetime();
        $this->updated   = StorageType::datetime();
    }
    
    public function getData($data) {
        return $this->$data->getval();
    }
}

class MessageHandler {
    function saveMessage($array, $jid, $from) {
        if(isset($jid) && isset($from) && isset($array['entry']['content'])) {
            global $sdb;
            $message = $sdb->select('Message', array(
                                                    'key' => $jid, 
                                                    'jid' => $from,
                                                    'nodeid'=> $array['@attributes']['id']));
                                                    
            if($message == false) {
                $message = new Message();
                $message->key = $jid;
                $message->jid = $from;
                $message->nodeid = $array['@attributes']['id'];
                $message->content = $array['entry']['content'];
                $message->published = date('Y-m-d H:i:s', strtotime($array['entry']['published']));
                $message->updated = date('Y-m-d H:i:s', strtotime($array['entry']['updated']));
                $sdb->save($message);
                
                $new = false;
                
            } else {
                $message = new Message();
                $sdb->load($message, array('key' => $jid, 
                                           'jid' => $from,
                                           'nodeid' => $array['@attributes']['id']));
                $message->content = $array['entry']['content'];
                $message->published = date('Y-m-d H:i:s', strtotime($array['entry']['published']));
                $message->updated = date('Y-m-d H:i:s', strtotime($array['entry']['updated']));
                $sdb->save($message); 
                
                $new = true;
            }
            
            return $new;
         }
    }
}
