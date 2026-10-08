<?php

namespace Moxl\Xec\Action\Muc;

use App\Member;
use Carbon\Carbon;
use Moxl\Stanza\Muc;
use Moxl\Xec\Action;

class SetAffiliations extends Action
{
    protected string $_to;

    protected array $_affiliations = [];

    protected ?string $_reason = null;

    private const AFFILIATIONS = ['owner', 'admin', 'member', 'outcast', 'none'];

    public function request()
    {
        $this->store();
        $this->iq(Muc::changeAffiliations($this->_affiliations, $this->_reason), to: $this->_to, type: 'set');
    }

    public function setAffiliations(array $affiliations)
    {
        foreach ($affiliations as $jid => $affiliation) {
            if (in_array($affiliation, SetAffiliations::AFFILIATIONS)) {
                $this->_affiliations[$jid] = $affiliation;
            }
        }

        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        foreach ($this->_affiliations as $jid => $affiliation) {
            Member::where('conference', $this->_to)
                ->where('jid', $jid)
                ->update(['affiliation' => $affiliation, 'updated_at' => Carbon::now()]);
        }

        $this->pack($this->_affiliations, (string) $stanza->attributes()->from);
        $this->deliver();
    }

    public function errorNotAllowed(string $errorId, ?string $message = null)
    {
        $this->deliver();
    }
}
