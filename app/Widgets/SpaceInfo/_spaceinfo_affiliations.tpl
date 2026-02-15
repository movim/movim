
<form name="spaceinfo_affiliations">
    <div>
        <ul class="list flex">
            <li>
                <li class="subheader">
                    <div>
                        <p>{$c->__('communityaffiliation.roles')}</p>
                    </div>
                </li>
            </li>
            {loop="$affiliations"}
                <li>
                    <span class="primary icon bubble">
                        {if="$value->contact"}
                            <img src="{$value->contact->getPicture()}">
                        {else}
                            <img src="{getPicture(placeholder: $value->jid)}">
                        {/if}
                    </span>
                    <div>
                        <p class="line">
                            {if="$value->contact"}
                                {$value->contact->truename}
                            {else}
                                {$value->jid}
                            {/if}
                        </p>
                        <p class="line">
                            {$value->jid}
                        </p>
                    </div>
                </li>
                <li>
                    <div>
                        <div class="select">
                            <select name="{$value->jid}" id="role"
                                {if="$value->affiliation == 'owner' && $value->jid == $c->me->id"}disabled{/if}>
                                <option value="member" {if="$value->affiliation == 'member'"}selected="selected"{/if}>
                                    {$c->__('affiliation.member')}
                                </option>
                                <option value="owner" {if="$value->affiliation == 'owner'"}selected="selected"{/if}>
                                    {$c->__('affiliation.owner')}
                                </option>
                                <option value="none" {if="$value->affiliation == 'none'"}selected="selected"{/if}>
                                    {$c->__('spaceinfo.affiliation_none')}
                                </option>
                            </select>
                        </div>
                        <label for="role">{$c->__('room.role')}</label>
                    </div>
                </li>
            {/loop}
        </ul>
    </div>
</form>