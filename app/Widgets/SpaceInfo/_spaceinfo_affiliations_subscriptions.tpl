{loop="$subscriptions"}
    <li>
        <span class="primary icon bubble">
            <img src="{$value->getPicture()}">
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
                <select name="{$value->jid}" id="role">
                    <option value="member">
                        {$c->__('affiliation.member')}
                    </option>
                    <option value="owner">
                        {$c->__('affiliation.owner')}
                    </option>
                    <option value="none" selected="selected">
                        {$c->__('spaceinfo.affiliation_none')}
                    </option>
                </select>
            </div>
            <label for="role">{$c->__('room.role')}</label>
        </div>
    </li>
{/loop}