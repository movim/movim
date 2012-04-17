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
    
    protected $commentson;
    protected $commentplace;

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
        
        $this->commentson  = DatajarType::int();
        $this->commentplace= DatajarType::varchar(128);
    }
    
    public function setPost($array, $from, $parent = false, $key = false) {
        
        if($key == false) {
            $user = new User();
            $key = $user->getLogin();
        }
        
        $this->key->setval($key);
        
        if($parent != false)
            $this->jid->setval(substr($array['entry']['source']['author']['uri'], 5));
        else
            $this->jid->setval($from);
        
        $this->name->setval($array['entry']['source']['author']['name']);
        $this->uri->setval(substr($array['entry']['source']['author']['uri'], 5));
        $this->nodeid->setval($array['@attributes']['id']);
        $this->parentid->setval($parent);
        
        if(isset($array['entry']['content']))
            $this->content->setval($array['entry']['content']);
        elseif(isset($array['entry']['body']))
            $this->content->setval($array['entry']['body']);

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
                    AttachmentHandler::saveAttachment($attachment, $key, $from, $array['@attributes']['id']);
                }
                if($attachment['@attributes']['title'] == 'comments') {
                    $this->commentson->setval(1);
                    $this->commentplace->setval(reset(explode('?', substr($attachment['@attributes']['href'], 5))));
                }
            }
        }
    }
    
    public function setNoComments() {
        $this->commentson->setval(0);
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
