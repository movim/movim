<?php

class Message extends DatajarBase {
    protected $key;
    protected $jid;

    protected $name;
    protected $uri; 
    protected $nodeid;
    protected $parentid;
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
        $this->key      = DatajarType::varchar(128);
        $this->jid      = DatajarType::varchar(128);

        $this->name     = DatajarType::varchar(128);
        $this->uri      = DatajarType::varchar(128);
        $this->nodeid   = DatajarType::varchar(128);
        $this->parentid = DatajarType::varchar(128);
        $this->title    = DatajarType::varchar(128);
        $this->content  = DatajarType::text();

        $this->published = DatajarType::datetime();
        $this->updated   = DatajarType::datetime();

        $this->lat         = DatajarType::varchar(128);
        $this->lon         = DatajarType::varchar(128);
        $this->country     = DatajarType::varchar(128);
        $this->countrycode = DatajarType::varchar(128);
        $this->region      = DatajarType::varchar(128);
        $this->postalcode  = DatajarType::varchar(128);
        $this->locality    = DatajarType::varchar(128);
        $this->street      = DatajarType::varchar(128);
        $this->building    = DatajarType::varchar(128);
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
    private $instance;

    public function __construct() {
    	$this->instance = new Message();
    }
    
    function saveMessage($array, $jid, $from, $parent = false) {

        if(isset($jid) && isset($from) && isset($array['entry']['content'])) {
            if($parent != false)
                $from = substr($array['entry']['source']['author']['uri'], 5);

            global $sdb;
            $message = $sdb->select('Message', array(
                                                    'key' => $jid,
                                                    'jid' => $from,
                                                    'nodeid'=> $array['@attributes']['id']));

            if($message == false) {
                $message = new Message();
                $message->key = $jid;
                $message->jid = $from;
                
                $message->name = $array['entry']['source']['author']['name'];
                $message->uri = substr($array['entry']['source']['author']['uri'], 5);
                $message->nodeid = $array['@attributes']['id'];
                $message->parentid = $parent;
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

                $new = false;

            } else {
                $message = new Message();
                $sdb->load($message, array('key' => $jid,
                                           'jid' => $from,
                                           'nodeid' => $array['@attributes']['id']));
                $message->name = $array['entry']['source']['author']['name'];
                $message->uri = substr($array['entry']['source']['author']['uri'], 5);
                $message->parentid = $parent;
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
