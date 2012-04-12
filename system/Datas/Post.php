<?php

class Post extends DatajarBase {
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
    
    public function setPost($array, $from, $parent = false) {
        $user = new User();
        
        $this->key->setval($user->getLogin());
        if($parent != false)
            $this->jid->setval(substr($array['entry']['source']['author']['uri'], 5));
        else
            $this->jid->setval($from);
        
        $this->name->setval($array['entry']['source']['author']['name']);
        $this->uri->setval(substr($array['entry']['source']['author']['uri'], 5));
        $this->nodeid->setval($array['@attributes']['id']);
        $this->parentid->setval($parent);
        $this->content->setval($array['entry']['content']);
        $this->published->setval(date('Y-m-d H:i:s', strtotime($array['entry']['published'])));
        $this->updated->setval(date('Y-m-d H:i:s', strtotime($array['entry']['updated'])));

        $this->lat->setval($array['entry']['geoloc']['lat']);
        $this->lon->setval($array['entry']['geoloc']['lon']);
        $this->country->setval($array['entry']['geoloc']['country']);
        $this->countrycode->setval($array['entry']['geoloc']['countrycode']);
        $this->region->setval($array['entry']['geoloc']['region']);
        $this->postalcode->setval($array['entry']['geoloc']['postalcode']);
        $this->locality->setval($array['entry']['geoloc']['locality']);
        $this->street->setval($array['entry']['geoloc']['street']);
        $this->building->setval($array['entry']['geoloc']['building']);
        
        if(is_array($array['entry']['link'])) {
            foreach($array['entry']['link'] as $attachment) {
                if($attachment['link'][0]['@attributes']['title'] == 'thumb') {
                    AttachmentHandler::saveAttachment($attachment, $user->getLogin(), $from, $array['@attributes']['id']);
                }
            }
        }
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

class PostHandler {
    private $instance;

    public function __construct() {
    	$this->instance = new Post();
    }
    
    public function get($id) {
	    global $sdb;
        $sdb->load($this->instance, array('nodeid' => $id));
        return $this->instance;
    }
}
