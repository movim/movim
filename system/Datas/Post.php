<?php

class Post extends DatajarBase {
    public $key;
    public $jid;

    public $name;
    public $uri; 
    public $nodeid;
    public $parentid;
    public $title;
    public $content;

    public $published;
    public $updated;

    public $lat;
    public $lon;
    public $country;
    public $countrycode;
    public $region;
    public $postalcode;
    public $locality;
    public $street;
    public $building;
    
    public $commentson;
    public $commentplace;
    
    public $public;

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
        
        $this->public      = DatajarType::int();
    }
    
    public function setPost($item, $from, $parent = false, $key = false) {
        if($key == false) {
            $user = new User();
            $key = $user->getLogin();
        }
        
        $this->key->setval($key);
        
        if($parent != false)
            $this->jid->setval(substr((string)$item->entry->source->author->uri, 5));
        else
            $this->jid->setval($from);
        
        $this->name->setval((string)$item->entry->source->author->name);
        $this->uri->setval(substr((string)$item->entry->source->author->uri, 5));
        $this->nodeid->setval((string)$item->attributes()->id);
        
        if($parent)
            $this->parentid->setval($parent);
        
        if($item->entry->title)
            $content = (string)$item->entry->title;        
        elseif($item->entry->content)
            $content = (string)$item->entry->content;
        elseif($item->entry->body)
            $content = (string)$item->entry->body;

        $this->published->setval(date('Y-m-d H:i:s', strtotime((string)$item->entry->published)));
        
        if(!$item->entry->updated)
            $this->updated->setval(date('Y-m-d H:i:s', strtotime((string)$item->entry->published)));
        else
            $this->updated->setval(date('Y-m-d H:i:s', strtotime((string)$item->entry->updated)));

        $this->lat->setval((string)$item->entry->geoloc->lat);
        $this->lon->setval((string)$item->entry->geoloc->lon);
        $this->country->setval((string)$item->entry->geoloc->country);
        $this->countrycode->setval((string)$item->entry->geoloc->countrycode);
        $this->region->setval((string)$item->entry->geoloc->region);
        $this->postalcode->setval((string)$item->entry->geoloc->postalcode);
        $this->locality->setval((string)$item->entry->geoloc->locality);
        $this->street->setval((string)$item->entry->geoloc->street);
        $this->building->setval((string)$item->entry->geoloc->building);
        
        $contentimg = '';
        
        foreach($item->entry->link as $attachment) {
            if(isset($attachment->link[0]) && (string)$attachment->link[0]->attributes()->title == 'thumb') {
                $contentimg .= '
                    <a href="'.(string)$attachment->attributes()->href.'" target="_blank" class="imglink"><img title="'.(string)$attachment->attributes()->title.'" src="'.(string)$attachment->link[0]->attributes()->href.'"/></a>';
            }
            
            if((string)$attachment->attributes()->title == 'comments') {
                $this->commentson->setval(1);
                $this->commentplace->setval(reset(explode('?', substr((string)$attachment->attributes()->href, 5))));
            }
        }
        
        if($contentimg != '')
            $content .= '<br />'.$contentimg;
        
        $this->content->setval($content);
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
    
    public function get($jid, $id) {
	    global $sdb;
        $sdb->load($this->instance, array('key' => $jid, 'nodeid' => $id));
        return $this->instance;
    }
}
