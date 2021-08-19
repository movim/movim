<ul class="list card active thin">
    <li class="subheader">
        <div>
            <p>{$c->__('communityaffiliation.public_subscriptions')}</p>
        </div>
    </li>
    {loop="$subscriptions"}
        <a href="{$c->route('contact', $value->jid)}">
            <li title="{$value->jid}">
                <span class="control icon gray">
                    <i class="material-icons">chevron_right</i>
                </span>
                {if="$value->contact"}
                    {$url = $value->contact->getPhoto('m')}
                    {if="$url"}
                        <span class="primary icon bubble small"
                            style="background-image: url({$url});">
                        </span>
                    {else}
                        <span class="primary icon bubble small color {$value->jid|stringToColor}">
                            {$value->contact->truename|firstLetterCapitalize:true}
                        </span>
                    {/if}
                    <div>
                        <p class="normal">{$value->contact->truename}</p>
                    </div>
                {else}
                    <span class="primary icon bubble small color {$value->jid|stringToColor}">
                        {$value->jid|firstLetterCapitalize:true}
                    </span>
                    <div>
                        <p class="normal">{$value->jid}</p>
                    </div>
                {/if}
            </li>
        </a>
    {/loop}
</ul>