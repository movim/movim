{if="!empty($ids)"}
    <ul class="list card shadow">
    {loop="$ids"}
        {if="isset($posts[$value])"}
            <div id="{$value|cleanupId}" class="block large">
                {autoescape="off"}
                    {$c->preparePost($posts[$value])}
                {/autoescape}
            </div>
        {/if}
    {/loop}
    </ul>
{elseif="$publicposts->isNotEmpty()"}
    <ul class="list card shadow">
    {loop="$publicposts"}
        <div id="{$value->nodeid|cleanupId}" class="block large">
            {autoescape="off"}
                {$c->preparePost($value)}
            {/autoescape}
        </div>
    {/loop}
    </ul>
{else}
    <div class="placeholder">
        <i class="material-icons">receipt</i>
        <h4>{$c->__('post.empty')}</h4>
    </div>
{/if}

<ul class="list thick" id="goback">
    <li class="block">
        <p class="center">
            <a class="button flat" href="javascript:history.back()">
                <i class="material-icons">keyboard_arrow_left</i>
                {$c->__('button.previous')}
            </a>
            {if="$last"}
            <a class="button flat" href="{$goback}" title="{$c->__('post.older')}">
                {$c->__('button.next')}
                <i class="material-icons">keyboard_arrow_right</i>
            </a>
            {/if}
        </p>
    </li>
</ul>
