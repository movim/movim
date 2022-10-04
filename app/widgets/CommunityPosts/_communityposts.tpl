{if="!empty($ids)"}
    <ul class="list card shadow {if="$gallery"}middle flex third gallery active{/if}">
    {loop="$ids"}
        {if="isset($posts[$value])"}
            {if="$gallery"}
                {autoescape="off"}
                    {$c->prepareTicket($posts[$value])}
                {/autoescape}
            {else}
                <div id="{$value|cleanupId}" class="block large">
                    {autoescape="off"}
                        {$c->preparePost($posts[$value])}
                    {/autoescape}
                </div>
            {/if}
        {/if}
    {/loop}
    </ul>
{elseif="$publicposts->isNotEmpty()"}
    <ul class="list card shadow {if="$gallery"}flex third gallery active{/if}">
    {loop="$publicposts"}
        {if="$gallery"}
            {autoescape="off"}
                {$c->prepareTicket($value)}
            {/autoescape}
        {else}
            <div id="{$value|cleanupId}" class="block large">
                {autoescape="off"}
                    {$c->preparePost($value)}
                {/autoescape}
            </div>
        {/if}
    {/loop}
    </ul>
{else}
    <div class="placeholder">
        <i class="material-icons">receipt</i>
        <h4>{$c->__('post.empty')}</h4>
    </div>
{/if}

<ul class="list thick" id="nextpage">
    <li class="block">
        <div>
            <p class="center">
                {if="(isset($previouspage) && (($before != null && $before != 'empty') || $after != null)) || $page > 0"}
                <a class="button flat" href="{$previouspage}">
                    <i class="material-icons">keyboard_arrow_left</i>
                    {$c->__('button.previous')}
                </a>
                {/if}
                {if="$last"}
                <a class="button flat" href="{$nextpage}" title="{$c->__('post.older')}">
                    {$c->__('button.next')}
                    <i class="material-icons">keyboard_arrow_right</i>
                </a>
                {/if}
            </p>
        </div>
    </li>
</ul>
