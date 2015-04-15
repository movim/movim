<ul class="divided spaced middle">
    <li class="subheader">{$c->__('post.comments')}</li>
    {loop="$comments"}
        <li class="condensed">
            <a href="{$c->route('contact', $value->getContact()->jid)}">
                {$url = $value->getContact()->getPhoto('s')}
                {if="$url"}
                    <span class="icon bubble">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="icon bubble color {$value->getContact()->jid|stringToColor}">
                        <i class="md md-person"></i>
                    </span>
                {/if}
            </a>
            <span class="info">{$value->published|strtotime|prepareDate}</span>
            <span>
                <a href="{$c->route('contact', $value->getContact()->jid)}">
                    {$value->getContact()->getTrueName()}
                </a>
            </span>
            <p>
                {$value->content}
            </p>
        </li>
    {/loop}
</ul>
