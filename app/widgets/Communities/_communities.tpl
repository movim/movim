{if="$tags->isNotEmpty()"}
    <ul class="list">
        <li>
            <div>
                <p class="line normal">
                    <a class="button flat disabled gray">
                        <i class="material-icons">whatshot</i>
                    </a>
                    {loop="$tags"}
                        <a class="button flat narrow" href="{$c->route('tag', $value->name)}">
                            <i class="material-icons">tag</i>{$value->name}
                        </a>
                    {/loop}
                </p>
            </div>
        </li>
    </ul>
{/if}

{if="!$communities->isEmpty()"}
<ul class="list middle flex third active all">
    <li class="subheader block large">
        <div>
            <p>{$c->__('communities.interesting')}</p>
        </div>
    </li>
    {loop="$communities"}
        <li
            class="block"
            onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')"
            title="{$value->server} - {$value->node}"
        >
            {$url = $value->getPhoto('m')}

            {if="$url"}
                <span class="primary icon bubble">
                    <img src="{$url}"/>
                </span>
            {else}
                <span class="primary icon bubble color {$value->node|stringToColor}">
                    {$value->node|firstLetterCapitalize}
                </span>
            {/if}
            <span class="control icon gray">
                <i class="material-icons">chevron_right</i>
            </span>
            <div>
                <p class="line normal">
                    {if="$value->name"}
                        {$value->name}
                    {else}
                        {$value->node}
                    {/if}
                    {if="$value->description"}
                        <span class="second">
                            {$value->description|strip_tags}
                        </span>
                    {/if}
                </p>
                <p class="line">
                    {$value->server} / {$value->node}
                </p>
            </div>
        </li>
    {/loop}
</ul>
<br />
{/if}

<div id="communities_posts">
    {autoescape="off"}
        {$c->preparePosts()}
    {/autoescape}
</div>