{if="(!isset($posts) || $posts->isEmpty()) && (!isset($contacts) || $contacts->isEmpty()) && (!isset($tags) || $tags->isEmpty())"}
    {autoescape="off"}
        {$c->prepareEmpty()}
    {/autoescape}
{/if}

{if="(isset($communities) && $communities->isNotEmpty()) || (isset($tags) && $tags->isNotEmpty()) || (isset($posts) && $posts->isNotEmpty())"}
<ul class="list flex">
    <li class="subheader">
        <div>
            <p>
                {$c->__('page.communities')}
            </p>
        </div>
    </li>
</ul>
{/if}

{if="isset($communities) && $communities->isNotEmpty()"}
<ul class="list middle card shadow active flex">
    {loop="$communities"}
    <li
        class="block"
        onclick="MovimUtils.reload('{$c->route('community', [$value->server, $value->node])}'); Drawer.clear();"
        title="{$value->server} - {$value->node}"
    >
            <span class="primary icon thumb">
                <img src="{$value->getPicture(\Movim\ImageSize::M)}"/>
            </span>
            <div>
                <p class="line normal">
                    {if="$value->name"}
                        {$value->name}
                    {else}
                        {$value->node}
                    {/if}
                </p>
                {if="$value->description"}
                    <p class="line two">{$value->description|strip_tags}</p>
                {/if}
                <p class="line">
                    {if="$value->isGallery()"}
                        <i class="material-symbols">grid_view</i>
                        •
                    {/if}
                    {$value->server} / {$value->node}
                </p>
            </div>
        </li>
    {/loop}
</ul>
{/if}

{if="isset($tags) && $tags->isNotEmpty()"}
<ul class="list flex middle">
    <li class="block large">
        <div>
            <p class="line two normal">{loop="$tags"}<a class="chip outline active" href="#" onclick="MovimUtils.reload('{$c->route('tag', $value)}')">
                <i class="material-symbols icon gray">tag</i>{$value}
            </a>{/loop}</p>
        </div>
    </li>
</ul>
<br />
{/if}

{if="isset($posts) && $posts->isNotEmpty()"}
<ul id="search_posts" class="list middle card shadow active flex">
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
