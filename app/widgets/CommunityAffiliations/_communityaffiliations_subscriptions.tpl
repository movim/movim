<section class="scroll">
    <ul class="list thin">
        <li class="subheader">
            <p><span class="info">{$subscriptions|count}</span>{$c->__('communityaffiliation.subscriptions')}</p>
        </li>
        {loop="$subscriptions"}
            {$contact = $c->getContact($value->jid)}
            <li>
                {$url = $contact->getPhoto('m')}
                {if="$url"}
                    <span class="primary icon bubble"
                        style="background-image: url({$url});">
                    </span>
                {else}
                    <span class="primary icon bubble color {$contact->jid|stringToColor}">
                        {$contact->getTrueName()|firstLetterCapitalize}
                    </span>
                {/if}
                <p class="normal line">
                    <a href="{$c->route('contact', $value->jid)}">
                        {$contact->getTrueName()}
                    </a>
                </p>
                <p>
                    {$contact->jid}
                </p>
            </li>
        {/loop}
    </ul>
</section>
<div>
    <a onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>
