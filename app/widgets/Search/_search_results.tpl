{if="(!isset($posts) || $posts->isEmpty()) && (!isset($contacts) || $contacts->isEmpty()) && (!isset($tags) || $tags->isEmpty())"}
    {autoescape="off"}
        {$c->prepareEmpty()}
    {/autoescape}
{/if}

{if="isset($tags) && $tags->isNotEmpty()"}
<ul class="list active flex large">
    <li class="subheader block large">
        <div>
            <p>{$c->__('search.tags')}</p>
        </div>
    </li>
    {loop="$tags"}
        <li class="block" onclick="MovimUtils.reload('{$c->route('tag', $value)}'); Drawer.clear();">
            <span class="primary icon gray">
                #
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <div>
                <p class="line normal">{$value}</p>
                <p>{$c->__('communitydata.num', $key)}</p>
            </div>
        </li>
    {/loop}
</ul>
{/if}

{if="isset($communities) && $communities->isNotEmpty()"}
<ul class="list card active middle">
    <li class="subheader">
        <div>
            <p>
                <span class="info">{$communities|count}</span>
                {$c->__('page.communities')}
            </p>
        </div>
    </li>
    {loop="$communities"}
    <li
        onclick="MovimUtils.reload('{$c->route('community', [$value->server, $value->node])}'); Drawer.clear();"
        title="{$value->server} - {$value->node}"
    >
            <span class="primary icon bubble">
                <img src="{$value->getPicture('m')}"/>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
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
{/if}

{if="isset($posts) && $posts->isNotEmpty()"}
<ul id="search_posts" class="list card active middle">
    <li class="subheader">
        <div>
            <p>
                <span class="info">{$posts|count}</span>
                {$c->__('page.news')}
            </p>
        </div>
    </li>
    {loop="$posts"}
        {autoescape="off"}
            {$c->prepareTicket($value)}
        {/autoescape}
    {/loop}
</ul>
{/if}

{if="isset($contacts) && $contacts->isNotEmpty()"}
    {autoescape="off"}
        {$c->prepareUsers($contacts)}
    {/autoescape}
{/if}
