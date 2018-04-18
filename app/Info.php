<?php

namespace App;

use CoenJacobs\EloquentCompositePrimaryKeys\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use HasCompositePrimaryKey;

    protected $primaryKey = ['server', 'node'];
    public $incrementing = false;

    public function setAdminaddressesAttribute(array $arr)
    {
        $this->attributes['adminaddresses'] = serialize($arr);
    }

    public function getAdminaddressesAttribute()
    {
        return (isset($this->attributes['adminaddresses']))
            ? unserialize($this->attributes['adminaddresses'])
            : null;
    }

    public function setAbuseaddressesAttribute(array $arr)
    {
        $this->attributes['abuseaddresses'] = serialize($arr);
    }

    public function getAbuseaddressesAttribute()
    {
        return (isset($this->attributes['abuseaddresses']))
            ? unserialize($this->attributes['abuseaddresses'])
            : null;
    }

    public function setFeedbackaddressesAttribute(array $arr)
    {
        $this->attributes['feedbackaddresses'] = serialize($arr);
    }

    public function getFeedbackaddressesAttribute()
    {
        return (isset($this->attributes['feedbackaddresses']))
            ? unserialize($this->attributes['feedbackaddresses'])
            : null;
    }

    public function setSalesaddressesAttribute(array $arr)
    {
        $this->attributes['salesaddresses'] = serialize($arr);
    }

    public function getSalesaddressesAttribute()
    {
        return (isset($this->attributes['salesaddresses']))
            ? unserialize($this->attributes['salesaddresses'])
            : null;
    }
    public function setSecurityaddressesAttribute(array $arr)
    {
        $this->attributes['securityaddresses'] = serialize($arr);
    }

    public function getSecurityaddressesAttribute()
    {
        return (isset($this->attributes['securityaddresses']))
            ? unserialize($this->attributes['securityaddresses'])
            : null;
    }

    public function setSupportaddressesAttribute(array $arr)
    {
        $this->attributes['supportaddresses'] = serialize($arr);
    }

    public function getSupportaddressesAttribute()
    {
        return (isset($this->attributes['supportaddresses']))
            ? unserialize($this->attributes['supportaddresses'])
            : null;
    }

    public function set($query)
    {
        $from = (string)$query->attributes()->from;

        if (strpos($from, '/') == false
        && isset($query->query)) {
            $this->server   = $from;
            $this->node     = (string)$query->query->attributes()->node;

            foreach ($query->query->identity as $i) {
                if ($i->attributes()) {
                    $this->category = (string)$i->attributes()->category;
                    $this->type     = (string)$i->attributes()->type;

                    if ($i->attributes()->name) {
                        $this->name = (string)$i->attributes()->name;
                    } else {
                        $this->name = $this->node;
                    }
                }
            }

            foreach ($query->query->feature as $feature) {
                $key = (string)$feature->attributes()->var;

                switch ($key) {
                    case 'muc_public':
                        $this->mucpublic = true;
                        break;
                    case 'muc_persistent':
                        $this->mucpersistent = true;
                        break;
                    case 'muc_passwordprotected':
                        $this->mucpasswordprotected = true;
                        break;
                    case 'muc_membersonly':
                        $this->mucpasswordprotected = true;
                        break;
                    case 'muc_moderated':
                        $this->mucmoderated = true;
                        break;
                }
            }

            if (isset($query->query->x)) {
                foreach($query->query->x->field as $field) {
                    $key = (string)$field->attributes()->var;
                    switch ($key) {
                        case 'pubsub#title':
                            $this->name = (string)$field->value;
                            break;
                        case 'pubsub#creation_date':
                            $this->created = date(SQL_DATE, strtotime((string)$field->value));
                            break;
                        case 'muc#roominfo_description':
                        case 'pubsub#description':
                            if (!empty((string)$field->value)) {
                                $this->description = (string)$field->value;
                            }
                            break;
                        case 'pubsub#num_subscribers':
                        case 'muc#roominfo_occupants':
                            $this->occupants = (int)$field->value;
                            break;

                        case 'abuse-addresses':
                            $arr = [];
                            foreach($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->abuseaddresses = $arr;
                            break;
                        case 'admin-addresses':
                            $arr = [];
                            foreach($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->adminaddresses = $arr;
                            break;
                        case 'feedback-addresses':
                            $arr = [];
                            foreach($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->feedbackaddresses = $arr;
                            break;
                        case 'sales-addresses':
                            $arr = [];
                            foreach($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->salesaddresses = $arr;
                            break;
                        case 'security-addresses':
                            $arr = [];
                            foreach($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->securityaddresses = $arr;
                            break;
                        case 'support-addresses':
                            $arr = [];
                            foreach($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->supportaddresses = $arr;
                            break;
                    }
                }
            }
        }
    }

    public function setItem($item)
    {
        $this->server = (string)$item->attributes()->jid;
        $this->node   = (string)$item->attributes()->node;
        $this->name   = (string)$item->attributes()->name;
    }

    public function isPubsubService()
    {
        return ($this->category == 'pubsub' && $this->type == 'service');
    }

    public function isMicroblogCommentsNode()
    {
        return (substr($this->node, 0, 29) == 'urn:xmpp:microblog:0:comments');
    }
}
