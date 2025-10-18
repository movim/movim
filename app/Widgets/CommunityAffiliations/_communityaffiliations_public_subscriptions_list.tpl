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
                    <i class="material-symbols">chevron_right</i>
                </span>
                {if="$value->contact"}
                    <span class="primary icon bubble small">
                        <img src="{$value->contact->getPicture(\Movim\ImageSize::M)}">
                    </span>
                    <div>
                        <p class="normal line">{$value->contact->truename}</p>
                    </div>
                {else}
                    <span class="primary icon bubble small color {$value->jid|stringToColor}">
                        {$value->jid|firstLetterCapitalize:true}
                    </span>
                    <div>
                        <p class="normal line">{$value->jid}</p>
                    </div>
                {/if}
            </li>
        </a>
    {/loop}
</ul>
