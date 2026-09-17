<?php

namespace App;

use Awobaz\Compoships\Database\Eloquent\Model;
use Movim\ImageSize;

class Affiliation extends Model
{
    public $primaryKey = ['server', 'node', 'jid'];
    public $incrementing = false;
    public const TYPES = ['member', 'none', 'outcast', 'owner', 'publisher', 'publish-only'];

    public function contact()
    {
        return $this->hasOne(Contact::class, 'id', 'jid');
    }

    public function getAffiliationtextAttribute(): string
    {
        return getAffiliations()[$this->affiliation];
    }

    public function getPicture(ImageSize $size = ImageSize::M): string
    {
        return $this->contact
            ? $this->contact->getPicture($size)
            : avatarPlaceholder($this->jid);
    }
}
