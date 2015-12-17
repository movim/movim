<br />
<h2 class="thin">{$c->__('post.hot')}</h2>
<h4 class="gray">{$c->__('post.hot_text')}</h4><br />
<ul class="list flex card shadow active">
{loop="$posts"}
    {if="!filter_var($value->origin, FILTER_VALIDATE_EMAIL)"}
        {$attachements = $value->getAttachements()}
        <li
            class="block condensed"
            data-id="{$value->nodeid}"
            data-server="{$value->origin}"
            data-node="{$value->node}">
            {$picture = $value->getPicture()}
            {if="current(explode('.', $value->origin)) == 'nsfw'"}
                <span class="primary icon thumb color red tiny">
                    +18
                </span>
            {elseif="$picture != null"}
                <span class="primary icon thumb" style="background-image: url({$picture});"></span>
            {else}
                <span class="primary icon thumb color {$value->node|stringToColor}">
                    {$value->node|firstLetterCapitalize}
                </span>
            {/if}
            <p class="line">
            {if="isset($value->title)"}
                {$value->title}
            {else}
                {$value->node}
            {/if}
            </p>
            <p>
                {$value->origin} /
                <a href="{$c->route('group', array($value->origin, $value->node))}">
                    <i class="zmdi zmdi-pages"></i> {$value->node}
                </a> –
                {$value->published|strtotime|prepareDate}
            </p>

            <p>
                {if="current(explode('.', $value->origin)) != 'nsfw'"}
                    {$value->contentcleaned|strip_tags}
                {/if}
            </p>
        </li>
    {/if}
{/loop}
</ul>
<ul class="list active thick">
    <a href="{$c->route('group')}">
        <li>
            <span class="primary icon"><i class="zmdi zmdi-pages"></i></span>
            <span class="control icon">
                <i class="zmdi zmdi-chevron-right"></i>
            </span>
            <p class="normal">{$c->__('post.discover')}</p>
        </li>
    </a>
</ul>

