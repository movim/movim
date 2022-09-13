<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    protected $fillable = ['server', 'node', 'avatarhash'];
    protected $with = ['identities'];

    private $freshIdentities;

    public function identities()
    {
        return $this->hasMany('App\Identity');
    }

    public function save(array $options = [])
    {
        // Empty features, we're not saving anything
        if ($this->isEmptyFeatures() && empty($this->freshIdentities)) return;

        try {
            unset($this->identities);
            parent::save($options);

            if ($this->freshIdentities) {
                $this->identities()->delete();
                $this->identities()->saveMany($this->freshIdentities);
            }

        } catch (\Exception $e) {
            /**
             * Existing info are saved in the DB
             */
        }
    }

    public function scopeWhereCategory($query, $category)
    {
        return $query->whereHas('identities', function($query) use ($category) {
            $query->where('category', $category);
        });
    }

    public function scopeWhereType($query, $type)
    {
        return $query->whereHas('identities', function($query) use ($type) {
            $query->where('type', $type);
        });
    }

    public function scopeRestrictUserHost($query)
    {
        $configuration = Configuration::get();

        if ($configuration->restrictsuggestions) {
            $query->whereIn('server', function ($query) {
                $host = \App\User::me()->session->host;
                $query->select('server')
                      ->from('infos')
                      ->where('server', 'like', '%.' . $host);
            });
        }
    }

    public function setAdminaddressesAttribute(array $arr)
    {
        $this->attributes['adminaddresses'] = serialize($arr);
    }

    public function getAdminaddressesAttribute(): array
    {
        return (isset($this->attributes['adminaddresses']))
            ? unserialize($this->attributes['adminaddresses'])
            : [];
    }

    public function setAbuseaddressesAttribute(array $arr)
    {
        $this->attributes['abuseaddresses'] = serialize($arr);
    }

    public function getAbuseaddressesAttribute(): array
    {
        return (isset($this->attributes['abuseaddresses']))
            ? unserialize($this->attributes['abuseaddresses'])
            : [];
    }

    public function setFeedbackaddressesAttribute(array $arr)
    {
        $this->attributes['feedbackaddresses'] = serialize($arr);
    }

    public function getFeedbackaddressesAttribute(): array
    {
        return (isset($this->attributes['feedbackaddresses']))
            ? unserialize($this->attributes['feedbackaddresses'])
            : [];
    }

    public function setSalesaddressesAttribute(array $arr)
    {
        $this->attributes['salesaddresses'] = serialize($arr);
    }

    public function getSalesaddressesAttribute(): array
    {
        return (isset($this->attributes['salesaddresses']))
            ? unserialize($this->attributes['salesaddresses'])
            : [];
    }
    public function setSecurityaddressesAttribute(array $arr)
    {
        $this->attributes['securityaddresses'] = serialize($arr);
    }

    public function getSecurityaddressesAttribute(): array
    {
        return (isset($this->attributes['securityaddresses']))
            ? unserialize($this->attributes['securityaddresses'])
            : [];
    }

    public function setSupportaddressesAttribute(array $arr)
    {
        $this->attributes['supportaddresses'] = serialize($arr);
    }

    public function getSupportaddressesAttribute(): array
    {
        return (isset($this->attributes['supportaddresses']))
            ? unserialize($this->attributes['supportaddresses'])
            : [];
    }

    public function getFeaturesAttribute(): array
    {
        return (isset($this->attributes['features']))
            ? unserialize($this->attributes['features'])
            : [];
    }

    public function isEmptyFeatures(): bool
    {
        return empty($this->getFeaturesAttribute());
    }

    public function getNameAttribute()
    {
        return isset($this->attributes['name'])
            ? $this->attributes['name']
            : $this->attributes['node'];
    }

    public function getRelatedAttribute()
    {
        // Dangerous for now
        /*if ($this->identities->contains('category', 'pubsub') && $this->identities->contains('type', 'leaf')) {
            return \App\Info::where('related', 'xmpp:'.$this->server.'?;node='.$this->node)
                ->first();
        }*/

        if (isset($this->attributes['related'])
        && $this->identities->contains('category', 'conference') && $this->identities->contains('type', 'text')) {
            $uri = parse_url($this->attributes['related']);

            if (isset($uri['query']) && isset($uri['path'])) {
                $params = explodeQueryParams($uri['query']);

                if (isset($params['node'])) {
                    return \App\Info::where('server', $uri['path'])
                        ->where('node', $params['node'])
                        ->first();
                }
            }
        }
    }

    /**
     * Only for gateways
     */
    public function getPresenceAttribute()
    {
        return \App\User::me()->session->presences
                    ->where('jid', $this->attributes['server'])
                    ->first();
    }

    public function getPhoto($size = 'm')
    {
        return isset($this->attributes['avatarhash'])
            ? getPhoto($this->attributes['avatarhash'], $size)
            : null;
    }

    public function getDeviceIcon()
    {
        if ($this->identities->contains('type', 'handheld')
        || $this->identities->contains('type', 'phone')) {
            return 'smartphone';
        }
        if ($this->identities->contains('type', 'bot')) {
            return 'memory';
        }
        if ($this->identities->contains('type', 'console')) {
            return 'video_label';
        }
        if ($this->identities->contains('type', 'web')) {
            if ($this->name == 'Movim') {
                return 'cloud_queue';
            }

            return 'language';
        }

        return 'desktop_windows';
    }

    public function hasFeature(string $feature)
    {
        return (in_array($feature, unserialize($this->attributes['features'])));
    }

    public function isJingleAudio()
    {
        return $this->hasFeature('urn:xmpp:jingle:apps:rtp:audio')
            && $this->hasFeature('urn:xmpp:jingle-message:0');
    }

    public function isJingleVideo()
    {
        return $this->hasFeature('urn:xmpp:jingle:apps:rtp:video')
            && $this->hasFeature('urn:xmpp:jingle-message:0');
    }

    public function isMAM()
    {
        return $this->hasFeature('urn:xmpp:mam:1');
    }

    public function isMAM2()
    {
        return $this->hasFeature('urn:xmpp:mam:2');
    }

    public function hasExternalServices()
    {
        return $this->hasFeature('urn:xmpp:extdisco:2');
    }

    public function set($query, $node = false, $parent = false)
    {
        $from = (string)$query->attributes()->from;

        if (isset($query->query)) {
            $this->server   = strpos($from, '/') == false ? $from : null;
            $this->node     = (string)$query->query->attributes()->node;
            $this->parent   = $parent == false ? null : $parent;

            /**
             * Enforce node, it seems that some servers and clients doesn't
             * returns the node attribute when answering a caps…
             * - Slixmpp
             * - bitlbee
             * - jtalk
             */
            if (empty($this->node) && $node != false) {
                $this->node = $node;
            }
            $this->freshIdentities = collect();

            foreach ($query->query->identity as $i) {
                $identity = new Identity;
                $identity->category = (string)$i->attributes()->category;
                $identity->type     = (string)$i->attributes()->type;

                $this->freshIdentities->push($identity);
                $this->name = ($i->attributes()->name)
                    ? (string)$i->attributes()->name
                    : $this->node;
            }


            $features = [];
            foreach ($query->query->feature as $feature) {
                switch ((string)$feature->attributes()->var) {
                    case 'muc_public':
                        $this->mucpublic = true;
                        break;
                    case 'muc_hidden':
                        $this->mucpublic = false;
                        break;
                    case 'muc_persistent':
                        $this->mucpersistent = true;
                        break;
                    case 'muc_temporary':
                        $this->mucpersistent = false;
                        break;
                    case 'muc_passwordprotected':
                        $this->mucpasswordprotected = true;
                        break;
                    case 'muc_unsecured':
                        $this->mucpasswordprotected = false;
                        break;
                    case 'muc_membersonly':
                        $this->mucmembersonly = true;
                        break;
                    case 'muc_open':
                        $this->mucmembersonly = false;
                        break;
                    case 'muc_moderated':
                        $this->mucmoderated = true;
                        break;
                    case 'muc_unmoderated':
                        $this->mucmoderated = false;
                        break;
                    case 'muc_semianonymous':
                        $this->mucsemianonymous = true;
                        break;
                    case 'muc_nonanonymous':
                        $this->mucsemianonymous = false;
                        break;
                }

                array_push($features, (string)$feature->attributes()->var);
            }
            $this->attributes['features'] = serialize($features);

            if (isset($query->query->x)) {
                foreach ($query->query->x->field as $field) {
                    switch ((string)$field->attributes()->var) {
                        case 'pubsub#title':
                            $this->name = (string)$field->value;
                            break;
                        case 'pubsub#creation_date':
                            $this->created = toSQLDate($field->value);
                            break;
                        case 'pubsub#access_model':
                            $this->pubsubaccessmodel = (string)$field->value;
                            break;
                        case 'pubsub#publish_model':
                            $this->pubsubpublishmodel = (string)$field->value;
                            break;
                        case 'muc#roominfo_pubsub':
                            if (!empty((string)$field->value)) {
                                $this->related = (string)$field->value;
                            }
                            break;
                        case 'muc#roominfo_description':
                        case 'pubsub#description':
                        case 'max-file-size': // https://xmpp.org/extensions/xep-0363.html#disco
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
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->abuseaddresses = $arr;
                            break;
                        case 'admin-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->adminaddresses = $arr;
                            break;
                        case 'feedback-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->feedbackaddresses = $arr;
                            break;
                        case 'sales-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->salesaddresses = $arr;
                            break;
                        case 'security-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->securityaddresses = $arr;
                            break;
                        case 'support-addresses':
                            $arr = [];
                            foreach ($field->children() as $value) {
                                $arr[] = (string)$value;
                            }
                            $this->supportaddresses = $arr;
                            break;
                    }
                }
            }
        }
    }

    public function setPubsubItem($item)
    {
        $this->server = (string)$item->attributes()->jid;
        $this->node   = (string)$item->attributes()->node;

        if ($item->attributes()->name) {
            $this->name   = (string)$item->attributes()->name;
        }

        $this->freshIdentities = collect();
        $identity = new Identity;
        $identity->category = 'pubsub';
        $identity->type     = 'leaf';

        $this->freshIdentities->push($identity);
    }

    public function getPubsubRoles()
    {
        $roles = ['owner' => __('affiliation.owner'), 'none' =>  __('affiliation.no-aff')];

        $features = unserialize($this->attributes['features']);

        if (is_array($features)) {
            foreach ($features as $feature) {
                preg_match("/http:\/\/jabber.org\/protocol\/pubsub#(.*)-affiliation$/", $feature, $matches);
                if (!empty($matches)) {
                    $roles[$matches[1]] = __('affiliation.' . $matches[1]);
                }
            }
        }

        return $roles;
    }

    public function isPubsubService()
    {
        return ($this->identities->contains('category', 'pubsub')
             && $this->identities->contains('type', 'service'));
    }

    public function isMicroblogCommentsNode()
    {
        return (substr($this->node, 0, 29) == 'urn:xmpp:microblog:0:comments');
    }
}
