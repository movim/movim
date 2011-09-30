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
