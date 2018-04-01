<?php

namespace App;

use CoenJacobs\EloquentCompositePrimaryKeys\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use HasCompositePrimaryKey;

    protected $primaryKey = ['server', 'node'];
    public $incrementing = false;

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
                            $this->created = date(SQL::SQL_DATE, strtotime((string)$field->value));
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
                            foreach($field->children() as $value) {
                                $this->abuseaddresses[] = (string)$value;
                            }
                            break;
                        case 'admin-addresses':
                            foreach($field->children() as $value) {
                                $this->adminaddresses[] = (string)$value;
                            }
                            break;
                        case 'feedback-addresses':
                            foreach($field->children() as $value) {
                                $this->feedbackaddresses[] = (string)$value;
                            }
                            break;
                        case 'sales-addresses':
                            foreach($field->children() as $value) {
                                $this->salesaddresses[] = (string)$value;
                            }
                            break;
                        case 'security-addresses':
                            foreach($field->children() as $value) {
                                $this->securityaddresses[] = (string)$value;
                            }
                            break;
                        case 'support-addresses':
                            foreach($field->children() as $value) {
                                $this->supportaddresses[] = (string)$value;
                            }
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
}
