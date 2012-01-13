<?php

class Message extends StorageBase {
    protected $key;
    protected $jid;
    
    protected $nodeid;
    protected $title;
    protected $content;
    
    protected $published;
    protected $updated;
    
    protected $lat;
    protected $lon;
    protected $country;
    protected $countrycode;
    protected $region;
    protected $postalcode;
    protected $locality;
    protected $street;
    protected $building;
    
    protected function type_init() {
        $this->key      = StorageType::varchar(128);
        $this->jid      = StorageType::varchar(128);
        
        $this->nodeid   = StorageType::varchar(128);
        $this->title    = StorageType::varchar(128);
        $this->content  = StorageType::text();

        $this->published = StorageType::datetime();
        $this->updated   = StorageType::datetime();
        
        $this->lat         = StorageType::varchar(128);
        $this->lon         = StorageType::varchar(128);
        $this->country     = StorageType::varchar(128);
        $this->countrycode = StorageType::varchar(128);
        $this->region      = StorageType::varchar(128);
        $this->postalcode  = StorageType::varchar(128);
        $this->locality    = StorageType::varchar(128);
        $this->street      = StorageType::varchar(128);
        $this->building    = StorageType::varchar(128);
    }
    
    public function getData($data) {
        return $this->$data->getval();
    }
    
    public function getPlace() {
        if(isset($this->lat, $this->lon) && $this->lat->getval() != '' && $this->lon->getval() != '') {
            return $this->locality->getval().', '.$this->region->getval().' -  '.$this->country->getval();
        }
        else 
            return false;
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
                
                $message->lat = $array['entry']['geoloc']['lat'];
                $message->lon = $array['entry']['geoloc']['lon'];
                $message->country = $array['entry']['geoloc']['country'];
                $message->countrycode = $array['entry']['geoloc']['countrycode'];
                $message->region = $array['entry']['geoloc']['region'];
                $message->postalcode = $array['entry']['geoloc']['postalcode'];
                $message->locality = $array['entry']['geoloc']['locality'];
                $message->street = $array['entry']['geoloc']['street'];
                $message->building = $array['entry']['geoloc']['building'];
                
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
                
                $message->lat = $array['entry']['geoloc']['lat'];
                $message->lon = $array['entry']['geoloc']['lon'];
                $message->country = $array['entry']['geoloc']['country'];
                $message->countrycode = $array['entry']['geoloc']['countrycode'];
                $message->region = $array['entry']['geoloc']['region'];
                $message->postalcode = $array['entry']['geoloc']['postalcode'];
                $message->locality = $array['entry']['geoloc']['locality'];
                $message->street = $array['entry']['geoloc']['street'];
                $message->building = $array['entry']['geoloc']['building'];
                
                if(is_array($array['entry']['link'])) {
                    foreach($array['entry']['link'] as $attachment) {
                        if($attachment['link'][0]['@attributes']['title'] == 'thumb') {
                            AttachmentHandler::saveAttachment($attachment, $jid, $from, $array['@attributes']['id']);
                        }
                    }
                }
                
                $sdb->save($message); 
                
                $new = true;
            }
            
            return $new;
         }
    }
}
